<?php

class ViewModel
{
    public function getPlanByIdForUser(int $planId, int $userId): ?array
    {
        $rs = Database::search(
            "SELECT plan_id, title, description, plan_type, status, assigned_status, progress_percentage, counselor_id
             FROM recovery_plans
             WHERE plan_id = $planId
               AND user_id = $userId
             LIMIT 1"
        );

        if (!$rs || $rs->num_rows === 0) {
            return null;
        }

        $row = $rs->fetch_assoc();
        return [
            'planId'             => (int)$row['plan_id'],
            'title'              => $row['title']              ?? 'Recovery Plan',
            'description'        => $row['description']        ?? '',
            'planType'           => $row['plan_type']           ?? 'self',
            'status'             => $row['status']              ?? 'draft',
            'assignedStatus'     => $row['assigned_status']     ?? null,
            'progressPercentage' => (int)($row['progress_percentage'] ?? 0),
            'counselorId'        => isset($row['counselor_id']) ? (int)$row['counselor_id'] : null,
        ];
    }

    public function getGoalsByPlanId(int $planId): array
    {
        $rs = Database::search(
            "SELECT goal_type, title, target_days, current_progress
             FROM recovery_goals
             WHERE plan_id = $planId
             ORDER BY goal_id ASC"
        );

        $goals = [];
        while ($row = $rs->fetch_assoc()) {
            $targetDays = max(1, (int)($row['target_days'] ?? 0));
            $current    = max(0, (int)($row['current_progress'] ?? 0));
            $goals[] = [
                'goalType'           => $row['goal_type'] ?? 'short_term',
                'title'              => $row['title']     ?? 'Goal',
                'targetDays'         => $targetDays,
                'currentProgress'    => $current,
                'progressPercentage' => min(100, (int)round(($current / $targetDays) * 100)),
            ];
        }
        return $goals;
    }

    public function getTasksByPlanId(int $planId, int $userId): array
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
                'title'    => $row['title']    ?? 'Task',
                'status'   => $row['status']   ?? 'pending',
                'priority' => $row['priority'] ?? 'medium',
                'taskType' => $row['task_type'] ?? 'custom',
                'phase'    => (int)($row['phase'] ?? 1),
                'dueDate'  => !empty($row['due_date']) ? date('M j', strtotime($row['due_date'])) : null,
            ];
        }
        return $tasks;
    }
}
