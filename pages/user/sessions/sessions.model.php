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

    public static function getFollowupSessions(int $userId): array
    {
        if (!defined('FOLLOWUP_WINDOW_DAYS')) {
            define('FOLLOWUP_WINDOW_DAYS', 7);
        }

        $rs = Database::search(
            "SELECT s.session_id, s.session_datetime, s.updated_at,
                    COALESCE(cu.display_name, cu.username, 'Counselor') AS counselor_name,
                    cu.profile_picture AS counselor_avatar,
                    COUNT(sm.message_id) AS msg_count
             FROM sessions s
             JOIN counselors c ON c.counselor_id = s.counselor_id
             JOIN users cu ON cu.user_id = c.user_id
             LEFT JOIN session_messages sm ON sm.session_id = s.session_id
             WHERE s.user_id = $userId AND s.status = 'completed'
             GROUP BY s.session_id
             ORDER BY s.session_datetime DESC
             LIMIT 20"
        );

        $items = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $completedTs = !empty($row['updated_at']) ? strtotime($row['updated_at']) : strtotime($row['session_datetime']);
            $daysLeft    = max(0, (int) ceil(($completedTs + FOLLOWUP_WINDOW_DAYS * 86400 - time()) / 86400));
            $items[] = [
                'sessionId'       => (int) $row['session_id'],
                'counselorName'   => $row['counselor_name'],
                'counselorAvatar' => $row['counselor_avatar'] ?: '/assets/img/avatar.png',
                'sessionDate'     => date('M j, Y', strtotime($row['session_datetime'])),
                'msgCount'        => (int) $row['msg_count'],
                'daysLeft'        => $daysLeft,
                'isLocked'        => time() > $completedTs + FOLLOWUP_WINDOW_DAYS * 86400,
            ];
        }
        return $items;
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