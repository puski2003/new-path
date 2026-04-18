<?php

class ExtendModel
{
    /**
     * Build the two standard extension options from the counselor's hourly rate.
     * 30 min = half rate, 60 min = full rate.
     */
    public static function buildOptions(float $hourlyRate): array
    {
        return [
            ['duration_minutes' => 30, 'fee' => round($hourlyRate / 2, 2)],
            ['duration_minutes' => 60, 'fee' => round($hourlyRate,     2)],
        ];
    }

    /**
     * Create a new extension request (expires in 10 minutes).
     * Automatically expires any existing pending request for the same session.
     * Returns the new extension_id, or 0 on failure.
     */
    public static function createRequest(int $sessionId, int $counselorId, int $userId, array $options): int
    {
        // Expire any still-pending requests for this session
        Database::iud(
            "UPDATE session_extension_requests
             SET status = 'expired'
             WHERE session_id = $sessionId AND status = 'pending'"
        );

        Database::setUpConnection();
        $optionsJson = Database::$connection->real_escape_string(json_encode($options));

        Database::iud(
            "INSERT INTO session_extension_requests
                (session_id, counselor_id, user_id, status, extension_options, expires_at)
             VALUES
                ($sessionId, $counselorId, $userId, 'pending',
                 '$optionsJson', DATE_ADD(NOW(), INTERVAL 10 MINUTE))"
        );

        $rs = Database::search(
            "SELECT extension_id FROM session_extension_requests
             WHERE session_id = $sessionId AND counselor_id = $counselorId AND status = 'pending'
             ORDER BY extension_id DESC LIMIT 1"
        );
        $row = $rs ? $rs->fetch_assoc() : null;
        return (int)($row['extension_id'] ?? 0);
    }

    /**
     * Fetch the current pending extension request for the user's session.
     * Expires stale requests first (check-on-read).
     */
    public static function getPendingForUser(int $userId, int $sessionId): ?array
    {
        Database::iud(
            "UPDATE session_extension_requests
             SET status = 'expired'
             WHERE user_id = $userId AND status = 'pending' AND expires_at <= NOW()"
        );

        $rs = Database::search(
            "SELECT extension_id, session_id, counselor_id, extension_options, expires_at,
                    GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), expires_at)) AS expires_in_seconds
             FROM session_extension_requests
             WHERE user_id = $userId AND session_id = $sessionId
               AND status = 'pending' AND expires_at > NOW()
             ORDER BY extension_id DESC LIMIT 1"
        );
        $row = $rs ? $rs->fetch_assoc() : null;
        if (!$row) {
            return null;
        }
        return [
            'extensionId'      => (int)$row['extension_id'],
            'sessionId'        => (int)$row['session_id'],
            'counselorId'      => (int)$row['counselor_id'],
            'options'          => json_decode((string)$row['extension_options'], true) ?: [],
            'expiresAt'        => $row['expires_at'],
            'expiresInSeconds' => (int)$row['expires_in_seconds'],
        ];
    }

    /**
     * Accept one of the offered durations.
     * Validates that the chosen duration+fee match what was offered (server-side).
     * Returns true on success.
     */
    public static function accept(int $extensionId, int $userId, int $durationMinutes): array
    {
        // Load the options from the request row
        $rs = Database::search(
            "SELECT extension_id, extension_options
             FROM session_extension_requests
             WHERE extension_id = $extensionId
               AND user_id = $userId
               AND status  = 'pending'
               AND expires_at > NOW()
             LIMIT 1"
        );
        $row = $rs ? $rs->fetch_assoc() : null;
        if (!$row) {
            return ['success' => false, 'error' => 'Request not found or expired.'];
        }

        $options = json_decode((string)$row['extension_options'], true) ?: [];
        $matched = null;
        foreach ($options as $opt) {
            if ((int)$opt['duration_minutes'] === $durationMinutes) {
                $matched = $opt;
                break;
            }
        }
        if (!$matched) {
            return ['success' => false, 'error' => 'Invalid duration selected.'];
        }

        $fee = (float)$matched['fee'];
        $feeFormatted = number_format($fee, 2, '.', '');

        Database::iud(
            "UPDATE session_extension_requests
             SET status = 'accepted',
                 selected_duration_minutes = $durationMinutes,
                 selected_fee = $feeFormatted,
                 responded_at = NOW()
             WHERE extension_id = $extensionId"
        );

        return ['success' => true, 'fee' => $fee, 'durationMinutes' => $durationMinutes];
    }

    /**
     * Decline the extension request.
     */
    public static function decline(int $extensionId, int $userId): bool
    {
        Database::iud(
            "UPDATE session_extension_requests
             SET status = 'declined', responded_at = NOW()
             WHERE extension_id = $extensionId
               AND user_id = $userId
               AND status  = 'pending'"
        );
        $rs = Database::search(
            "SELECT extension_id FROM session_extension_requests
             WHERE extension_id = $extensionId AND status = 'declined' LIMIT 1"
        );
        return $rs && $rs->num_rows > 0;
    }

    /**
     * Fetch the latest extension request for a counselor's session (for polling).
     */
    public static function getLatestForCounselor(int $counselorId, int $sessionId): ?array
    {
        $rs = Database::search(
            "SELECT extension_id, status, selected_duration_minutes, selected_fee, responded_at
             FROM session_extension_requests
             WHERE counselor_id = $counselorId AND session_id = $sessionId
             ORDER BY extension_id DESC LIMIT 1"
        );
        $row = $rs ? $rs->fetch_assoc() : null;
        if (!$row) {
            return null;
        }
        return [
            'extensionId'      => (int)$row['extension_id'],
            'status'           => $row['status'],
            'selectedDuration' => (int)($row['selected_duration_minutes'] ?? 0),
            'selectedFee'      => (float)($row['selected_fee'] ?? 0),
            'respondedAt'      => $row['responded_at'],
        ];
    }

    /**
     * Fetch an accepted extension request for the checkout page.
     * Returns null if not found or not owned by this user.
     */
    public static function getAccepted(int $extensionId, int $userId): ?array
    {
        $rs = Database::search(
            "SELECT ser.extension_id, ser.session_id, ser.counselor_id, ser.user_id,
                    ser.selected_duration_minutes, ser.selected_fee,
                    s.meeting_link
             FROM session_extension_requests ser
             JOIN sessions s ON s.session_id = ser.session_id
             WHERE ser.extension_id = $extensionId
               AND ser.user_id      = $userId
               AND ser.status       = 'accepted'
             LIMIT 1"
        );
        $row = $rs ? $rs->fetch_assoc() : null;
        if (!$row) {
            return null;
        }
        return [
            'extensionId'     => (int)$row['extension_id'],
            'sessionId'       => (int)$row['session_id'],
            'counselorId'     => (int)$row['counselor_id'],
            'userId'          => (int)$row['user_id'],
            'durationMinutes' => (int)($row['selected_duration_minutes'] ?? 0),
            'fee'             => (float)($row['selected_fee'] ?? 0),
            'meetingLink'     => $row['meeting_link'] ?? '',
        ];
    }

    /**
     * Mark extension as paid, update the parent session, record the transaction.
     * Returns true on success.
     */
    public static function markPaid(
        int    $extensionId,
        string $payhereOrderId,
        string $payherePaymentId,
        string $statusCode
    ): bool {
        $rs = Database::search(
            "SELECT ser.extension_id, ser.session_id, ser.counselor_id, ser.user_id,
                    ser.selected_duration_minutes, ser.selected_fee
             FROM session_extension_requests ser
             WHERE ser.extension_id = $extensionId AND ser.status = 'accepted'
             LIMIT 1"
        );
        $row = $rs ? $rs->fetch_assoc() : null;
        if (!$row) {
            return false;
        }

        $sessionId    = (int)$row['session_id'];
        $counselorId  = (int)$row['counselor_id'];
        $userId       = (int)$row['user_id'];
        $duration     = (int)$row['selected_duration_minutes'];
        $fee          = (float)$row['selected_fee'];
        $feeFormatted = number_format($fee, 2, '.', '');

        Database::setUpConnection();
        $safeOrderId   = Database::$connection->real_escape_string($payhereOrderId);
        $safePaymentId = Database::$connection->real_escape_string($payherePaymentId);
        $safeStatus    = Database::$connection->real_escape_string($statusCode);
        $uuid          = bin2hex(random_bytes(16));

        Database::iud(
            "INSERT INTO transactions
                (transaction_uuid, session_id, user_id, counselor_id,
                 amount, currency, payment_type, status,
                 payhere_order_id, payhere_payment_id, payhere_status_code,
                 processed_at, created_at, updated_at)
             VALUES
                ('$uuid', $sessionId, $userId, $counselorId,
                 $feeFormatted, 'LKR', 'session', 'completed',
                 '$safeOrderId', '$safePaymentId', '$safeStatus',
                 NOW(), NOW(), NOW())"
        );

        $txRs  = Database::search("SELECT transaction_id FROM transactions WHERE transaction_uuid = '$uuid' LIMIT 1");
        $txRow = $txRs ? $txRs->fetch_assoc() : null;
        $txId  = (int)($txRow['transaction_id'] ?? 0);

        $txClause = $txId > 0 ? ", transaction_id = $txId" : '';
        Database::iud(
            "UPDATE session_extension_requests
             SET status = 'paid' $txClause
             WHERE extension_id = $extensionId"
        );

        Database::iud(
            "UPDATE sessions
             SET extended_minutes = COALESCE(extended_minutes, 0) + $duration,
                 extension_fee    = COALESCE(extension_fee, 0) + $feeFormatted,
                 updated_at       = NOW()
             WHERE session_id = $sessionId"
        );

        return true;
    }

    /**
     * Load full extension details (with session, counselor, user info) for success/email pages.
     */
    public static function getFullById(int $extensionId): ?array
    {
        $rs = Database::search(
            "SELECT ser.extension_id, ser.session_id, ser.counselor_id, ser.user_id,
                    ser.status, ser.selected_duration_minutes, ser.selected_fee,
                    s.meeting_link, s.session_datetime,
                    s.duration_minutes, s.extended_minutes,
                    COALESCE(cu.display_name, CONCAT(cu.first_name,' ',cu.last_name), cu.username, 'Counselor') AS counselor_name,
                    cu.email        AS counselor_email,
                    cu.user_id      AS counselor_user_id,
                    COALESCE(uu.display_name, CONCAT(uu.first_name,' ',uu.last_name), uu.username, 'Client') AS user_name,
                    uu.email        AS user_email
             FROM session_extension_requests ser
             JOIN sessions   s  ON s.session_id       = ser.session_id
             JOIN counselors c  ON c.counselor_id     = ser.counselor_id
             JOIN users      cu ON cu.user_id         = c.user_id
             JOIN users      uu ON uu.user_id         = ser.user_id
             WHERE ser.extension_id = $extensionId
             LIMIT 1"
        );
        $row = $rs ? $rs->fetch_assoc() : null;
        if (!$row) {
            return null;
        }
        return [
            'extensionId'      => (int)$row['extension_id'],
            'sessionId'        => (int)$row['session_id'],
            'counselorId'      => (int)$row['counselor_id'],
            'counselorUserId'  => (int)$row['counselor_user_id'],
            'userId'           => (int)$row['user_id'],
            'status'           => $row['status'],
            'durationMinutes'  => (int)($row['selected_duration_minutes'] ?? 0),
            'fee'              => (float)($row['selected_fee'] ?? 0),
            'meetingLink'      => $row['meeting_link'] ?? '',
            'sessionDatetime'  => $row['session_datetime'],
            'originalDuration' => (int)($row['duration_minutes'] ?? 60),
            'extendedMinutes'  => (int)($row['extended_minutes'] ?? 0),
            'counselorName'    => $row['counselor_name'],
            'counselorEmail'   => $row['counselor_email'] ?? '',
            'userName'         => $row['user_name'],
            'userEmail'        => $row['user_email'] ?? '',
        ];
    }
}
