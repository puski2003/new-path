<?php

class BookingModel
{
    public const PAYHERE_MERCHANT_ID = '1233865';
    public const PAYHERE_MERCHANT_SECRET = 'MjI4MzM1ODk5MDE4NjQyMjI0MTUzODY2Njg3OTQxMTUwMjQxMjIwMQ==';

    // ------------------------------------------------------------------
    // Slot availability
    // ------------------------------------------------------------------

    /**
     * Check if a slot is already taken (confirmed session OR active hold).
     * Returns true when the slot is FREE.
     */
    public static function isSlotAvailable(int $counselorId, string $slotDatetime): bool
    {
        // Release expired holds first (check-on-read pattern — PRD §5.5 note)
        self::releaseExpiredHolds($counselorId, $slotDatetime);

        // Check for a confirmed/scheduled session at this exact datetime
        $rs = Database::search(
            "SELECT 1 FROM sessions
             WHERE counselor_id = $counselorId
               AND session_datetime = '$slotDatetime'
               AND status IN ('scheduled','confirmed','in_progress')
             LIMIT 1"
        );
        if ($rs && $rs->num_rows > 0) {
            return false;
        }

        // Check for an active hold on this slot
        $rs2 = Database::search(
            "SELECT 1 FROM booking_holds
             WHERE counselor_id = $counselorId
               AND slot_datetime  = '$slotDatetime'
               AND status         = 'held'
               AND expires_at     > NOW()
             LIMIT 1"
        );
        return !($rs2 && $rs2->num_rows > 0);
    }

    // ------------------------------------------------------------------
    // Slot locking
    // ------------------------------------------------------------------

    /**
     * Create a 15-minute hold for the requested slot.
     * Returns the new hold_id on success, 0 on failure (slot taken).
     */
    public static function lockSlot(int $counselorId, int $userId, string $slotDatetime, int $durationMinutes = 60): int
    {
        if (!self::isSlotAvailable($counselorId, $slotDatetime)) {
            return 0;
        }

        Database::iud(
            "INSERT INTO booking_holds
                (counselor_id, user_id, slot_datetime, duration_minutes, status, held_at, expires_at)
             VALUES
                ($counselorId, $userId, '$slotDatetime', $durationMinutes,
                 'held', NOW(), DATE_ADD(NOW(), INTERVAL 15 MINUTE))"
        );

        // Retrieve the inserted hold_id
        $rs = Database::search(
            "SELECT hold_id FROM booking_holds
             WHERE counselor_id  = $counselorId
               AND user_id       = $userId
               AND slot_datetime = '$slotDatetime'
               AND status        = 'held'
             ORDER BY hold_id DESC
             LIMIT 1"
        );
        $row = $rs ? $rs->fetch_assoc() : null;
        return (int)($row['hold_id'] ?? 0);
    }

    /**
     * Confirm a hold (payment succeeded → slot is now permanently taken).
     */
    public static function confirmHold(int $holdId): void
    {
        Database::iud(
            "UPDATE booking_holds
             SET status = 'confirmed'
             WHERE hold_id = $holdId AND status = 'held'"
        );
    }

    /**
     * Release a hold (payment cancelled / failed).
     */
    public static function releaseHold(int $holdId): void
    {
        Database::iud(
            "UPDATE booking_holds
             SET status = 'released'
             WHERE hold_id = $holdId AND status = 'held'"
        );
    }

    /**
     * Release all expired holds for a given counselor+slot (called on read).
     */
    private static function releaseExpiredHolds(int $counselorId, string $slotDatetime): void
    {
        Database::iud(
            "UPDATE booking_holds
             SET status = 'released'
             WHERE counselor_id  = $counselorId
               AND slot_datetime = '$slotDatetime'
               AND status        = 'held'
               AND expires_at   <= NOW()"
        );
    }

    /**
     * Fetch a hold row by hold_id (for return/cancel handlers).
     */
    public static function getHold(int $holdId): ?array
    {
        $rs = Database::search(
            "SELECT hold_id, counselor_id, user_id, slot_datetime, duration_minutes, status, expires_at
             FROM booking_holds
             WHERE hold_id = $holdId
             LIMIT 1"
        );
        $row = $rs ? $rs->fetch_assoc() : null;
        if (!$row) {
            return null;
        }
        return [
            'holdId'          => (int)$row['hold_id'],
            'counselorId'     => (int)$row['counselor_id'],
            'userId'          => (int)$row['user_id'],
            'slotDatetime'    => $row['slot_datetime'],
            'durationMinutes' => (int)$row['duration_minutes'],
            'status'          => $row['status'],
            'expiresAt'       => $row['expires_at'],
        ];
    }

    // ------------------------------------------------------------------
    // Counselor data (used on checkout page)
    // ------------------------------------------------------------------

    public static function getCounselorForBooking(int $counselorId): ?array
    {
        $rs = Database::search(
            "SELECT c.counselor_id, c.title, c.specialty, c.consultation_fee, c.availability_schedule,
                    COALESCE(u.display_name, CONCAT(u.first_name,' ',u.last_name), u.username, 'Counselor') AS name,
                    u.user_id, u.profile_picture, u.email
             FROM counselors c
             JOIN users u ON u.user_id = c.user_id
             WHERE c.counselor_id = $counselorId
               AND u.role = 'counselor'
             LIMIT 1"
        );
        $row = $rs ? $rs->fetch_assoc() : null;
        if (!$row) {
            return null;
        }
        return [
            'counselorId' => (int)$row['counselor_id'],
            'userId'      => (int)$row['user_id'],
            'name'        => $row['name'],
            'title'       => $row['title'] ?: 'Counselor',
            'specialty'   => $row['specialty'] ?: 'Counseling',
            'fee'         => (float)($row['consultation_fee'] ?? 0),
            'profilePic'  => $row['profile_picture'] ?: '/assets/img/avatar.png',
            'email'       => $row['email'] ?? null,
        ];
    }

    /**
     * Fetch the email address for a user by user_id.
     * Returns empty string if not found.
     */
    public static function getUserEmail(int $userId): string
    {
        $rs = Database::search(
            "SELECT email FROM users WHERE user_id = $userId LIMIT 1"
        );
        $row = $rs ? $rs->fetch_assoc() : null;
        return (string)($row['email'] ?? '');
    }

    // ------------------------------------------------------------------
    // Session + Transaction creation (called from return handler)
    // ------------------------------------------------------------------

    /**
     * Create a session record after successful payment.
     * Returns the new session_id on success, 0 on failure.
     */
    public static function createSession(
        int    $userId,
        int    $counselorId,
        string $sessionDatetime,
        int    $durationMinutes,
        string $sessionType,
        string $meetingLink = '',
        string $meetSpaceName = ''
    ): int {
        $safeType      = in_array($sessionType, ['video','audio','chat','in_person']) ? $sessionType : 'video';
        $safeMeeting   = addslashes($meetingLink);
        $safeDt        = addslashes($sessionDatetime);
        $safeSpaceName = addslashes($meetSpaceName);

        Database::iud(
            "INSERT INTO sessions
                (user_id, counselor_id, session_datetime, duration_minutes,
                 session_type, status, meeting_link, meet_space_name, created_at, updated_at)
             VALUES
                ($userId, $counselorId, '$safeDt', $durationMinutes,
                 '$safeType', 'scheduled',
                 " . ($safeMeeting !== '' ? "'$safeMeeting'" : 'NULL') . ",
                 " . ($safeSpaceName !== '' ? "'$safeSpaceName'" : 'NULL') . ",
                 NOW(), NOW())"
        );

        $rs = Database::search(
            "SELECT session_id FROM sessions
             WHERE user_id = $userId AND counselor_id = $counselorId
               AND session_datetime = '$safeDt'
             ORDER BY session_id DESC
             LIMIT 1"
        );
        $row = $rs ? $rs->fetch_assoc() : null;
        return (int)($row['session_id'] ?? 0);
    }

    /**
     * Record a PayHere transaction after payment confirmation.
     */
    public static function createTransaction(
        int    $sessionId,
        int    $userId,
        int    $counselorId,
        float  $amount,
        string $payhereOrderId,
        string $payherePaymentId,
        string $payhereStatusCode
    ): void {
        $uuid               = bin2hex(random_bytes(16));
        $safeOrderId        = addslashes($payhereOrderId);
        $safePaymentId      = addslashes($payherePaymentId);
        $safeStatusCode     = addslashes($payhereStatusCode);
        $formattedAmount    = number_format($amount, 2, '.', '');

        Database::iud(
            "INSERT INTO transactions
                (transaction_uuid, session_id, user_id, counselor_id,
                 amount, currency, payment_type, status,
                 payhere_order_id, payhere_payment_id, payhere_status_code,
                 processed_at, created_at, updated_at)
             VALUES
                ('$uuid', $sessionId, $userId, $counselorId,
                 $formattedAmount, 'LKR', 'session', 'completed',
                 '$safeOrderId', '$safePaymentId', '$safeStatusCode',
                 NOW(), NOW(), NOW())"
        );
    }

    // ------------------------------------------------------------------
    // Reschedule free-credit helpers
    // ------------------------------------------------------------------

    /**
     * Return an unused approved reschedule credit for this user+counselor, or null.
     */
    public static function getActiveFreeCredit(int $userId, int $counselorId): ?array
    {
        $rs = Database::search(
            "SELECT rr.request_id, rr.counselor_id
             FROM reschedule_requests rr
             WHERE rr.user_id      = $userId
               AND rr.counselor_id = $counselorId
               AND rr.status       = 'approved'
               AND rr.credit_used  = 0
             ORDER BY rr.reviewed_at DESC
             LIMIT 1"
        );
        $row = $rs ? $rs->fetch_assoc() : null;
        if (!$row) return null;
        return [
            'requestId'   => (int)$row['request_id'],
            'counselorId' => (int)$row['counselor_id'],
        ];
    }

    /**
     * Mark a reschedule credit as consumed. Safe to call only after the
     * session has been successfully created.
     */
    public static function consumeFreeCredit(int $requestId, int $userId): void
    {
        Database::iud(
            "UPDATE reschedule_requests
             SET credit_used = 1
             WHERE request_id = $requestId AND user_id = $userId AND credit_used = 0"
        );
    }

    // ------------------------------------------------------------------
    // Income helper (used by counselor dashboard)
    // ------------------------------------------------------------------

    /**
     * Total completed earnings for a counselor (sum of completed transactions).
     */
    public static function getTotalIncomeByCounselor(int $counselorId): float
    {
        $rs = Database::search(
            "SELECT COALESCE(SUM(amount), 0) AS total
             FROM transactions
             WHERE counselor_id = $counselorId
               AND status       = 'completed'"
        );
        $row = $rs ? $rs->fetch_assoc() : null;
        return (float)($row['total'] ?? 0);
    }

    public static function generatePayHereHash(string $orderId, string $amount, string $currency = 'LKR'): ?string
    {
        $safeOrderId = trim($orderId);
        $safeAmount = trim($amount);
        $safeCurrency = trim($currency);

        if ($safeOrderId === '' || $safeAmount === '' || !is_numeric($safeAmount) || (float) $safeAmount <= 0 || $safeCurrency === '') {
            return null;
        }

        $formattedAmount = number_format((float) $safeAmount, 2, '.', '');
        $hashedSecret = strtoupper(md5(self::PAYHERE_MERCHANT_SECRET));
        $raw = self::PAYHERE_MERCHANT_ID . $safeOrderId . $formattedAmount . $safeCurrency . $hashedSecret;

        return strtoupper(md5($raw));
    }
}
