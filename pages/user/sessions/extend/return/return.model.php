<?php

class ExtendReturnModel
{
    public static function getStatus(int $extensionId): ?string
    {
        $rs = Database::search(
            "SELECT status FROM session_extension_requests WHERE extension_id = $extensionId LIMIT 1"
        );
        $row = $rs ? $rs->fetch_assoc() : null;
        return $row ? $row['status'] : null;
    }

    public static function insertNotifications(array $ext, string $newEndLabel): void
    {
        Database::setUpConnection();

        $uTitle = Database::$connection->real_escape_string('Session Extended!');
        $uMsg   = Database::$connection->real_escape_string(
            'Your session has been extended by ' . $ext['durationMinutes'] . ' minutes. New end time: ' . $newEndLabel . '.'
        );
        $uLink  = Database::$connection->real_escape_string('/user/sessions?id=' . $ext['sessionId']);
        Database::iud(
            "INSERT INTO notifications (user_id, type, title, message, link)
             VALUES ({$ext['userId']}, 'extension_paid', '$uTitle', '$uMsg', '$uLink')"
        );

        if ($ext['counselorUserId'] > 0) {
            $cTitle = Database::$connection->real_escape_string('Extension Payment Confirmed');
            $cMsg   = Database::$connection->real_escape_string(
                $ext['userName'] . ' has paid for the ' . $ext['durationMinutes'] . '-minute extension. New end time: ' . $newEndLabel . '.'
            );
            $cLink  = Database::$connection->real_escape_string('/counselor/sessions/workspace?session_id=' . $ext['sessionId']);
            Database::iud(
                "INSERT INTO notifications (user_id, type, title, message, link)
                 VALUES ({$ext['counselorUserId']}, 'extension_paid', '$cTitle', '$cMsg', '$cLink')"
            );
        }
    }
}
