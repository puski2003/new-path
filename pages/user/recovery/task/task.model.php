<?php

trait TaskModel
{
    public static function getUserDailyTasks(int $userId): array
    {
        $rs = Database::search(
            "SELECT rt.task_id, rt.title, rt.status, rt.priority, rt.task_type, rt.due_date, rt.phase
             FROM recovery_tasks rt
             INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             WHERE rp.user_id = $userId
               AND rp.status = 'active'
               AND (rp.assigned_status IS NULL OR rp.assigned_status = 'accepted')
               AND rt.phase = (
                   SELECT MIN(rt2.phase)
                   FROM recovery_tasks rt2
                   WHERE rt2.plan_id = rp.plan_id
                     AND rt2.status <> 'completed'
               )
             ORDER BY rt.status ASC, rt.priority DESC, rt.sort_order ASC"
        );

        $tasks = [];
        while ($row = $rs->fetch_assoc()) {
            $tasks[] = [
                'taskId'   => (int)$row['task_id'],
                'title'    => $row['title'] ?? 'Task',
                'status'   => $row['status'] ?? 'pending',
                'priority' => $row['priority'] ?? 'medium',
                'taskType' => $row['task_type'] ?? 'custom',
                'phase'    => (int)$row['phase'],
                'dueDate'  => !empty($row['due_date']) ? date('M j', strtotime($row['due_date'])) : null,
            ];
        }
        return $tasks;
    }

    public static function getUserTaskStats(int $userId): array
    {
        $rs = Database::search(
            "SELECT
                SUM(CASE WHEN rt.status = 'completed' THEN 1 ELSE 0 END) AS completed_count,
                SUM(CASE WHEN rt.status <> 'completed' THEN 1 ELSE 0 END) AS pending_count
             FROM recovery_tasks rt
             INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             WHERE rp.user_id = $userId
               AND rp.status = 'active'"
        );

        $row = $rs->fetch_assoc();
        return [
            'completed' => isset($row['completed_count']) ? (int)$row['completed_count'] : 0,
            'pending' => isset($row['pending_count']) ? (int)$row['pending_count'] : 0,
        ];
    }

    public static function getTasksByPlanId(int $planId, int $userId): array
    {
        $rs = Database::search(
            "SELECT rt.task_id, rt.title, rt.status, rt.priority, rt.task_type, rt.due_date, rt.phase
             FROM recovery_tasks rt
             INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             WHERE rt.plan_id = $planId
               AND rp.user_id = $userId
             ORDER BY rt.phase ASC, rt.sort_order ASC, rt.task_id ASC"
        );

        $tasks = [];
        while ($row = $rs->fetch_assoc()) {
            $tasks[] = [
                'taskId'   => (int)$row['task_id'],
                'title'    => $row['title'] ?? 'Task',
                'status'   => $row['status'] ?? 'pending',
                'priority' => $row['priority'] ?? 'medium',
                'taskType' => $row['task_type'] ?? 'custom',
                'phase'    => (int)($row['phase'] ?? 1),
                'dueDate'  => !empty($row['due_date']) ? date('M j', strtotime($row['due_date'])) : null,
            ];
        }
        return $tasks;
    }

    public static function completeTask(int $taskId, int $userId): bool
    {
        if ($taskId <= 0 || $userId <= 0) return false;

        $taskRs = Database::search(
            "SELECT rt.phase, rt.plan_id, rt.status
             FROM recovery_tasks rt
             INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             WHERE rt.task_id = $taskId AND rp.user_id = $userId
             LIMIT 1"
        );
        if (!$taskRs || $taskRs->num_rows === 0) return false;
        $taskRow = $taskRs->fetch_assoc();

        if ($taskRow['status'] === 'completed') return true;

        $currentPhase = (int)$taskRow['phase'];
        $planId       = (int)$taskRow['plan_id'];

        if ($currentPhase > 1) {
            $blockRs = Database::search(
                "SELECT COUNT(*) AS blocked
                 FROM recovery_tasks
                 WHERE plan_id = $planId
                   AND phase < $currentPhase
                   AND status <> 'completed'"
            );
            $blockRow = $blockRs->fetch_assoc();
            if ((int)($blockRow['blocked'] ?? 0) > 0) {
                return false;
            }
        }

        Database::iud(
            "UPDATE recovery_tasks
             SET status = 'completed', completed_at = NOW(), updated_at = NOW()
             WHERE task_id = $taskId"
        );

        self::recalculatePlanProgress($planId);
        return true;
    }

    public static function uncompleteTask(int $taskId, int $userId): bool
    {
        if ($taskId <= 0 || $userId <= 0) return false;

        $taskRs = Database::search(
            "SELECT rt.plan_id, rt.status
             FROM recovery_tasks rt
             INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             WHERE rt.task_id = $taskId AND rp.user_id = $userId
             LIMIT 1"
        );
        if (!$taskRs || $taskRs->num_rows === 0) return false;
        $taskRow = $taskRs->fetch_assoc();

        if ($taskRow['status'] !== 'completed') return true;

        $planId = (int)$taskRow['plan_id'];

        Database::iud(
            "UPDATE recovery_tasks
             SET status = 'pending', completed_at = NULL, updated_at = NOW()
             WHERE task_id = $taskId"
        );

        self::recalculatePlanProgress($planId);
        return true;
    }

    public static function recalculatePlanProgress(int $planId): void
    {
        if ($planId <= 0) return;

        $stats = Database::search(
            "SELECT COUNT(*) AS total_count,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_count
             FROM recovery_tasks
             WHERE plan_id = $planId"
        );
        $row = $stats ? $stats->fetch_assoc() : null;
        $total = (int) ($row['total_count'] ?? 0);
        $completed = (int) ($row['completed_count'] ?? 0);
        $progress = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        $newStatus = ($total > 0 && $completed >= $total) ? 'completed' : 'active';

        Database::iud(
            "UPDATE recovery_plans
             SET progress_percentage = $progress,
                 status = '$newStatus',
                 actual_completion_date = " . ($newStatus === 'completed' ? 'CURDATE()' : 'actual_completion_date') . ",
                 updated_at = NOW()
             WHERE plan_id = $planId"
        );

        if ($newStatus === 'completed') {
            $ownerRs = Database::search(
                "SELECT user_id FROM recovery_plans WHERE plan_id = $planId LIMIT 1"
            );
            if ($ownerRs && ($ownerRow = $ownerRs->fetch_assoc())) {
                $uid = (int)$ownerRow['user_id'];
                Database::iud("INSERT IGNORE INTO user_achievements (user_id, achievement_key, awarded_at) VALUES ($uid, 'plan_completed', NOW())");
            }
        }
    }

    public static function addUserTask(int $planId, int $userId, array $data): bool
    {
        if ($planId <= 0 || $userId <= 0) return false;

        $rs = Database::search(
            "SELECT plan_id FROM recovery_plans
             WHERE plan_id = $planId AND user_id = $userId AND counselor_id IS NULL LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;

        Database::setUpConnection();
        $title    = Database::$connection->real_escape_string(trim($data['title'] ?? ''));
        $taskType = Database::$connection->real_escape_string(trim($data['taskType'] ?? 'custom'));
        $priority = Database::$connection->real_escape_string(trim($data['priority'] ?? 'medium'));
        $phase    = max(1, (int)($data['phase'] ?? 1));

        if ($title === '') return false;

        Database::iud(
            "INSERT INTO recovery_tasks (plan_id, title, task_type, status, priority, phase, sort_order, created_at, updated_at)
             VALUES ($planId, '$title', '$taskType', 'pending', '$priority', $phase, 999, NOW(), NOW())"
        );
        return true;
    }

    public static function updateUserTask(int $taskId, int $userId, array $data): bool
    {
        if ($taskId <= 0 || $userId <= 0) return false;

        $rs = Database::search(
            "SELECT rt.task_id FROM recovery_tasks rt
             INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             WHERE rt.task_id = $taskId AND rp.user_id = $userId AND rp.counselor_id IS NULL LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;

        Database::setUpConnection();
        $title    = Database::$connection->real_escape_string(trim($data['title'] ?? ''));
        $taskType = Database::$connection->real_escape_string(trim($data['taskType'] ?? 'custom'));
        $priority = Database::$connection->real_escape_string(trim($data['priority'] ?? 'medium'));

        if ($title === '') return false;

        Database::iud(
            "UPDATE recovery_tasks SET title='$title', task_type='$taskType', priority='$priority', updated_at=NOW()
             WHERE task_id = $taskId"
        );
        return true;
    }

    public static function deleteUserTask(int $taskId, int $userId): bool
    {
        if ($taskId <= 0 || $userId <= 0) return false;

        $rs = Database::search(
            "SELECT rt.task_id FROM recovery_tasks rt
             INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             WHERE rt.task_id = $taskId AND rp.user_id = $userId AND rp.counselor_id IS NULL LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;

        Database::iud("DELETE FROM recovery_tasks WHERE task_id = $taskId");
        return true;
    }
}