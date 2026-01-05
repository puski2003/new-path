<?php
require_once __DIR__ . '/../../../core/GoogleMeetService.php';

class AdminSessionsModel
{
    /**
     * Paginated list of all sessions with user + counselor names.
     *
     * @param  string $search  Optional search term (user name, counselor name, status)
     * @param  int    $limit
     * @param  int    $offset
     * @return array{ rows: array, total: int }
     */
    public static function getSessions(string $search = '', int $limit = 20, int $offset = 0): array
    {
        $safeSearch = addslashes($search);
        $where = $search !== ''
            ? "WHERE (
                  COALESCE(u.display_name, CONCAT(u.first_name,' ',u.last_name), u.username) LIKE '%$safeSearch%'
               OR COALESCE(cu.display_name, CONCAT(cu.first_name,' ',cu.last_name), cu.username) LIKE '%$safeSearch%'
               OR s.status LIKE '%$safeSearch%'
               )"
            : '';

        $countRs = Database::search(
            "SELECT COUNT(*) AS total
             FROM sessions s
             JOIN users u  ON u.user_id  = s.user_id
             JOIN counselors c ON c.counselor_id = s.counselor_id
             JOIN users cu ON cu.user_id = c.user_id
             $where"
        );
        $total = (int)(($countRs ? $countRs->fetch_assoc() : [])['total'] ?? 0);

        $rs = Database::search(
            "SELECT
                s.session_id,
                s.session_datetime,
                s.duration_minutes,
                s.session_type,
                s.status,
                s.meeting_link,
                s.meet_space_name,
                s.created_at,
                COALESCE(u.display_name, CONCAT(u.first_name,' ',u.last_name), u.username, 'User') AS user_name,
                u.email AS user_email,
                COALESCE(cu.display_name, CONCAT(cu.first_name,' ',cu.last_name), cu.username, 'Counselor') AS counselor_name,
                cu.email AS counselor_email
             FROM sessions s
             JOIN users u  ON u.user_id  = s.user_id
             JOIN counselors c ON c.counselor_id = s.counselor_id
             JOIN users cu ON cu.user_id = c.user_id
             $where
             ORDER BY s.session_datetime DESC
             LIMIT $limit OFFSET $offset"
        );

        $rows = [];
        if ($rs) {
            while ($row = $rs->fetch_assoc()) {
                $rows[] = $row;
            }
        }

        return ['rows' => $rows, 'total' => $total];
    }

    /**
     * Fetch a single session row (for the meeting detail modal).
     */
    public static function getSession(int $sessionId): ?array
    {
        $rs = Database::search(
            "SELECT
                s.session_id, s.session_datetime, s.duration_minutes,
                s.status, s.meeting_link, s.meet_space_name,
                COALESCE(u.display_name, CONCAT(u.first_name,' ',u.last_name), u.username) AS user_name,
                COALESCE(cu.display_name, CONCAT(cu.first_name,' ',cu.last_name), cu.username) AS counselor_name
             FROM sessions s
             JOIN users u ON u.user_id = s.user_id
             JOIN counselors c ON c.counselor_id = s.counselor_id
             JOIN users cu ON cu.user_id = c.user_id
             WHERE s.session_id = $sessionId
             LIMIT 1"
        );
        $row = $rs ? $rs->fetch_assoc() : null;
        return $row ?: null;
    }

    // ------------------------------------------------------------------
    // No-show disputes
    // ------------------------------------------------------------------

    /**
     * Paginated list of session_disputes with reason=no_show.
     */
    public static function getNoShowDisputes(int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;

        $countRs = Database::search(
            "SELECT COUNT(*) AS total FROM session_disputes WHERE reason = 'no_show'"
        );
        $total = (int)(($countRs ? $countRs->fetch_assoc() : [])['total'] ?? 0);

        $rs = Database::search(
            "SELECT
                sd.dispute_id, sd.status AS dispute_status, sd.description,
                sd.admin_note, sd.created_at AS reported_at, sd.reviewed_at,
                s.session_id, s.session_datetime, s.duration_minutes,
                s.status AS session_status, s.meet_space_name,
                COALESCE(u.display_name, CONCAT(u.first_name,' ',u.last_name), u.username, 'User') AS user_name,
                u.email AS user_email,
                COALESCE(cu.display_name, CONCAT(cu.first_name,' ',cu.last_name), cu.username, 'Counselor') AS counselor_name,
                cu.email AS counselor_email,
                t.transaction_id, t.amount, t.currency, t.status AS txn_status
             FROM session_disputes sd
             JOIN sessions s ON s.session_id = sd.session_id
             JOIN users u ON u.user_id = sd.reported_by
             JOIN counselors c ON c.counselor_id = s.counselor_id
             JOIN users cu ON cu.user_id = c.user_id
             LEFT JOIN transactions t ON t.session_id = s.session_id AND t.user_id = sd.reported_by
             WHERE sd.reason = 'no_show'
             ORDER BY sd.created_at DESC
             LIMIT $limit OFFSET $offset"
        );

        $rows = [];
        if ($rs) {
            while ($row = $rs->fetch_assoc()) {
                $rows[] = $row;
            }
        }

        return [
            'rows'       => $rows,
            'total'      => $total,
            'totalPages' => (int)ceil(max(1, $total) / $limit),
        ];
    }

    /**
     * Admin approves no-show: marks session as no_show, refunds transaction,
     * resolves the dispute, notifies the user.
     */
    public static function markRefunded(int $disputeId, int $adminUserId, string $note): bool
    {
        $rs = Database::search(
            "SELECT sd.dispute_id, sd.session_id, sd.reported_by, sd.status,
                    s.counselor_id,
                    t.transaction_id, t.amount, t.currency
             FROM session_disputes sd
             JOIN sessions s ON s.session_id = sd.session_id
             LEFT JOIN transactions t ON t.session_id = sd.session_id AND t.user_id = sd.reported_by
             WHERE sd.dispute_id = $disputeId AND sd.reason = 'no_show'
             LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;

        $row           = $rs->fetch_assoc();
        if (!in_array($row['status'], ['pending', 'reviewed'], true)) return false;

        $sessionId     = (int)$row['session_id'];
        $userId        = (int)$row['reported_by'];
        $transactionId = $row['transaction_id'] !== null ? (int)$row['transaction_id'] : 0;
        $amount        = $row['amount'] ?? '0.00';

        Database::setUpConnection();
        $safeNote = Database::$connection->real_escape_string(trim($note));

        // 1. Resolve the dispute
        Database::iud(
            "UPDATE session_disputes
             SET status = 'resolved', reviewed_by = $adminUserId,
                 admin_note = '$safeNote', reviewed_at = NOW()
             WHERE dispute_id = $disputeId"
        );

        // 2. Mark session as no_show
        Database::iud(
            "UPDATE sessions SET status = 'no_show', updated_at = NOW()
             WHERE session_id = $sessionId"
        );

        // 3. Mark transaction as refunded
        if ($transactionId > 0) {
            Database::iud(
                "UPDATE transactions SET status = 'refunded', updated_at = NOW()
                 WHERE transaction_id = $transactionId"
            );

            // 4. Approve refund_disputes row if it exists; insert if not
            $rdRs = Database::search(
                "SELECT dispute_id FROM refund_disputes
                 WHERE transaction_id = $transactionId AND user_id = $userId AND issue_type = 'missed_session'
                 LIMIT 1"
            );
            if ($rdRs && $rdRs->num_rows > 0) {
                $rdRow = $rdRs->fetch_assoc();
                $rdId  = (int)$rdRow['dispute_id'];
                // Look up admin_id for resolved_by FK
                $adminRs = Database::search("SELECT admin_id FROM admin WHERE user_id = $adminUserId LIMIT 1");
                $adminId = $adminRs && ($ar = $adminRs->fetch_assoc()) ? (int)$ar['admin_id'] : null;
                $adminIdSql = $adminId ? $adminId : 'NULL';
                Database::iud(
                    "UPDATE refund_disputes
                     SET status = 'approved', refunded_amount = $amount,
                         resolved_by = $adminIdSql, resolved_at = NOW(),
                         admin_notes = '$safeNote'
                     WHERE dispute_id = $rdId"
                );
            }
        }

        // 5. Notify user
        $t = Database::$connection->real_escape_string('Refund Approved');
        $m = Database::$connection->real_escape_string('Your absence report has been reviewed. A refund has been approved for your session.');
        $l = Database::$connection->real_escape_string('/user/sessions?tab=reports');
        Database::iud(
            "INSERT INTO notifications (user_id, type, title, message, link)
             VALUES ($userId, 'refund_approved', '$t', '$m', '$l')"
        );

        return true;
    }

    /**
     * Admin rejects a no-show dispute: dismisses it and notifies the user.
     */
    public static function rejectDispute(int $disputeId, int $adminUserId, string $note): bool
    {
        $rs = Database::search(
            "SELECT dispute_id, reported_by, status
             FROM session_disputes
             WHERE dispute_id = $disputeId AND reason = 'no_show'
             LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;

        $row    = $rs->fetch_assoc();
        if (!in_array($row['status'], ['pending', 'reviewed'], true)) return false;
        $userId = (int)$row['reported_by'];

        Database::setUpConnection();
        $safeNote = Database::$connection->real_escape_string(trim($note));

        Database::iud(
            "UPDATE session_disputes
             SET status = 'dismissed', reviewed_by = $adminUserId,
                 admin_note = '$safeNote', reviewed_at = NOW()
             WHERE dispute_id = $disputeId"
        );

        // Notify user
        $t = Database::$connection->real_escape_string('Absence Report Rejected');
        $m = !empty(trim($note))
            ? Database::$connection->real_escape_string("Your absence report was reviewed and rejected: " . trim($note))
            : Database::$connection->real_escape_string('Your absence report was reviewed. We could not verify the counselor was absent.');
        $l = Database::$connection->real_escape_string('/user/sessions?tab=reports');
        Database::iud(
            "INSERT INTO notifications (user_id, type, title, message, link)
             VALUES ($userId, 'refund_rejected', '$t', '$m', '$l')"
        );

        return true;
    }

    /**
     * Fetch session details and, if a Meet space exists, live data from the Meet API.
     * Always returns session data; space/records are omitted when no space name is stored.
     */
    public static function getMeetingDetails(int $sessionId): ?array
    {
        $session = self::getSession($sessionId);
        if (!$session) {
            return null;
        }

        if (empty($session['meet_space_name'])) {
            return [
                'session'           => $session,
                'space'             => null,
                'conferenceRecords' => [],
            ];
        }

        $spaceName = $session['meet_space_name'];
        $space     = GoogleMeetService::getSpaceDetails($spaceName);
        $records   = GoogleMeetService::getConferenceRecords($spaceName);

        return [
            'session'           => $session,
            'space'             => $space,
            'conferenceRecords' => $records,
        ];
    }
}
