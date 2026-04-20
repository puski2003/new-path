<?php

class NoShowModel
{
    public static function reportNoShow(int $userId, int $sessionId, string $description): bool
    {
        if ($sessionId <= 0) return false;

        // Verify the session belongs to this user and is completed/past
        $rs = Database::search(
            "SELECT session_id FROM sessions
             WHERE session_id = $sessionId
               AND user_id   = $userId
               AND (status IN ('completed','no_show') OR session_datetime < NOW())
             LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;

        // Idempotency: one report per user+session
        $existing = Database::search(
            "SELECT dispute_id FROM session_disputes
             WHERE session_id = $sessionId AND reported_by = $userId
             LIMIT 1"
        );
        if ($existing && $existing->num_rows > 0) return false;

        Database::setUpConnection();
        $safeDesc = Database::$connection->real_escape_string(trim($description));

        Database::iud(
            "INSERT INTO session_disputes (session_id, reported_by, reason, description)
             VALUES ($sessionId, $userId, 'no_show', '$safeDesc')"
        );

        // Notify the counselor that a no-show was reported
        $counselorRs = Database::search(
            "SELECT u.user_id
             FROM sessions s
             JOIN counselors c ON c.counselor_id = s.counselor_id
             JOIN users u ON u.user_id = c.user_id
             WHERE s.session_id = $sessionId
             LIMIT 1"
        );
        if ($counselorRs && ($cRow = $counselorRs->fetch_assoc())) {
            $counselorUserId = (int)($cRow['user_id'] ?? 0);
            if ($counselorUserId > 0) {
                $notifTitle = Database::$connection->real_escape_string('Absence Report Filed');
                $notifMsg   = Database::$connection->real_escape_string('A client has reported that you did not attend a session. This will be reviewed by our admin team.');
                $notifLink  = Database::$connection->real_escape_string('/counselor/sessions?tab=disputes');
                Database::iud(
                    "INSERT INTO notifications (user_id, type, title, message, link)
                     VALUES ($counselorUserId, 'no_show_reported', '$notifTitle', '$notifMsg', '$notifLink')"
                );
            }
        }

        $txRs = Database::search(
            "SELECT transaction_id, amount, currency
             FROM transactions
             WHERE session_id = $sessionId
               AND user_id = $userId
               AND status = 'completed'
             ORDER BY created_at DESC
             LIMIT 1"
        );
        $txRow = $txRs ? $txRs->fetch_assoc() : null;

        if ($txRow) {
            $transactionId = (int)($txRow['transaction_id'] ?? 0);
            if ($transactionId > 0) {
                $refundExisting = Database::search(
                    "SELECT dispute_id FROM refund_disputes
                     WHERE transaction_id = $transactionId
                       AND user_id = $userId
                       AND issue_type = 'missed_session'
                     LIMIT 1"
                );
                if (!$refundExisting || $refundExisting->num_rows === 0) {
                    $requestedAmount = isset($txRow['amount']) ? number_format((float)$txRow['amount'], 2, '.', '') : '0.00';
                    Database::iud(
                        "INSERT INTO refund_disputes (transaction_id, user_id, issue_type, description, requested_amount, status)
                         VALUES ($transactionId, $userId, 'missed_session', '$safeDesc', $requestedAmount, 'pending')"
                    );
                }
            }
        }

        return true;
    }
}
