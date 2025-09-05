<?php

class SessionsModel
{
    public static function getReportItems(int $userId, int $page = 1, int $perPage = 5): array
    {
        $safePage = max(1, $page);
        $safePerPage = max(1, min(50, $perPage));
        $offset = ($safePage - 1) * $safePerPage;

        $countRs = Database::search(
            "SELECT COUNT(*) AS total
             FROM session_disputes sd
             INNER JOIN sessions s ON s.session_id = sd.session_id
             WHERE sd.reported_by = $userId"
        );
        $countRow = $countRs ? ($countRs->fetch_assoc() ?: []) : [];
        $total = (int)($countRow['total'] ?? 0);

        $rs = Database::search(
            "SELECT sd.dispute_id, sd.reason, sd.description, sd.status AS report_status,
                    sd.admin_note AS report_admin_note, sd.created_at AS reported_at,
                    s.session_id, s.session_datetime, s.counselor_id, s.status AS session_status,
                    COALESCE(u.display_name, CONCAT(u.first_name, ' ', u.last_name), u.username, 'Counselor') AS counselor_name,
                    u.profile_picture,
                    t.transaction_id, t.transaction_uuid, t.amount, t.currency,
                    rd.status AS refund_status, rd.admin_notes AS refund_admin_notes,
                    rd.requested_amount, rd.refunded_amount, rd.created_at AS refund_created_at
             FROM session_disputes sd
             INNER JOIN sessions s ON s.session_id = sd.session_id
             INNER JOIN counselors c ON c.counselor_id = s.counselor_id
             INNER JOIN users u ON u.user_id = c.user_id
             LEFT JOIN transactions t ON t.session_id = s.session_id
             LEFT JOIN refund_disputes rd
                    ON rd.transaction_id = t.transaction_id
                   AND rd.user_id = sd.reported_by
                   AND rd.issue_type = 'missed_session'
             WHERE sd.reported_by = $userId
             ORDER BY sd.created_at DESC
             LIMIT $safePerPage OFFSET $offset"
        );

        $items = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $items[] = [
                'disputeId' => (int)$row['dispute_id'],
                'sessionId' => (int)$row['session_id'],
                'counselorId' => (int)$row['counselor_id'],
                'counselorName' => $row['counselor_name'] ?? 'Counselor',
                'profilePicture' => $row['profile_picture'] ?: '/assets/img/avatar.png',
                'sessionDate' => !empty($row['session_datetime']) ? date('M j, Y \a\t g:ia', strtotime($row['session_datetime'])) : '',
                'sessionStatus' => $row['session_status'] ?? '',
                'reason' => $row['reason'] ?? 'no_show',
                'description' => trim((string)($row['description'] ?? '')),
                'reportStatus' => $row['report_status'] ?? 'pending',
                'reportAdminNote' => trim((string)($row['report_admin_note'] ?? '')),
                'reportedAt' => !empty($row['reported_at']) ? date('M j, Y g:i A', strtotime($row['reported_at'])) : '',
                'refundStatus' => $row['refund_status'] ?? null,
                'refundAdminNotes' => trim((string)($row['refund_admin_notes'] ?? '')),
                'requestedAmount' => $row['requested_amount'] !== null ? number_format((float)$row['requested_amount'], 2) . ' ' . ($row['currency'] ?: 'LKR') : null,
                'refundedAmount' => $row['refunded_amount'] !== null ? number_format((float)$row['refunded_amount'], 2) . ' ' . ($row['currency'] ?: 'LKR') : null,
                'transactionUuid' => $row['transaction_uuid'] ?? '',
            ];
        }

        return [
            'items' => $items,
            'total' => $total,
            'page' => $safePage,
            'totalPages' => max(1, (int)ceil($total / $safePerPage)),
        ];
    }

    public static function getSessionsByType(int $userId, string $type, int $page = 1, int $perPage = 5): array
    {
        $safePage = max(1, $page);
        $safePerPage = max(1, min(50, $perPage));
        $offset = ($safePage - 1) * $safePerPage;

        $isUpcoming = $type === 'upcoming';
        $where = $isUpcoming
            ? "s.session_datetime >= NOW() AND s.status IN ('scheduled','confirmed','in_progress')"
            : "(s.session_datetime < NOW() OR s.status IN ('completed','cancelled','no_show'))";

        $order = $isUpcoming ? 's.session_datetime ASC' : 's.session_datetime DESC';

        $countRs = Database::search(
            "SELECT COUNT(*) AS total
             FROM sessions s
             WHERE s.user_id = $userId
               AND $where"
        );
        $countRow = $countRs->fetch_assoc();
        $total = (int)($countRow['total'] ?? 0);

        $rs = Database::search(
            "SELECT s.session_id, s.counselor_id, s.session_datetime, s.session_type, s.status, s.location, s.meeting_link,
                    s.rating,
                    c.title AS counselor_title, c.specialty,
                    u.profile_picture,
                    COALESCE(u.display_name, CONCAT(u.first_name, ' ', u.last_name), u.username, 'Counselor') AS counselor_name,
                    (SELECT COUNT(1) FROM session_disputes sd
                     WHERE sd.session_id = s.session_id AND sd.reported_by = s.user_id) AS has_dispute,
                    (SELECT rr.status FROM reschedule_requests rr
                     WHERE rr.session_id = s.session_id
                     ORDER BY rr.requested_at DESC LIMIT 1) AS reschedule_status,
                    (SELECT rr.counselor_note FROM reschedule_requests rr
                     WHERE rr.session_id = s.session_id
                     ORDER BY rr.requested_at DESC LIMIT 1) AS reschedule_note
             FROM sessions s
             JOIN counselors c ON c.counselor_id = s.counselor_id
             JOIN users u ON u.user_id = c.user_id
             WHERE s.user_id = $userId
               AND $where
             ORDER BY $order
             LIMIT $safePerPage OFFSET $offset"
        );

        $items = [];
        while ($row = $rs->fetch_assoc()) {
            $items[] = self::mapSessionCard($row, $isUpcoming ? 'upcoming' : 'history');
        }

        return [
            'items' => $items,
            'total' => $total,
            'page' => $safePage,
            'totalPages' => max(1, (int)ceil($total / $safePerPage)),
        ];
    }

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

        // Auto-complete sessions whose datetime has passed but status was never updated.
        // Sessions are created as 'scheduled' and nothing flips them to 'completed' automatically.
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

    private static function mapSessionCard(array $row, string $type): array
    {
        $sessionDate = strtotime((string)$row['session_datetime']);
        $formattedDayTime = $sessionDate ? date('M j, Y \a\t g:ia', $sessionDate) : '';

        $schedule = $type === 'upcoming'
            ? (self::formatSessionTypeShort((string)($row['session_type'] ?? 'video')) . ' · ' . $formattedDayTime)
            : ('Completed · ' . $formattedDayTime);

        return [
            'sessionId'        => (int)$row['session_id'],
            'counselorId'      => (int)$row['counselor_id'],
            'doctorName'       => $row['counselor_name'] ?? 'Counselor',
            'specialty'        => $row['specialty'] ?: 'Addiction Specialist',
            'profilePicture'   => $row['profile_picture'] ?: '/assets/img/avatar.png',
            'schedule'         => $schedule,
            'sessionType'      => $type,
            'status'           => $row['status'] ?? '',
            'meetingLink'      => $row['meeting_link'] ?? '',
            'hasReview'        => $row['rating'] !== null,
            'hasDispute'       => (int)($row['has_dispute'] ?? 0) > 0,
            'rescheduleStatus' => $row['reschedule_status'] ?? null,
            'rescheduleNote'   => $row['reschedule_note']   ?? '',
        ];
    }

    // ------------------------------------------------------------------
    // Reschedule request (user-initiated)
    // ------------------------------------------------------------------

    /**
     * Create a reschedule request for an upcoming session.
     * Only one pending request per session is allowed.
     * Returns false if the session doesn't belong to the user, is not upcoming,
     * or already has a pending request.
     */
    public static function requestReschedule(int $userId, int $sessionId, string $reason): bool
    {
        if ($sessionId <= 0) return false;

        $rs = Database::search(
            "SELECT session_id, counselor_id FROM sessions
             WHERE session_id = $sessionId
               AND user_id    = $userId
               AND status     IN ('scheduled','confirmed')
               AND session_datetime > NOW()
             LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;

        $session     = $rs->fetch_assoc();
        $counselorId = (int)$session['counselor_id'];

        // Block if a pending request already exists
        $existing = Database::search(
            "SELECT request_id FROM reschedule_requests
             WHERE session_id = $sessionId AND status = 'pending'
             LIMIT 1"
        );
        if ($existing && $existing->num_rows > 0) return false;

        Database::setUpConnection();
        $safeReason = Database::$connection->real_escape_string(trim($reason));

        Database::iud(
            "INSERT INTO reschedule_requests (session_id, user_id, counselor_id, reason)
             VALUES ($sessionId, $userId, $counselorId, '$safeReason')"
        );

        // Fetch counselor user info (for notification + email)
        $cuRs = Database::search(
            "SELECT u.user_id, u.email,
                    COALESCE(u.display_name, CONCAT(u.first_name,' ',u.last_name), u.username, 'Counselor') AS counselor_name
             FROM counselors c
             JOIN users u ON u.user_id = c.user_id
             WHERE c.counselor_id = $counselorId LIMIT 1"
        );
        if ($cuRs) {
            $cuRow = $cuRs->fetch_assoc();
            $counselorUserId = (int)($cuRow['user_id'] ?? 0);
            $counselorEmail  = (string)($cuRow['email'] ?? '');
            $counselorName   = (string)($cuRow['counselor_name'] ?? 'Counselor');

            if ($counselorUserId > 0) {
                $t = Database::$connection->real_escape_string('Reschedule Request');
                $m = Database::$connection->real_escape_string('A client has requested to reschedule their upcoming session.');
                $l = Database::$connection->real_escape_string('/counselor/sessions');
                Database::iud(
                    "INSERT INTO notifications (user_id, type, title, message, link)
                     VALUES ($counselorUserId, 'reschedule_request', '$t', '$m', '$l')"
                );
            }

            // Email counselor
            if ($counselorEmail !== '') {
                require_once ROOT . '/core/Mailer.php';
                $userRs = Database::search(
                    "SELECT COALESCE(display_name, CONCAT(first_name,' ',last_name), username, 'Client') AS client_name
                     FROM users WHERE user_id = $userId LIMIT 1"
                );
                $clientName = 'A client';
                if ($userRs && ($uRow = $userRs->fetch_assoc())) {
                    $clientName = $uRow['client_name'];
                }
                $reasonHtml = $safeReason !== ''
                    ? "<p style='margin:8px 0;'><strong>Reason:</strong> " . htmlspecialchars(trim($reason)) . "</p>"
                    : '';
                $html = "
                    <div style='font-family:Montserrat,sans-serif;max-width:520px;margin:auto;padding:32px;'>
                        <h2 style='color:#2c3e50;margin-bottom:8px;'>Reschedule Request</h2>
                        <p style='color:#555;'>Hi " . htmlspecialchars($counselorName) . ", a client has requested to reschedule their upcoming session.</p>
                        <div style='background:#f9f9f9;border-radius:8px;padding:20px;margin:20px 0;'>
                            <p style='margin:8px 0;'><strong>Client:</strong> " . htmlspecialchars($clientName) . "</p>
                            $reasonHtml
                        </div>
                        <a href='/counselor/sessions' style='display:inline-block;padding:12px 28px;background:#4CAF50;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;'>
                            Review Request
                        </a>
                        <p style='color:#999;font-size:0.85rem;margin-top:24px;'>Log in to approve or decline the request.</p>
                    </div>";
                Mailer::send($counselorEmail, 'NewPath  Reschedule Request from Client', $html, $counselorName);
            }
        }

        return true;
    }

    // ------------------------------------------------------------------
    // Submit review + rating for a completed session
    // ------------------------------------------------------------------

    /**
     * Submit a 1-5 star rating and optional review text for a completed session.
     * Idempotent: returns false (without overwriting) if a rating already exists.
     * Also recalculates the counselor's aggregate rating.
     */
    public static function submitReview(int $userId, int $sessionId, int $rating, string $reviewText): bool
    {
        if ($sessionId <= 0 || $rating < 1 || $rating > 5) return false;

        $rs = Database::search(
            "SELECT session_id, counselor_id, rating FROM sessions
             WHERE session_id = $sessionId
               AND user_id   = $userId
               AND (status = 'completed' OR (status IN ('scheduled','confirmed') AND session_datetime < NOW()))
             LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;

        $session = $rs->fetch_assoc();
        if ($session['rating'] !== null) return false; // already reviewed

        Database::setUpConnection();
        $safeReview = Database::$connection->real_escape_string(trim($reviewText));
        $reviewSql  = $safeReview !== '' ? "'$safeReview'" : 'NULL';

        Database::iud(
            "UPDATE sessions
             SET rating     = $rating,
                 review     = $reviewSql,
                 updated_at = NOW()
             WHERE session_id = $sessionId
               AND user_id   = $userId
               AND rating    IS NULL"
        );

        // Recalculate counselor's aggregate rating
        $counselorId = (int)$session['counselor_id'];
        Database::iud(
            "UPDATE counselors c
             SET c.total_reviews = (
                     SELECT COUNT(*) FROM sessions
                     WHERE counselor_id = $counselorId
                       AND rating IS NOT NULL AND status = 'completed'
                 ),
                 c.rating = (
                     SELECT AVG(rating) FROM sessions
                     WHERE counselor_id = $counselorId
                       AND rating IS NOT NULL AND status = 'completed'
                 )
             WHERE c.counselor_id = $counselorId"
        );

        return true;
    }

    // ------------------------------------------------------------------
    // No-show dispute (user-initiated)
    // ------------------------------------------------------------------

    /**
     * Report that a counselor did not show up for a completed session.
     * Idempotent: only one dispute per user+session allowed.
     */
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
            "SELECT u.user_id,
                    COALESCE(u.display_name, CONCAT(u.first_name,' ',u.last_name), u.username, 'Client') AS client_name
             FROM sessions s
             JOIN counselors c ON c.counselor_id = s.counselor_id
             JOIN users u ON u.user_id = c.user_id
             LEFT JOIN users client ON client.user_id = s.user_id
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

    private static function formatSessionTypeShort(string $sessionType): string
    {
        return match ($sessionType) {
            'video' => 'Video',
            'audio' => 'Audio',
            'chat' => 'Chat',
            'in_person' => 'In Person',
            default => 'Session',
        };
    }
}

