<?php

class TaskUncompleteModel
{
    public static function uncomplete(int $taskId, int $userId): bool
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

    private static function recalculatePlanProgress(int $planId): void
    {
        if ($planId <= 0) return;

        $stats = Database::search(
            "SELECT COUNT(*) AS total_count,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_count
             FROM recovery_tasks
             WHERE plan_id = $planId"
        );
        $row = $stats ? $stats->fetch_assoc() : null;
        $total = (int)($row['total_count'] ?? 0);
        $completed = (int)($row['completed_count'] ?? 0);
        $progress = $total > 0 ? (int)round(($completed / $total) * 100) : 0;

        Database::iud(
            "UPDATE recovery_plans
             SET progress_percentage = $progress, updated_at = NOW()
             WHERE plan_id = $planId"
        );
    }
}