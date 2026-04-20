<?php

require_once __DIR__ . '/../common/counselor.data.php';

class CounselorSessionsModel
{
    public static function getAll(int $counselorId): array
    {
        return CounselorData::getSessionsByCounselor($counselorId);
    }
    public static function createReport(array $input){
        $counselorUserId = $input['counselorUser_id'] ?? 0;
        $sessionId = $input['session_id'] ?? 0;
        $reason = $input['reason'] ?? 'other';
        $desc = $input['description'] ?? '';
        $rs=Database::iud("INSERT INTO session_disputes(session_id,reported_by,reason,description,created_at)
        VALUES ('$sessionId','$counselorUserId','$reason','$desc',NOW())");
        if($rs){
            return ['ok' => true];
        }else{
            return ['ok' => false];
        }

    }

    public static function getPendingRescheduleRequests(int $counselorId): array
    {
        $rs = Database::search(
            "SELECT rr.request_id, rr.session_id, rr.user_id, rr.reason, rr.requested_at,
                    s.session_datetime, s.session_type,
                    COALESCE(u.display_name, CONCAT(u.first_name,' ',u.last_name), u.username, 'Client') AS client_name,
                    u.profile_picture AS client_avatar
             FROM reschedule_requests rr
             JOIN sessions s ON s.session_id = rr.session_id
             JOIN users u ON u.user_id = rr.user_id
             WHERE rr.counselor_id = $counselorId AND rr.status = 'pending'
             ORDER BY rr.requested_at ASC"
        );

        $items = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $sessionTs = !empty($row['session_datetime']) ? strtotime($row['session_datetime']) : null;
            $items[] = [
                'requestId'   => (int)$row['request_id'],
                'sessionId'   => (int)$row['session_id'],
                'userId'      => (int)$row['user_id'],
                'clientName'  => $row['client_name'],
                'clientAvatar'=> $row['client_avatar'] ?: '/assets/img/avatar.png',
                'sessionDate' => $sessionTs ? date('D, M j \a\t g:i A', $sessionTs) : 'Unknown',
                'sessionType' => $row['session_type'] ?? 'video',
                'reason'      => $row['reason'] ?? '',
                'requestedAt' => date('M j, Y g:i A', strtotime($row['requested_at'])),
            ];
        }

        return $items;
    }

    /**
     * Approve a reschedule request.
     * - Marks the request as approved
     * - Cancels the session (status = 'cancelled', cancelled_by = counselor user_id)
     * - Notifies the user
     */
    public static function approveReschedule(int $counselorId, int $requestId, int $counselorUserId, string $note): bool
    {
        $rs = Database::search(
            "SELECT rr.request_id, rr.session_id, rr.user_id
             FROM reschedule_requests rr
             WHERE rr.request_id = $requestId AND rr.counselor_id = $counselorId AND rr.status = 'pending'
             LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;

        $row       = $rs->fetch_assoc();
        $sessionId = (int)$row['session_id'];
        $userId    = (int)$row['user_id'];

        Database::setUpConnection();
        $safeNote = Database::$connection->real_escape_string(trim($note));

        Database::iud(
            "UPDATE reschedule_requests
             SET status = 'approved', counselor_note = '$safeNote', reviewed_at = NOW()
             WHERE request_id = $requestId"
        );

        Database::iud(
            "UPDATE sessions
             SET status = 'cancelled', cancelled_by = $counselorUserId,
                 cancellation_reason = 'Reschedule approved by counselor', updated_at = NOW()
             WHERE session_id = $sessionId"
        );

        // Notify user
        $t = Database::$connection->real_escape_string('Reschedule Approved');
        $m = Database::$connection->real_escape_string('Your reschedule request was approved. Your session has been cancelled — please rebook at a new time.');
        $l = Database::$connection->real_escape_string('/user/counselors');
        Database::iud(
            "INSERT INTO notifications (user_id, type, title, message, link)
             VALUES ($userId, 'reschedule_approved', '$t', '$m', '$l')"
        );

        // Email user
        $userRs = Database::search(
            "SELECT email, COALESCE(display_name, CONCAT(first_name,' ',last_name), username, 'User') AS uname
             FROM users WHERE user_id = $userId LIMIT 1"
        );
        if ($userRs && ($uRow = $userRs->fetch_assoc()) && !empty($uRow['email'])) {
            require_once dirname(__DIR__, 3) . '/core/Mailer.php';
            $uName  = htmlspecialchars($uRow['uname']);
            $noteHtml = $note !== ''
                ? "<p style='margin:8px 0;'><strong>Counselor note:</strong> " . htmlspecialchars($note) . "</p>"
                : '';
            $html = "
                <div style='font-family:Montserrat,sans-serif;max-width:520px;margin:auto;padding:32px;'>
                    <h2 style='color:#2c3e50;margin-bottom:8px;'>Reschedule Approved</h2>
                    <p style='color:#555;'>Hi $uName, your reschedule request has been approved.</p>
                    <div style='background:#f9f9f9;border-radius:8px;padding:20px;margin:20px 0;'>
                        <p style='margin:8px 0;'>Your original session has been cancelled. You can now rebook a new slot with your counselor — free of charge.</p>
                        $noteHtml
                    </div>
                    <a href='/user/counselors' style='display:inline-block;padding:12px 28px;background:#4CAF50;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;'>
                        Rebook Now
                    </a>
                </div>";
            Mailer::send($uRow['email'], 'NewPath  Your Reschedule Request was Approved', $html, $uRow['uname']);
        }

        return true;
    }

    // ------------------------------------------------------------------
    // No-show disputes (counselor view — read-only)
    // ------------------------------------------------------------------

    /**
     * Returns all no-show disputes for sessions belonging to this counselor.
     */
    public static function getNoShowDisputes(int $counselorId): array
    {
        $rs = Database::search(
            "SELECT
                sd.dispute_id, sd.status AS dispute_status, sd.description,
                sd.admin_note, sd.created_at AS reported_at,
                s.session_id, s.session_datetime, s.status AS session_status,
                COALESCE(u.display_name, CONCAT(u.first_name,' ',u.last_name), u.username, 'Client') AS client_name,
                u.profile_picture AS client_avatar,
                t.amount, t.currency, t.status AS txn_status
             FROM session_disputes sd
             JOIN sessions s ON s.session_id = sd.session_id
             JOIN users u ON u.user_id = sd.reported_by
             LEFT JOIN transactions t ON t.session_id = s.session_id AND t.user_id = sd.reported_by
             WHERE s.counselor_id = $counselorId AND sd.reason = 'no_show'
             ORDER BY sd.created_at DESC"
        );

        $items = [];
        if ($rs) {
            while ($row = $rs->fetch_assoc()) {
                $sessionTs = !empty($row['session_datetime']) ? strtotime($row['session_datetime']) : null;
                $items[] = [
                    'disputeId'     => (int)$row['dispute_id'],
                    'sessionId'     => (int)$row['session_id'],
                    'disputeStatus' => $row['dispute_status'] ?? 'pending',
                    'description'   => $row['description'] ?? '',
                    'adminNote'     => $row['admin_note'] ?? '',
                    'reportedAt'    => !empty($row['reported_at']) ? date('M j, Y g:i A', strtotime($row['reported_at'])) : '',
                    'sessionDate'   => $sessionTs ? date('D, M j \a\t g:i A', $sessionTs) : 'Unknown',
                    'sessionStatus' => $row['session_status'] ?? '',
                    'clientName'    => $row['client_name'] ?? 'Client',
                    'clientAvatar'  => $row['client_avatar'] ?: '/assets/img/avatar.png',
                    'amount'        => $row['amount'] !== null ? number_format((float)$row['amount'], 2) . ' ' . ($row['currency'] ?: 'LKR') : null,
                    'txnStatus'     => $row['txn_status'] ?? '',
                ];
            }
        }

        return $items;
    }

    /**
     * Reject a reschedule request.
     * - Marks the request as rejected
     * - Original session remains active
     * - Notifies the user
     */
    public static function rejectReschedule(int $counselorId, int $requestId, string $note): bool
    {
        $rs = Database::search(
            "SELECT rr.request_id, rr.user_id
             FROM reschedule_requests rr
             WHERE rr.request_id = $requestId AND rr.counselor_id = $counselorId AND rr.status = 'pending'
             LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;

        $row    = $rs->fetch_assoc();
        $userId = (int)$row['user_id'];

        Database::setUpConnection();
        $safeNote = Database::$connection->real_escape_string(trim($note));

        Database::iud(
            "UPDATE reschedule_requests
             SET status = 'rejected', counselor_note = '$safeNote', reviewed_at = NOW()
             WHERE request_id = $requestId"
        );

        // Notify user: session stays, request was declined
        $t = Database::$connection->real_escape_string('Reschedule Declined');
        $m = $safeNote !== ''
            ? Database::$connection->real_escape_string("Your reschedule request was declined: $note Your original session remains scheduled.")
            : Database::$connection->real_escape_string('Your reschedule request was declined. Your original session remains scheduled.');
        $l = Database::$connection->real_escape_string('/user/sessions');
        Database::iud(
            "INSERT INTO notifications (user_id, type, title, message, link)
             VALUES ($userId, 'reschedule_rejected', '$t', '$m', '$l')"
        );

        // Email user
        $userRs = Database::search(
            "SELECT email, COALESCE(display_name, CONCAT(first_name,' ',last_name), username, 'User') AS uname
             FROM users WHERE user_id = $userId LIMIT 1"
        );
        if ($userRs && ($uRow = $userRs->fetch_assoc()) && !empty($uRow['email'])) {
            require_once dirname(__DIR__, 3) . '/core/Mailer.php';
            $uName    = htmlspecialchars($uRow['uname']);
            $noteHtml = $note !== ''
                ? "<p style='margin:8px 0;'><strong>Reason:</strong> " . htmlspecialchars($note) . "</p>"
                : '';
            $html = "
                <div style='font-family:Montserrat,sans-serif;max-width:520px;margin:auto;padding:32px;'>
                    <h2 style='color:#2c3e50;margin-bottom:8px;'>Reschedule Request Declined</h2>
                    <p style='color:#555;'>Hi $uName, unfortunately your reschedule request has been declined.</p>
                    <div style='background:#f9f9f9;border-radius:8px;padding:20px;margin:20px 0;'>
                        <p style='margin:8px 0;'>Your original session remains scheduled. Please check your session details.</p>
                        $noteHtml
                    </div>
                    <a href='/user/sessions' style='display:inline-block;padding:12px 28px;background:#4CAF50;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;'>
                        View My Sessions
                    </a>
                </div>";
            Mailer::send($uRow['email'], 'NewPath  Your Reschedule Request was Declined', $html, $uRow['uname']);
        }

        return true;
    }
}
