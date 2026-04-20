<?php

class CounselorsModel
{
    /**
     * Fetch counselors based on search query and filters
     */
    public static function getCounselors($params = [])
    {
        $limit  = (int)($params['limit'] ?? 10);
        $offset = (int)($params['offset'] ?? 0);

        $whereSql = "WHERE u.role = 'counselor'";

        // Search by name
        if (!empty($params['q'])) {
            $q = addslashes($params['q']);
            $whereSql .= " AND u.display_name LIKE '%$q%'";
        }

        // Filter by specialty
        if (!empty($params['specialty'])) {
            $specialty = addslashes($params['specialty']);
            $whereSql .= " AND c.specialty LIKE '%$specialty%'";
        }

        // Filter by min experience
        if (!empty($params['minExperience'])) {
            $minExp = (int) $params['minExperience'];
            $whereSql .= " AND c.experience_years >= $minExp";
        }

        // Filter by max price
        if (!empty($params['maxPrice'])) {
            $maxPrice = (float) $params['maxPrice'];
            $whereSql .= " AND c.consultation_fee <= $maxPrice";
        }

        $sql = "
            SELECT
                c.counselor_id,
                u.display_name AS name,
                u.profile_picture,
                c.specialty AS specialty_short,
                c.experience_years,
                c.consultation_fee,
                (SELECT ROUND(AVG(s.rating), 1) FROM sessions s WHERE s.counselor_id = c.counselor_id AND s.rating IS NOT NULL AND s.status = 'completed') AS rating_value
            FROM counselors c
            INNER JOIN users u ON c.user_id = u.user_id
            $whereSql
            ORDER BY c.counselor_id DESC
            LIMIT $limit OFFSET $offset
        ";

        $rs = Database::search($sql);
        $counselors = [];

        while ($row = $rs->fetch_assoc()) {
            $counselors[] = $row;
        }

        // Count total for pagination
        $countSql = "SELECT COUNT(*) as total FROM counselors c INNER JOIN users u ON c.user_id = u.user_id $whereSql";
        $countRs = Database::search($countSql);
        $total = $countRs->fetch_assoc()['total'] ?? 0;

        return [
            'data'  => $counselors,
            'total' => $total
        ];
    }

    /**
     * Fetch one counselor for single counselor view.
     */
    public static function getCounselorById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $counselorId = (int)$id;
        $sql = "
            SELECT
                c.counselor_id,
                u.display_name AS name,
                u.profile_picture,
                c.title,
                c.specialty,
                c.bio,
                c.experience_years,
                c.consultation_fee,
                c.availability_schedule,
                (SELECT ROUND(AVG(s.rating), 1) FROM sessions s WHERE s.counselor_id = c.counselor_id AND s.rating IS NOT NULL AND s.status = 'completed') AS rating_value,
                (SELECT COUNT(*) FROM sessions s2 WHERE s2.counselor_id = c.counselor_id AND s2.rating IS NOT NULL AND s2.status = 'completed') AS total_reviews
            FROM counselors c
            INNER JOIN users u ON c.user_id = u.user_id
            WHERE c.counselor_id = $counselorId AND u.role = 'counselor'
            LIMIT 1
        ";

        $rs = Database::search($sql);
        if (!$rs || $rs->num_rows === 0) {
            return null;
        }

        $row = $rs->fetch_assoc();

        $rating = $row['rating_value'] !== null ? (float)$row['rating_value'] : null;
        if ($rating !== null && $rating <= 0) {
            $rating = null;
        }

        $totalReviews = isset($row['total_reviews']) ? (int)$row['total_reviews'] : 0;
        if ($totalReviews < 0) {
            $totalReviews = 0;
        }

        $price = isset($row['consultation_fee']) ? (float)$row['consultation_fee'] : 0.0;

        return [
            'counselor_id' => (int)$row['counselor_id'],
            'name' => $row['name'] ?? 'Counselor',
            'profile_picture' => !empty($row['profile_picture']) ? $row['profile_picture'] : '/assets/img/avatar.png',
            'title' => !empty($row['title']) ? $row['title'] : 'Counselor',
            'specialty' => !empty($row['specialty']) ? $row['specialty'] : 'Specialist',
            'bio' => !empty($row['bio']) ? $row['bio'] : 'No biography available.',
            'experience_years' => isset($row['experience_years']) ? (int)$row['experience_years'] : 0,
            'consultation_fee' => $price,
            'price_formatted' => 'Rs. ' . number_format($price, 2),
            'rating' => $rating !== null ? number_format($rating, 1, '.', '') : null,
            'total_reviews' => $totalReviews,
            'availability_schedule' => !empty($row['availability_schedule']) ? $row['availability_schedule'] : '{}'
        ];
    }

    public static function getUnavailableSlotsByCounselor(int $counselorId, string $startDate, string $endDate): array
    {
        if ($counselorId <= 0) {
            return [];
        }

        Database::setUpConnection();

        $safeCounselorId = max(0, $counselorId);
        $safeStartDate = Database::$connection->real_escape_string($startDate);
        $safeEndDate = Database::$connection->real_escape_string($endDate);

        Database::iud(
            "UPDATE booking_holds
             SET status = 'released'
             WHERE counselor_id = $safeCounselorId
               AND status = 'held'
               AND expires_at <= NOW()
               AND DATE(slot_datetime) BETWEEN '$safeStartDate' AND '$safeEndDate'"
        );

        $slotsByDate = [];

        $sessionRs = Database::search(
            "SELECT session_datetime AS slot_datetime
             FROM sessions
             WHERE counselor_id = $safeCounselorId
               AND DATE(session_datetime) BETWEEN '$safeStartDate' AND '$safeEndDate'
               AND status IN ('scheduled', 'confirmed', 'in_progress')"
        );

        while ($sessionRs && ($row = $sessionRs->fetch_assoc())) {
            $timestamp = strtotime((string) ($row['slot_datetime'] ?? ''));
            if (!$timestamp) {
                continue;
            }

            $dateKey = date('Y-m-d', $timestamp);
            $timeKey = date('H:i', $timestamp);
            $slotsByDate[$dateKey][$timeKey] = true;
        }

        $holdRs = Database::search(
            "SELECT slot_datetime
             FROM booking_holds
             WHERE counselor_id = $safeCounselorId
               AND DATE(slot_datetime) BETWEEN '$safeStartDate' AND '$safeEndDate'
               AND status = 'held'
               AND expires_at > NOW()"
        );

        while ($holdRs && ($row = $holdRs->fetch_assoc())) {
            $timestamp = strtotime((string) ($row['slot_datetime'] ?? ''));
            if (!$timestamp) {
                continue;
            }

            $dateKey = date('Y-m-d', $timestamp);
            $timeKey = date('H:i', $timestamp);
            $slotsByDate[$dateKey][$timeKey] = true;
        }

        $unavailableSlots = [];
        foreach ($slotsByDate as $dateKey => $times) {
            $slotTimes = array_keys($times);
            sort($slotTimes);
            $unavailableSlots[$dateKey] = $slotTimes;
        }

        ksort($unavailableSlots);

        return $unavailableSlots;
    }

    /**
     * Return counselor_ids for which the user has an unused approved
     * reschedule credit (approved request with no new non-cancelled session booked after it).
     */
    public static function getFreeCreditCounselorIds(int $userId): array
    {
        if ($userId <= 0) return [];
        $rs = Database::search(
            "SELECT DISTINCT rr.counselor_id
             FROM reschedule_requests rr
             WHERE rr.user_id = $userId
               AND rr.status  = 'approved'
               AND NOT EXISTS (
                   SELECT 1 FROM sessions s
                   WHERE s.user_id      = $userId
                     AND s.counselor_id = rr.counselor_id
                     AND s.created_at   > rr.reviewed_at
                     AND s.status NOT IN ('cancelled')
               )"
        );
        $ids = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $ids[] = (int)$row['counselor_id'];
        }
        return $ids;
    }

    /**
     * Counselors the user has had at least one completed session with,
     * ordered by most recent session.
     */
    public static function getMyCounselors(int $userId): array
    {
        $rs = Database::search("
            SELECT
                c.counselor_id,
                u.display_name AS name,
                u.profile_picture,
                c.specialty,
                c.title,
                COUNT(s.session_id) AS sessions_count,
                MAX(s.session_datetime) AS last_session_at,
                (SELECT s2.session_id
                 FROM sessions s2
                 WHERE s2.user_id = $userId
                   AND s2.counselor_id = c.counselor_id
                   AND s2.status = 'completed'
                   AND s2.session_datetime >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                 ORDER BY s2.session_datetime DESC LIMIT 1) AS open_session_id
            FROM sessions s
            JOIN counselors c ON c.counselor_id = s.counselor_id
            JOIN users u ON u.user_id = c.user_id
            WHERE s.user_id = $userId AND s.status = 'completed'
            GROUP BY c.counselor_id, u.display_name, u.profile_picture, c.specialty, c.title
            ORDER BY last_session_at DESC
        ");
        $list = [];
        if (!$rs) return $list;
        while ($row = $rs->fetch_assoc()) {
            $list[] = [
                'counselor_id'    => (int)$row['counselor_id'],
                'name'            => $row['name'] ?? 'Counselor',
                'profile_picture' => !empty($row['profile_picture']) ? $row['profile_picture'] : '/assets/img/avatar.png',
                'specialty'       => $row['specialty'] ?? '',
                'title'           => $row['title'] ?? '',
                'sessions_count'  => (int)$row['sessions_count'],
                'last_session_at' => $row['last_session_at'],
                'open_session_id' => isset($row['open_session_id']) ? (int)$row['open_session_id'] : null,
            ];
        }
        return $list;
    }

    /**
     * Fetch paginated reviews (completed sessions that have a rating) for a counselor.
     * Returns ['data' => [...], 'total' => int].
     */
    public static function getReviewsByCounselor(int $counselorId, int $page = 1, int $perPage = 5): array
    {
        if ($counselorId <= 0) {
            return ['data' => [], 'total' => 0];
        }

        $offset = ($page - 1) * $perPage;

        $rs = Database::search("
            SELECT
                s.session_id,
                s.rating,
                s.review,
                s.updated_at,
                s.session_datetime,
                COALESCE(u.display_name, u.username) AS reviewer_name,
                u.profile_picture AS reviewer_avatar
            FROM sessions s
            JOIN users u ON u.user_id = s.user_id
            WHERE s.counselor_id = $counselorId
              AND s.rating IS NOT NULL
              AND s.status = 'completed'
            ORDER BY s.updated_at DESC
            LIMIT $perPage OFFSET $offset
        ");

        $reviews = [];
        if ($rs) {
            while ($row = $rs->fetch_assoc()) {
                $reviews[] = [
                    'session_id'      => (int)$row['session_id'],
                    'rating'          => (int)$row['rating'],
                    'review'          => $row['review'],
                    'date'            => !empty($row['updated_at']) ? $row['updated_at'] : $row['session_datetime'],
                    'reviewer_name'   => $row['reviewer_name'] ?? 'Anonymous',
                    'reviewer_avatar' => !empty($row['reviewer_avatar']) ? $row['reviewer_avatar'] : '/assets/img/avatar.png',
                ];
            }
        }

        $countRs = Database::search("
            SELECT COUNT(*) AS total
            FROM sessions
            WHERE counselor_id = $counselorId
              AND rating IS NOT NULL
              AND status = 'completed'
        ");
        $total = $countRs ? (int)($countRs->fetch_assoc()['total'] ?? 0) : 0;

        return ['data' => $reviews, 'total' => $total];
    }

    /**
     * Returns the count of each star rating (1–5) for a counselor.
     * ['1' => N, '2' => N, '3' => N, '4' => N, '5' => N]
     */
    public static function getRatingBreakdown(int $counselorId): array
    {
        $breakdown = ['1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0];
        if ($counselorId <= 0) {
            return $breakdown;
        }

        $rs = Database::search("
            SELECT rating, COUNT(*) AS cnt
            FROM sessions
            WHERE counselor_id = $counselorId
              AND rating IS NOT NULL
              AND status = 'completed'
            GROUP BY rating
        ");

        if ($rs) {
            while ($row = $rs->fetch_assoc()) {
                $star = (string)(int)$row['rating'];
                if (isset($breakdown[$star])) {
                    $breakdown[$star] = (int)$row['cnt'];
                }
            }
        }

        return $breakdown;
    }

    /**
     * Get distinct specialties for the filter dropdown
     */
    public static function getSpecialties()
    {
        $rs = Database::search("SELECT DISTINCT specialty FROM counselors WHERE specialty IS NOT NULL AND specialty != ''");
        $specialties = [];
        while ($row = $rs->fetch_assoc()) {
            // Split if comma separated or just take the raw string
            $specs = explode(',', $row['specialty']);
            foreach ($specs as $s) {
                $s = trim($s);
                if (!empty($s) && !in_array($s, $specialties, true)) {
                    $specialties[] = $s;
                }
            }
        }
        sort($specialties);
        return $specialties;
    }
}
