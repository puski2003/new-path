<?php

class TaskEditModel
{
    public static function update(int $taskId, int $userId, array $data): bool
    {
        if ($taskId <= 0 || $userId <= 0) return false;

        $rs = Database::search(
            "SELECT rt.task_id FROM recovery_tasks rt
             INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             WHERE rt.task_id = $taskId AND rp.user_id = $userId AND rp.counselor_id IS NULL LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;

        Database::setUpConnection();
        $conn = Database::$connection;
        $title = $conn->real_escape_string(trim($data['title'] ?? ''));
        $taskType = $conn->real_escape_string(trim($data['taskType'] ?? 'custom'));
        $priority = $conn->real_escape_string(trim($data['priority'] ?? 'medium'));

        if ($title === '') return false;

        Database::iud(
            "UPDATE recovery_tasks SET title='$title', task_type='$taskType', priority='$priority', updated_at=NOW()
             WHERE task_id = $taskId"
        );
        return true;
    }

    public static function getTask(int $taskId, int $userId): ?array
    {
        $rs = Database::search(
            "SELECT rt.task_id, rt.title, rt.task_type, rt.priority, rt.phase, rp.plan_id
             FROM recovery_tasks rt
             INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             WHERE rt.task_id = $taskId AND rp.user_id = $userId AND rp.counselor_id IS NULL
             LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return null;
        return $rs->fetch_assoc();
    }
}