<?php

class FollowUpModel
{
    public static function getSession(int $sessionId, int $userId): ?array
    {
        $rs = Database::search("
            SELECT s.session_id, s.user_id, s.counselor_id, s.session_datetime, s.status, s.updated_at,
                   COALESCE(u.display_name, u.username) AS counselor_name,
                   u.profile_picture                    AS counselor_avatar,
                   u.user_id                            AS counselor_user_id,
                   c.title                              AS counselor_title,
                   c.specialty
            FROM sessions s
            JOIN counselors c ON c.counselor_id = s.counselor_id
            JOIN users u ON u.user_id = c.user_id
            WHERE s.session_id = $sessionId
              AND s.user_id    = $userId
              AND s.status     = 'completed'
            LIMIT 1
        ");
        if (!$rs || $rs->num_rows === 0) {
            return null;
        }
        return $rs->fetch_assoc();
    }

    public static function getMessages(int $sessionId): array
    {
        $rs = Database::search("
            SELECT sm.message_id, sm.sender_id, sm.message, sm.created_at,
                   COALESCE(u.display_name, u.username) AS sender_name,
                   u.profile_picture AS sender_avatar,
                   u.role            AS sender_role
            FROM session_messages sm
            JOIN users u ON u.user_id = sm.sender_id
            WHERE sm.session_id = $sessionId
            ORDER BY sm.created_at ASC
        ");
        $messages = [];
        if ($rs) {
            while ($row = $rs->fetch_assoc()) {
                $messages[] = $row;
            }
        }
        return $messages;
    }

    public static function pollMessages(int $sessionId, int $lastId): array
    {
        $rs = Database::search("
            SELECT sm.message_id, sm.sender_id, sm.message, sm.created_at,
                   COALESCE(u.display_name, u.username) AS sender_name,
                   u.profile_picture AS sender_avatar
            FROM session_messages sm
            JOIN users u ON u.user_id = sm.sender_id
            WHERE sm.session_id = $sessionId AND sm.message_id > $lastId
            ORDER BY sm.created_at ASC
        ");
        $rows = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $rows[] = $row;
        }
        return $rows;
    }

    public static function sendMessage(int $sessionId, int $userId, string $msg, int $counselorUserId): int
    {
        Database::setUpConnection();
        $safeMsg = Database::$connection->real_escape_string($msg);
        Database::iud("INSERT INTO session_messages (session_id, sender_id, message) VALUES ($sessionId, $userId, '$safeMsg')");
        $newMsgId = (int)Database::$connection->insert_id;

        if ($counselorUserId > 0) {
            $t = Database::$connection->real_escape_string('New follow-up message');
            $m = Database::$connection->real_escape_string('Your client sent a follow-up message.');
            $l = Database::$connection->real_escape_string("/counselor/sessions/follow-up?session_id=$sessionId");
            Database::iud("INSERT INTO notifications (user_id, type, title, message, link) VALUES ($counselorUserId, 'followup_message', '$t', '$m', '$l')");
        }

        return $newMsgId;
    }
}
