<?php

class FreeBookModel
{
    public static function recordZeroTransaction(int $sessionId, int $userId, int $counselorId, int $creditId): void
    {
        Database::setUpConnection();
        $uuid      = bin2hex(random_bytes(16));
        $creditRef = 'CREDIT-' . $creditId;
        $safeRef   = Database::$connection->real_escape_string($creditRef);
        Database::iud(
            "INSERT INTO transactions
                (transaction_uuid, session_id, user_id, counselor_id,
                 amount, currency, payment_type, status,
                 payhere_order_id, processed_at, created_at, updated_at)
             VALUES
                ('$uuid', $sessionId, $userId, $counselorId,
                 0.00, 'LKR', 'session', 'completed',
                 '$safeRef', NOW(), NOW(), NOW())"
        );
    }

    public static function insertNotifications(
        int    $userId,
        int    $counselorUserId,
        string $userName,
        string $counselorName,
        string $sessionDateLabel
    ): void {
        Database::setUpConnection();

        $notifTitle = Database::$connection->real_escape_string('Rescheduled Session Confirmed');
        $notifMsg   = Database::$connection->real_escape_string(
            'Your rescheduled session with ' . $counselorName . ' on ' . $sessionDateLabel . ' is confirmed. No charge applied.'
        );
        $notifLink  = Database::$connection->real_escape_string('/user/sessions');
        Database::iud("INSERT INTO notifications (user_id, type, title, message, link)
                       VALUES ($userId, 'booking_confirmed', '$notifTitle', '$notifMsg', '$notifLink')");

        if ($counselorUserId > 0) {
            $cTitle = Database::$connection->real_escape_string('Rescheduled Session Booked');
            $cMsg   = Database::$connection->real_escape_string(
                $userName . ' completed their reschedule booking for ' . $sessionDateLabel . '.'
            );
            $cLink  = Database::$connection->real_escape_string('/counselor/sessions');
            Database::iud("INSERT INTO notifications (user_id, type, title, message, link)
                           VALUES ($counselorUserId, 'new_booking', '$cTitle', '$cMsg', '$cLink')");
        }
    }
}
