<?php

class TaskRequestChangeModel
{
    public static function create(int $taskId, int $userId, string $reason, string $requestedChange): bool
    {
        if ($taskId <= 0 || $userId <= 0 || trim($reason) === '') return false;

        $rs = Database::search(
            "SELECT rt.plan_id, rp.counselor_id, c.user_id AS counselor_user_id
             FROM recovery_tasks rt
             INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             INNER JOIN counselors c ON c.counselor_id = rp.counselor_id
             WHERE rt.task_id = $taskId
               AND rp.user_id = $userId
               AND rp.counselor_id IS NOT NULL
             LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;

        $row = $rs->fetch_assoc();
        $counselorId = (int)$row['counselor_id'];
        $planId = (int)$row['plan_id'];
        $counselorUserId = (int)$row['counselor_user_id'];

        Database::setUpConnection();
        $conn = Database::$connection;
        $safeReason = $conn->real_escape_string($reason);
        $safeChange = $conn->real_escape_string($requestedChange);

        Database::iud(
            "INSERT INTO task_change_requests
                (task_id, plan_id, user_id, counselor_id, reason, requested_change, status, created_at)
             VALUES ($taskId, $planId, $userId, $counselorId, '$safeReason', '$safeChange', 'pending', NOW())"
        );

        if ($counselorUserId > 0) {
            $t = $conn->real_escape_string('Task Change Request');
            $m = $conn->real_escape_string('A client has requested a change to one of their assigned tasks.');
            $l = $conn->real_escape_string('/counselor/recovery-plans/task-changes');
            Database::iud(
                "INSERT INTO notifications (user_id, type, title, message, link)
                 VALUES ($counselorUserId, 'task_change_request', '$t', '$m', '$l')"
            );
        }

        return true;
    }

    public static function getTaskTitle(int $taskId, int $userId): ?array
    {
        $rs = Database::search(
            "SELECT rt.title, rp.counselor_id
             FROM recovery_tasks rt
             INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             WHERE rt.task_id = $taskId AND rp.user_id = $userId
             LIMIT 1"
        );
        if (!$rs) return null;
        return $rs->fetch_assoc();
    }
}