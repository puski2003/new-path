<?php

class ViewModel
{
    public static function getSessionById(int $userId, int $sessionId): ?array
    {
        if ($sessionId <= 0) return null;

        $rs = Database::search(
            "SELECT s.session_id, s.user_id, s.counselor_id, s.session_datetime, s.duration_minutes,
                    s.session_type, s.status, s.location, s.meeting_link, s.session_notes,
                    s.rating, s.review, s.created_at, s.updated_at,
                    c.title AS counselor_title, c.specialty, c.bio,
                    COALESCE(u.display_name, CONCAT(u.first_name, ' ', u.last_name), u.username, 'Counselor') AS counselor_name,
                    u.profile_picture,
                    (SELECT COUNT(1) FROM session_disputes sd
                     WHERE sd.session_id = s.session_id AND sd.reported_by = s.user_id) AS has_dispute
             FROM sessions s
             JOIN counselors c ON c.counselor_id = s.counselor_id
             JOIN users u ON u.user_id = c.user_id
             WHERE s.user_id = $userId
               AND s.session_id = $sessionId
             LIMIT 1"
        );

        $row = $rs->fetch_assoc();
        if (!$row) return null;

        if (
            in_array($row['status'], ['scheduled', 'confirmed'], true)
            && strtotime((string)$row['session_datetime']) < time()
        ) {
            Database::iud(
                "UPDATE sessions SET status = 'completed', updated_at = NOW()
                 WHERE session_id = {$row['session_id']} AND status IN ('scheduled','confirmed')"
            );
            $row['status'] = 'completed';
        }

        $transaction = null;
        $txRs = Database::search(
            "SELECT t.transaction_id, t.transaction_uuid, t.payment_method_id, t.amount,
                    t.currency, t.payment_type, t.status, t.payhere_order_id,
                    t.payhere_payment_id, t.processed_at, t.created_at
             FROM transactions t
             WHERE t.session_id = $sessionId
             ORDER BY t.created_at DESC
             LIMIT 1"
        );
        $tx = $txRs->fetch_assoc();
        if ($tx) {
            $transaction = $tx;
        }

        $cardLast4 = '';
        $cardExpiry = '';
        $cardBrand = '';
        if (!empty($transaction['payment_method_id'])) {
            $paymentMethodId = (int)$transaction['payment_method_id'];
            $pmRs = Database::search(
                "SELECT card_last_four, card_brand, expiry_month, expiry_year
                 FROM payment_methods
                 WHERE payment_method_id = $paymentMethodId
                 LIMIT 1"
            );
            $pm = $pmRs->fetch_assoc();
            if ($pm) {
                if (!empty($pm['card_last_four'])) {
                    $cardLast4 = (string)$pm['card_last_four'];
                }
                if (!empty($pm['card_brand'])) {
                    $cardBrand = strtoupper((string)$pm['card_brand']);
                }
                if (!empty($pm['expiry_month']) && !empty($pm['expiry_year'])) {
                    $cardExpiry = str_pad((string)$pm['expiry_month'], 2, '0', STR_PAD_LEFT) . '/' . substr((string)$pm['expiry_year'], -2);
                }
            }
        }

        $sessionDateTime = strtotime((string)$row['session_datetime']);
        $joinWindow = $sessionDateTime ? date('Y-m-d H:i', $sessionDateTime - (15 * 60)) . ' Asia/Colombo' : null;
        $bookedAt = !empty($row['created_at']) ? date('Y-m-d H:i', strtotime($row['created_at'])) . ' Asia/Colombo' : null;
        $paymentCaptured = !empty($transaction['processed_at'])
            ? date('Y-m-d H:i', strtotime($transaction['processed_at'])) . ' Asia/Colombo'
            : (!empty($transaction['created_at']) ? date('Y-m-d H:i', strtotime($transaction['created_at'])) . ' Asia/Colombo' : null);
        $bookingId = !empty($transaction['transaction_uuid'])
            ? (string)$transaction['transaction_uuid']
            : ('S' . str_pad((string)$row['session_id'], 10, '0', STR_PAD_LEFT));
        $amount = isset($transaction['amount']) ? (float)$transaction['amount'] : null;
        $currency = !empty($transaction['currency']) ? (string)$transaction['currency'] : 'LKR';
        $hasPayment = $transaction !== null;
        $paymentMethodLabel = $cardLast4 !== ''
            ? trim(($cardBrand !== '' ? $cardBrand . ' ' : '') . '**** ' . $cardLast4)
            : ($hasPayment ? 'Card details unavailable' : 'No payment record');

        return [
            'sessionId' => (int)$row['session_id'],
            'counselorId' => (int)$row['counselor_id'],
            'doctorName' => $row['counselor_name'] ?? 'Counselor',
            'doctorTitle' => $row['counselor_title'] ?: 'Counselor',
            'specialization' => $row['specialty'] ?: 'Counseling',
            'profilePicture' => $row['profile_picture'] ?: '/assets/img/avatar.png',
            'sessionTypeRaw' => $row['session_type'] ?? 'video',
            'sessionType' => self::formatSessionType((string)($row['session_type'] ?? 'video')),
            'status' => $row['status'] ?? 'scheduled',
            'location' => $row['location'] ?: ucfirst((string)($row['session_type'] ?? 'video')),
            'bookingId' => $bookingId,
            'bookedAt' => $bookedAt ?: 'Not available',
            'paymentCaptured' => $paymentCaptured ?: 'Not available',
            'joinWindow' => $joinWindow ?: 'Not available',
            'notes' => trim((string)($row['session_notes'] ?? '')) !== '' ? trim((string)$row['session_notes']) : 'No session notes available.',
            'cardNumber' => $paymentMethodLabel,
            'cardExpiry' => $cardExpiry,
            'cardBrand' => $cardBrand,
            'hasPayment' => $hasPayment,
            'amount' => $amount,
            'amountFormatted' => $amount !== null ? number_format($amount, 2) . ' ' . $currency : 'Not available',
            'currency' => $currency,
            'paymentStatus' => !empty($transaction['status']) ? ucfirst((string)$transaction['status']) : 'Not available',
            'paymentType' => !empty($transaction['payment_type']) ? ucfirst((string)$transaction['payment_type']) : 'Session',
            'transactionId' => !empty($transaction['transaction_id']) ? (int)$transaction['transaction_id'] : null,
            'transactionUuid' => $transaction['transaction_uuid'] ?? '',
            'payhereOrderId' => $transaction['payhere_order_id'] ?? '',
            'payherePaymentId' => $transaction['payhere_payment_id'] ?? '',
            'orderUrl' => $hasPayment ? '/user/sessions/order?id=' . (int)$row['session_id'] : '',
            'receiptUrl' => $hasPayment ? '/user/sessions/receipt?id=' . (int)$row['session_id'] . '&print=1' : '',
            'meetingLink'     => $row['meeting_link'] ?: '',
            'sessionDateTime' => $row['session_datetime'],
            'rating'          => $row['rating'] !== null ? (int)$row['rating'] : null,
            'review'          => $row['review'] ?? '',
            'hasReview'       => $row['rating'] !== null,
            'hasDispute'      => (int)($row['has_dispute'] ?? 0) > 0,
        ];
    }

    public static function notifyExtensionAccepted(int $extId, int $durationMinutes): void
    {
        $extRow = Database::search(
            "SELECT ser.counselor_id, c.user_id AS counselor_user_id, s.session_id
             FROM session_extension_requests ser
             JOIN counselors c ON c.counselor_id = ser.counselor_id
             JOIN sessions   s ON s.session_id   = ser.session_id
             WHERE ser.extension_id = $extId LIMIT 1"
        );
        $extData = $extRow ? $extRow->fetch_assoc() : null;
        if (!$extData) return;

        $cUserId = (int)$extData['counselor_user_id'];
        $sId     = (int)$extData['session_id'];
        Database::setUpConnection();
        $t = Database::$connection->real_escape_string('Extension Request Accepted');
        $m = Database::$connection->real_escape_string('The client accepted the ' . $durationMinutes . '-minute extension. Awaiting payment.');
        $l = Database::$connection->real_escape_string('/counselor/sessions/workspace?session_id=' . $sId);
        Database::iud("INSERT INTO notifications (user_id, type, title, message, link) VALUES ($cUserId, 'extension_accepted', '$t', '$m', '$l')");
    }

    public static function notifyExtensionDeclined(int $extId): void
    {
        $extRow = Database::search(
            "SELECT ser.counselor_id, c.user_id AS counselor_user_id, s.session_id
             FROM session_extension_requests ser
             JOIN counselors c ON c.counselor_id = ser.counselor_id
             JOIN sessions   s ON s.session_id   = ser.session_id
             WHERE ser.extension_id = $extId LIMIT 1"
        );
        $extData = $extRow ? $extRow->fetch_assoc() : null;
        if (!$extData) return;

        $cUserId = (int)$extData['counselor_user_id'];
        $sId     = (int)$extData['session_id'];
        Database::setUpConnection();
        $t = Database::$connection->real_escape_string('Extension Request Declined');
        $m = Database::$connection->real_escape_string('The client declined your session extension request.');
        $l = Database::$connection->real_escape_string('/counselor/sessions/workspace?session_id=' . $sId);
        Database::iud("INSERT INTO notifications (user_id, type, title, message, link) VALUES ($cUserId, 'extension_declined', '$t', '$m', '$l')");
    }

    private static function formatSessionType(string $sessionType): string
    {
        return match ($sessionType) {
            'video' => '1:1 Video',
            'audio' => '1:1 Audio',
            'chat' => '1:1 Chat',
            'in_person' => 'In Person',
            default => '1:1',
        };
    }
}