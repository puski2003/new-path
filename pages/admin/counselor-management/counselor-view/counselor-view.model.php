<?php

class CounselorViewModel
{
    private static function esc(string $value): string
    {
        Database::setUpConnection();
        return Database::$connection->real_escape_string($value);
    }

    public static function getCounselor(int $counselorId): ?array
    {
        $safeId = max(0, $counselorId);
        $rs = Database::search(
            "SELECT c.*, u.email, u.username, u.first_name, u.last_name, u.display_name, 
                    u.phone_number, u.is_active, u.last_login, u.created_at AS user_created_at,
                    a.full_name AS admin_name
             FROM counselors c
             INNER JOIN users u ON u.user_id = c.user_id
             LEFT JOIN admin a ON a.user_id = c.user_id
             WHERE c.counselor_id = $safeId
             LIMIT 1"
        );

        if (!$rs || $rs->num_rows === 0) {
            return null;
        }

        $row = $rs->fetch_assoc();
        
        $name = $row['display_name']
            ?: trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))
            ?: ($row['email'] ?? 'Counselor');

        $availability = $row['availability_schedule'];
        if (is_string($availability)) {
            $decoded = json_decode($availability, true);
            $availability = json_encode($decoded, JSON_PRETTY_PRINT);
        } elseif ($availability !== null) {
            $availability = json_encode($availability, JSON_PRETTY_PRINT);
        }

        return [
            'counselorId' => (int) $row['counselor_id'],
            'userId' => (int) $row['user_id'],
            'email' => $row['email'] ?? '',
            'username' => $row['username'] ?? '',
            'fullName' => $name,
            'firstName' => $row['first_name'] ?? '',
            'lastName' => $row['last_name'] ?? '',
            'phoneNumber' => $row['phone_number'] ?? '',
            'title' => $row['title'] ?? '',
            'specialty' => $row['specialty'] ?? '',
            'specialtyShort' => $row['specialty_short'] ?? '',
            'bio' => $row['bio'] ?? '',
            'experienceYears' => (int) ($row['experience_years'] ?? 0),
            'education' => $row['education'] ?? '',
            'certifications' => $row['certifications'] ?? '',
            'languagesSpoken' => $row['languages_spoken'] ?? '',
            'consultationFee' => $row['consultation_fee'] !== null ? (float) $row['consultation_fee'] : null,
            'availabilitySchedule' => $availability ?? '',
            'isVerified' => !empty($row['is_verified']),
            'rating' => (float) ($row['rating'] ?? 0),
            'totalReviews' => (int) ($row['total_reviews'] ?? 0),
            'totalClients' => (int) ($row['total_clients'] ?? 0),
            'totalSessions' => (int) ($row['total_sessions'] ?? 0),
            'isActive' => !empty($row['is_active']),
            'lastLogin' => $row['last_login'] ?? '',
            'userCreatedAt' => $row['user_created_at'] ?? '',
            'counselorCreatedAt' => $row['created_at'] ?? '',
            'updatedAt' => $row['updated_at'] ?? '',
        ];
    }

    public static function getCounselorSessions(int $counselorId): array
    {
        $safeId = max(0, $counselorId);
        $rs = Database::search(
            "SELECT s.session_id, s.session_datetime, s.status, s.duration_minutes,
                    u.display_name, u.email AS user_email
             FROM sessions s
             INNER JOIN users u ON u.user_id = s.user_id
             WHERE s.counselor_id = $safeId
             ORDER BY s.session_datetime DESC
             LIMIT 10"
        );

        $sessions = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $sessions[] = [
                'sessionId' => (int) $row['session_id'],
                'scheduledAt' => $row['session_datetime'] ?? '',
                'status' => $row['status'] ?? '',
                'duration' => (int) ($row['duration_minutes'] ?? 0),
                'clientName' => $row['display_name'] ?? 'Unknown',
                'clientEmail' => $row['user_email'] ?? '',
            ];
        }

        return $sessions;
    }

    public static function getCounselorReviews(int $counselorId): array
    {
        $safeId = max(0, $counselorId);
        $rs = Database::search(
            "SELECT s.session_id, s.rating, s.review, s.updated_at,
                    u.display_name, u.profile_picture
             FROM sessions s
             INNER JOIN users u ON u.user_id = s.user_id
             WHERE s.counselor_id = $safeId
               AND (s.rating IS NOT NULL OR s.review IS NOT NULL)
             ORDER BY s.updated_at DESC
             LIMIT 10"
        );

        $reviews = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $reviews[] = [
                'reviewId' => (int) $row['session_id'],
                'rating' => (float) ($row['rating'] ?? 0),
                'review' => $row['review'] ?? '',
                'createdAt' => $row['updated_at'] ?? '',
                'clientName' => $row['display_name'] ?? 'Anonymous',
                'clientProfilePicture' => $row['profile_picture'] ?? '',
            ];
        }

        return $reviews;
    }
}
