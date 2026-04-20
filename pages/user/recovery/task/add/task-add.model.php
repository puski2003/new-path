<?php

class TaskAddModel
{
    public static function add(int $planId, int $userId, array $data): bool
    {
        if ($planId <= 0 || $userId <= 0) return false;

        $rs = Database::search(
            "SELECT plan_id FROM recovery_plans
             WHERE plan_id = $planId AND user_id = $userId AND counselor_id IS NULL LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;

        Database::setUpConnection();
        $conn = Database::$connection;
        $title = $conn->real_escape_string(trim($data['title'] ?? ''));
        $taskType = $conn->real_escape_string(trim($data['taskType'] ?? 'custom'));
        $priority = $conn->real_escape_string(trim($data['priority'] ?? 'medium'));
        $phase = max(1, (int)($data['phase'] ?? 1));

        if ($title === '') return false;

        Database::iud(
            "INSERT INTO recovery_tasks (plan_id, title, task_type, status, priority, phase, sort_order, created_at, updated_at)
             VALUES ($planId, '$title', '$taskType', 'pending', '$priority', $phase, 999, NOW(), NOW())"
        );
        return true;
    }
}