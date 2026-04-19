<?php

class TaskDeleteModel
{
    public static function delete(int $taskId, int $userId): bool
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