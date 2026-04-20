<?php

class RequestFollowupModel
{
    public static function request(int $userId, int $planId): bool
    {
        $rs = Database::search(
            "SELECT rp.counselor_id, rp.title,
                    COALESCE(u.display_name, CONCAT(u.first_name, ' ', u.last_name)) AS user_name,
                    cu.user_id AS counselor_user_id
             FROM recovery_plans rp
             INNER JOIN users u  ON u.user_id  = $userId
             LEFT JOIN counselors c  ON c.counselor_id  = rp.counselor_id
             LEFT JOIN users cu      ON cu.user_id       = c.user_id
             WHERE rp.plan_id = $planId AND rp.user_id = $userId
             LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;

        $row = $rs->fetch_assoc();
        $counselorUserId = $row['counselor_user_id'] ? (int)$row['counselor_user_id'] : null;
        Database::setUpConnection();
        $conn = Database::$connection;
        $safeUserName = $conn->real_escape_string($row['user_name'] ?? 'A user');
        $safePlanTitle = $conn->real_escape_string($row['title'] ?? 'their recovery plan');

        if ($counselorUserId) {
            Database::iud(
                "INSERT INTO notifications (user_id, type, title, message, link, is_read, created_at)
                 VALUES (
                     $counselorUserId,
                     'plan_followup_request',
                     'Follow-up Plan Requested',
                     '$safeUserName completed \"$safePlanTitle\" and is requesting a follow-up recovery plan.',
                     '/counselor/clients',
                     0, NOW()
                 )"
            );
        }

        Database::iud(
            "INSERT INTO notifications (user_id, type, title, message, link, is_read, created_at)
             VALUES (
                 $userId,
                 'plan_followup_sent',
                 'Follow-up Requested',
                 'Your counselor has been notified and will create a new plan for you soon.',
                 '/user/recovery',
                 0, NOW()
             )"
        );

        return true;
    }
}