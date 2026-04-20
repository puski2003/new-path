<?php

class ReturnModel
{
    public static function insertNotifications(
        int    $userId,
        int    $counselorUserId,
        string $userName,
        string $counselorName,
        string $sessionDateLabel
    ): void {
        Database::setUpConnection();

        $notifTitle = Database::$connection->real_escape_string('Session Confirmed');
        $notifMsg   = Database::$connection->real_escape_string(
            'Your session with ' . $counselorName . ' on ' . $sessionDateLabel . ' is confirmed.'
        );
        $notifLink  = Database::$connection->real_escape_string('/user/sessions');
        Database::iud("INSERT INTO notifications (user_id, type, title, message, link)
                       VALUES ($userId, 'booking_confirmed', '$notifTitle', '$notifMsg', '$notifLink')");

        if ($counselorUserId > 0) {
            $cNotifTitle = Database::$connection->real_escape_string('New Session Booked');
            $cNotifMsg   = Database::$connection->real_escape_string(
                $userName . ' has booked a session on ' . $sessionDateLabel . '.'
            );
            $cNotifLink  = Database::$connection->real_escape_string('/counselor/sessions');
            Database::iud("INSERT INTO notifications (user_id, type, title, message, link)
                           VALUES ($counselorUserId, 'new_booking', '$cNotifTitle', '$cNotifMsg', '$cNotifLink')");
        }
    }
}
