<?php
require_once __DIR__ . '/../common/counselor.data.php';
class CounselorDashboardModel
{
    public static function getCounselorById(int $counselorId): ?array
    {
        $safeCounselorId = max(0, $counselorId);
        $rs = Database::search(
            "SELECT c.counselor_id, c.user_id, c.title, c.specialty, c.specialty_short, c.bio,
                    c.experience_years, c.education, c.certifications, c.languages_spoken,
                    c.consultation_fee, c.availability_schedule, c.is_verified, c.rating,
                    c.total_reviews, c.total_clients, c.total_sessions,
                    u.email, u.username, u.display_name, u.first_name, u.last_name,
                    u.profile_picture, u.phone_number
             FROM counselors c
             JOIN users u ON u.user_id = c.user_id
             WHERE c.counselor_id = $safeCounselorId
             LIMIT 1"
        );

        $row = $rs ? $rs->fetch_assoc() : null;
        if (!$row) {
            return null;
        }

        $displayName = $row['display_name']
            ?: trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))
            ?: 'Counselor';

        return [
            'id' => (int) $row['user_id'],
            'counselorId' => (int) $row['counselor_id'],
            'displayName' => $displayName,
            'title' => $row['title'] ?? 'Counselor',
            'specialty' => $row['specialty'] ?? '',
            'bio' => $row['bio'] ?? '',
            'email' => $row['email'] ?? '',
            'phoneNumber' => $row['phone_number'] ?? '',
            'consultationFee' => $row['consultation_fee'] ?? null,
            'profilePictureUrl' => $row['profile_picture'] ?: '/assets/img/avatar.png',
            'availabilitySchedule' => $row['availability_schedule'] ?? '{}',
            'totalClients' => (int) ($row['total_clients'] ?? 0),
            'totalSessions' => (int) ($row['total_sessions'] ?? 0),
        ];
    }

    public static function getUpcomingSessionsByCounselor(int $counselorId): array
    {
        $safeCounselorId = max(0, $counselorId);
        $rs = Database::search(
            "SELECT s.session_id, s.user_id, s.session_datetime, s.session_type, s.status, s.meeting_link,
                COALESCE(u.display_name, CONCAT(u.first_name, ' ', u.last_name), u.username, 'Client') AS user_name
            FROM sessions s
            JOIN users u ON u.user_id = s.user_id
            WHERE s.counselor_id = $safeCounselorId 
               AND s.session_datetime > NOW()
               AND s.status IN ('scheduled', 'confirmed')
            ORDER BY s.session_datetime ASC"
        );

        $sessions = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $sessions[] = [
                'sessionId' => (int) $row['session_id'],
                'userId' => (int) $row['user_id'],
                'userName' => $row['user_name'] ?? 'Client',
                'sessionDatetime' => $row['session_datetime'],
                'sessionType' => $row['session_type'] ?? 'video',
                'status' => $row['status'] ?? 'scheduled',
                'meetingLink' => $row['meeting_link'] ?? '',
            ];
        }

        return $sessions;
    }

    public static function getCalendarSessionsByCounselor(int $counselorId, string $startDate, string $endDate): array
    {
        Database::setUpConnection();
        $safeCounselorId = max(0, $counselorId);
        $safeStartDate = Database::$connection->real_escape_string($startDate);
        $safeEndDate = Database::$connection->real_escape_string($endDate);

        $rs = Database::search(
            "SELECT s.session_datetime, s.session_type,
                    COALESCE(u.display_name, CONCAT(u.first_name, ' ', u.last_name), u.username, 'Client') AS user_name
             FROM sessions s
             JOIN users u ON u.user_id = s.user_id
             WHERE s.counselor_id = $safeCounselorId
               AND DATE(s.session_datetime) BETWEEN '$safeStartDate' AND '$safeEndDate'
               AND s.status IN ('scheduled', 'confirmed', 'in_progress', 'completed')
             ORDER BY s.session_datetime ASC"
        );

        $sessions = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $time = strtotime((string) $row['session_datetime']);
            $sessions[] = [
                'date' => $time ? date('Y-m-d', $time) : '',
                'time' => $time ? date('g:i A', $time) : '',
                'clientName' => $row['user_name'] ?? 'Client',
                'type' => $row['session_type'] ?? 'video',
            ];
        }

        return $sessions;
    }

    /**
     * Sum of completed transaction amounts for this counselor (PRD §3.1 stats card).
     */
    public static function getTotalIncome(int $counselorId): float
    {
        $safeCounselorId = max(0, $counselorId);
        $rs = Database::search(
            "SELECT COALESCE(SUM(amount), 0) AS total
             FROM transactions
             WHERE counselor_id = $safeCounselorId
               AND status       = 'completed'"
        );
        $row = $rs ? $rs->fetch_assoc() : null;
        return (float)($row['total'] ?? 0);
    }
    public static function getActiveClients(int $counselorId){
        
        return CounselorData::getClientsByCounselor($counselorId);
    }

    public static function getActiveClientsCount(int $counselorId): int
    {
        $safeCounselorId = max(0, $counselorId);
        $rs = Database::search(
            "SELECT COUNT(DISTINCT s.user_id) AS total
             FROM sessions s
             JOIN users u ON u.user_id = s.user_id
             WHERE s.counselor_id = $safeCounselorId
               AND u.is_active = 1"
        );
        $row = $rs ? $rs->fetch_assoc() : null;
        return (int) ($row['total'] ?? 0);
    }

    public static function getClientActivities(int $counselorId, int $limit = 5): array
    {
        $safeCounselorId = max(0, $counselorId);
        $safeLimit = max(1, min(20, $limit));
        $rs = Database::search(
            "SELECT s.session_type, s.status, s.session_datetime, s.updated_at, s.created_at,
                    u.profile_picture,
                    COALESCE(u.display_name, CONCAT(u.first_name, ' ', u.last_name), u.username, 'Client') AS client_name
             FROM sessions s
             JOIN users u ON u.user_id = s.user_id
             WHERE s.counselor_id = $safeCounselorId
             ORDER BY COALESCE(s.updated_at, s.created_at) DESC
             LIMIT $safeLimit"
        );

        $activities = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $referenceTime = $row['updated_at'] ?: $row['created_at'] ?: $row['session_datetime'];
            $activities[] = [
                'clientName' => $row['client_name'] ?? 'Client',
                'description' => self::buildActivityDescription($row),
                'profilePicture' => $row['profile_picture'] ?: '/assets/img/avatar.png',
                'timeAgo' => self::formatTimeAgo($referenceTime),
            ];
        }

        return $activities;
    }

    private static function buildActivityDescription(array $row): string
    {
        $typeLabel = match ($row['session_type'] ?? 'video') {
            'in_person' => 'in-person',
            'audio' => 'audio',
            'chat' => 'chat',
            default => 'video',
        };

        return match ($row['status'] ?? '') {
            'completed' => "Completed a {$typeLabel} session",
            'cancelled' => "Cancelled a {$typeLabel} session",
            'in_progress' => "Started a {$typeLabel} session",
            default => "Booked a {$typeLabel} session",
        };
    }

    private static function formatTimeAgo(?string $timestamp): string
    {
        if (!$timestamp) {
            return 'Just now';
        }

        $time = strtotime($timestamp);
        if (!$time) {
            return 'Just now';
        }

        $diff = max(0, time() - $time);
        if ($diff < 60) {
            return 'Just now';
        }
        if ($diff < 3600) {
            return floor($diff / 60) . ' min ago';
        }
        if ($diff < 86400) {
            return floor($diff / 3600) . ' hr ago';
        }

        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    }
}
