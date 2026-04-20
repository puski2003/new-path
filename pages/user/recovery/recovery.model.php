<?php

class RecoveryModel
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
                'taskId' => (int)$row['task_id'],
                'title' => $row['title'] ?? 'Task',
                'status' => $row['status'] ?? 'pending',
                'priority' => $row['priority'] ?? 'medium',
                'taskType' => $row['task_type'] ?? 'custom',
                'phase' => (int)$row['phase'],
                'dueDate' => !empty($row['due_date']) ? date('M j', strtotime($row['due_date'])) : null,
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

    public static function getUserActivePlans(int $userId): array
    {
        $rs = Database::search(
            "SELECT plan_id, title, description, progress_percentage, assigned_status, counselor_id
             FROM recovery_plans
             WHERE user_id = $userId
               AND status = 'active'
               AND (assigned_status IS NULL OR assigned_status = 'accepted')
             ORDER BY updated_at DESC"
        );

        $plans = [];
        while ($row = $rs->fetch_assoc()) {
            $plans[] = [
                'planId' => (int)$row['plan_id'],
                'title' => $row['title'] ?? 'Recovery Plan',
                'description' => $row['description'] ?? '',
                'progressPercentage' => (int)($row['progress_percentage'] ?? 0),
                'assignedStatus' => $row['assigned_status'] ?? null,
                'counselorId' => isset($row['counselor_id']) ? (int)$row['counselor_id'] : null,
            ];
        }
        return $plans;
    }

    public static function getAssignedPlansForUser(int $userId): array
    {
        $rs = Database::search(
            "SELECT plan_id, title, description, progress_percentage
             FROM recovery_plans
             WHERE user_id = $userId
               AND assigned_status = 'pending'
             ORDER BY updated_at DESC"
        );

        $plans = [];
        while ($row = $rs->fetch_assoc()) {
            $plans[] = [
                'planId' => (int)$row['plan_id'],
                'title' => $row['title'] ?? 'Recovery Plan',
                'description' => $row['description'] ?? '',
                'progressPercentage' => (int)($row['progress_percentage'] ?? 0),
            ];
        }
        return $plans;
    }

    public static function getGoalsByPlanId(int $planId): array
    {
        $rs = Database::search(
            "SELECT goal_id, title, description, goal_type, target_days, current_progress, status
             FROM recovery_goals
             WHERE plan_id = $planId
             ORDER BY goal_type ASC, goal_id ASC"
        );

        $goals = [];
        while ($row = $rs->fetch_assoc()) {
            $target = max(1, (int)$row['target_days']);
            $current = max(0, (int)$row['current_progress']);
            $goals[] = [
                'goalId' => (int)$row['goal_id'],
                'title' => $row['title'],
                'description' => $row['description'] ?? '',
                'goalType' => $row['goal_type'],
                'targetDays' => $target,
                'currentProgress' => $current,
                'progressPercentage' => min(100, (int)round(($current / $target) * 100)),
                'status' => $row['status'],
            ];
        }
        return $goals;
    }

    public static function getProgressStats(int $userId): array
    {
        $rs = Database::search(
            "SELECT sobriety_start_date, DATEDIFF(CURDATE(), sobriety_start_date) AS days_sober
             FROM user_profiles
             WHERE user_id = $userId LIMIT 1"
        );

        $row = $rs ? $rs->fetch_assoc() : null;

        $startDate = $row['sobriety_start_date'] ?? null;
        $daysSober = $startDate ? max(0, (int)($row['days_sober'] ?? 0)) : 0;

        $urgesLogged = 0;
        $urgeRs = Database::search("SELECT COUNT(*) AS cnt FROM urge_logs WHERE user_id = $userId AND outcome = 'resisted'");
        if ($urgeRs) {
            $urgesLogged = (int)($urgeRs->fetch_assoc()['cnt'] ?? 0);
        }

        return [
            'daysSober'        => $daysSober,
            'totalDaysTracked' => $daysSober,
            'urgesLogged'      => $urgesLogged,
            'sessionsCompleted' => 0,
            'trackingStarted'  => $startDate !== null,
        ];
    }

    public static function getNextSessionSummary(int $userId): array
    {
        $rs = Database::search(
            "SELECT s.session_datetime,
                   COALESCE(u.display_name, CONCAT(u.first_name, ' ', u.last_name)) AS counselor_name
            FROM sessions s
            INNER JOIN counselors c ON c.counselor_id = s.counselor_id
            INNER JOIN users u ON u.user_id = c.user_id
            WHERE s.user_id = $userId
              AND s.session_datetime >= NOW()
              AND s.status IN ('scheduled', 'confirmed')
            ORDER BY s.session_datetime ASC
            LIMIT 1"
        );

        if ($row = $rs->fetch_assoc()) {
            return [
                'time' => date('M j, g:i A', strtotime($row['session_datetime'])),
                'counselorName' => $row['counselor_name'] ?? 'Counselor',
            ];
        }

        return [
            'time' => 'No upcoming sessions',
            'counselorName' => 'Counselor',
        ];
    }

    public static function getUserGoalsForActivePlan(int $userId): array
    {
        $rs = Database::search(
            "SELECT rg.goal_id, rg.title, rg.description, rg.goal_type,
                    rg.target_days, rg.current_progress, rg.status, rg.plan_id
             FROM recovery_goals rg
             INNER JOIN recovery_plans rp ON rp.plan_id = rg.plan_id
             WHERE rp.user_id = $userId
               AND rp.status = 'active'
               AND (rp.assigned_status IS NULL OR rp.assigned_status = 'accepted')
             ORDER BY rg.goal_type ASC, rg.goal_id ASC"
        );

        $goals = [];
        while ($row = $rs->fetch_assoc()) {
            $target = max(1, (int)$row['target_days']);
            $current = max(0, (int)$row['current_progress']);
            $goals[] = [
                'goalId' => (int)$row['goal_id'],
                'planId' => (int)$row['plan_id'],
                'title' => $row['title'],
                'description' => $row['description'] ?? '',
                'goalType' => $row['goal_type'],
                'targetDays' => $target,
                'currentProgress' => $current,
                'progressPercentage' => min(100, (int)round(($current / $target) * 100)),
                'status' => $row['status'],
            ];
        }
        return $goals;
    }

    public static function getUserPausedPlans(int $userId): array
    {
        $rs = Database::search(
            "SELECT plan_id, title, description, progress_percentage
             FROM recovery_plans
             WHERE user_id = $userId
               AND status = 'paused'
             ORDER BY updated_at DESC"
        );

        $plans = [];
        while ($row = $rs->fetch_assoc()) {
            $plans[] = [
                'planId' => (int)$row['plan_id'],
                'title' => $row['title'] ?? 'Recovery Plan',
                'description' => $row['description'] ?? '',
                'progressPercentage' => (int)($row['progress_percentage'] ?? 0),
            ];
        }
        return $plans;
    }
}