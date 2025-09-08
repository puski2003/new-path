<?php

class CounselorManagementModel
{
    private static function esc(string $value): string
    {
        Database::setUpConnection();
        return Database::$connection->real_escape_string($value);
    }

    private static function getAdminIdByUserId(int $userId): int
    {
        $safeUserId = max(0, $userId);
        $rs = Database::search("SELECT admin_id FROM admin WHERE user_id = $safeUserId LIMIT 1");
        return (int) ($rs && $row = $rs->fetch_assoc() ? ($row['admin_id'] ?? 0) : 0);
    }

    public static function getPageData(array $filters, int $page = 1, int $perPage = 15): array
    {
        return [
            'stats' => self::getCounselorStats(),
            'applications' => self::getCounselorApplicationsPaginated($filters['appStatus'], $page, $perPage),
            'counselors' => self::getCounselorsPaginated($filters, $page, $perPage),
        ];
    }

    public static function getCounselorStats(): array
    {
        $activeCounselors = Database::search("SELECT COUNT(*) AS total FROM users WHERE role = 'counselor' AND is_active = 1");
        $pendingApplications = Database::search("SELECT COUNT(*) AS total FROM counselor_applications WHERE status = 'pending'");
        $rating = Database::search("SELECT COALESCE(AVG(rating), 0) AS avg_rating FROM counselors");
        $sessions = Database::search("SELECT COUNT(*) AS total FROM sessions");
        $avgSessions = Database::search("
            SELECT COALESCE(
                (SELECT COUNT(*) FROM sessions WHERE status = 'completed') / 
                NULLIF((SELECT COUNT(*) FROM users WHERE role = 'counselor' AND is_active = 1), 0),
                0
            ) AS avg_sessions
        ");

        return [
            'activeCounselorsCount' => (int) (($activeCounselors ? $activeCounselors->fetch_assoc()['total'] : 0) ?? 0),
            'pendingApplicationsCount' => (int) (($pendingApplications ? $pendingApplications->fetch_assoc()['total'] : 0) ?? 0),
            'averageRating' => round((float) (($rating ? $rating->fetch_assoc()['avg_rating'] : 0) ?? 0), 1),
            'totalSessions' => (int) (($sessions ? $sessions->fetch_assoc()['total'] : 0) ?? 0),
            'avgSessionsPerCounselor' => round((float) (($avgSessions ? $avgSessions->fetch_assoc()['avg_sessions'] : 0) ?? 0), 1),
        ];
    }

    public static function getCounselorApplicationsPaginated(string $status = 'pending', int $page = 1, int $perPage = 15): array
    {
        $safePage = Pagination::sanitizePage($page);
        $safePerPage = Pagination::sanitizePerPage($perPage, 15, 100);

        $allApplications = self::getCounselorApplications($status);
        $totalRows = count($allApplications);
        $meta = Pagination::meta($totalRows, $safePage, $safePerPage);

        $items = array_slice($allApplications, $meta['offset'], $meta['perPage']);

        return [
            'items' => $items,
            'pagination' => $meta,
        ];
    }

    public static function getCounselorApplications(string $status = 'pending'): array
    {
        $where = '';
        if ($status !== 'all') {
            $where = "WHERE ca.status = '" . self::esc($status) . "'";
        }

        $rs = Database::search(
            "SELECT ca.*, DATE(ca.created_at) AS application_date, a.full_name AS reviewer_name
             FROM counselor_applications ca
             LEFT JOIN admin a ON a.admin_id = ca.reviewed_by
             $where
             ORDER BY ca.created_at DESC"
        );

        $applications = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $applications[] = [
                'applicationId' => (int) $row['application_id'],
                'fullName' => $row['full_name'] ?? '',
                'email' => $row['email'] ?? '',
                'phoneNumber' => $row['phone_number'] ?? '',
                'title' => $row['title'] ?? '',
                'specialty' => $row['specialty'] ?? '',
                'bio' => $row['bio'] ?? '',
                'experienceYears' => $row['experience_years'] !== null ? (int) $row['experience_years'] : null,
                'education' => $row['education'] ?? '',
                'certifications' => $row['certifications'] ?? '',
                'languagesSpoken' => $row['languages_spoken'] ?? '',
                'consultationFee' => $row['consultation_fee'] !== null ? (float) $row['consultation_fee'] : null,
                'availabilitySchedule' => $row['availability_schedule'] ?? '',
                'documentsUrl' => $row['documents_url'] ?? '',
                'status' => $row['status'] ?? 'pending',
                'adminNotes' => $row['admin_notes'] ?? '',
                'reviewerName' => $row['reviewer_name'] ?? '',
                'applicationDate' => $row['application_date'] ?? '',
                'reviewDate' => $row['review_date'] ?? '',
            ];
        }

        return $applications;
    }

    public static function getCounselorApplicationById(int $applicationId): ?array
    {
        foreach (self::getCounselorApplications('all') as $application) {
            if ($application['applicationId'] === $applicationId) {
                return $application;
            }
        }
        return null;
    }

    public static function approveCounselorApplication(int $applicationId, int $adminUserId): array
    {
        $application = self::getCounselorApplicationById($applicationId);
        if (!$application) {
            return ['ok' => false, 'message' => 'Application not found.'];
        }

        $safeEmail = self::esc($application['email']);
        $existingUser = Database::search("SELECT user_id FROM users WHERE email = '$safeEmail' LIMIT 1");
        if ($existingUser && $existingUser->num_rows > 0) {
            $row = $existingUser->fetch_assoc();
            $userId = (int) $row['user_id'];
        } else {
            $usernameBase = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $application['fullName'])) ?: 'counselor';
            $username = trim($usernameBase, '_');
            if ($username === '') {
                $username = 'counselor';
            }
            $safeUsername = self::esc($username);
            $dup = Database::search("SELECT COUNT(*) AS total FROM users WHERE username LIKE '$safeUsername%'");
            $dupCount = (int) (($dup ? $dup->fetch_assoc()['total'] : 0) ?? 0);
            if ($dupCount > 0) {
                $safeUsername .= '_' . ($dupCount + 1);
            }

            $passwordHash = self::esc(password_hash(bin2hex(random_bytes(6)), PASSWORD_BCRYPT));
            $displayName = self::esc($application['fullName']);
            Database::iud(
                "INSERT INTO users (email, username, password_hash, salt, role, display_name, phone_number, is_active, created_at, updated_at)
                 VALUES ('$safeEmail', '$safeUsername', '$passwordHash', '', 'counselor', '$displayName', '" . self::esc($application['phoneNumber']) . "', 1, NOW(), NOW())"
            );
            $userId = (int) Database::$connection->insert_id;
        }

        $checkCounselor = Database::search("SELECT counselor_id FROM counselors WHERE user_id = $userId LIMIT 1");
        if (!($checkCounselor && $checkCounselor->num_rows > 0)) {
            $availability = self::esc($application['availabilitySchedule']);
            $consultationFee = $application['consultationFee'] !== null ? (float) $application['consultationFee'] : 'NULL';
            Database::iud(
                "INSERT INTO counselors
                    (user_id, title, specialty, specialty_short, bio, experience_years, education, certifications, languages_spoken, consultation_fee, availability_schedule, is_verified, created_at, updated_at)
                 VALUES
                    ($userId, '" . self::esc($application['title']) . "', '" . self::esc($application['specialty']) . "', '', '" . self::esc($application['bio']) . "', " . (int) ($application['experienceYears'] ?? 0) . ", '" . self::esc($application['education']) . "', '" . self::esc($application['certifications']) . "', '" . self::esc($application['languagesSpoken']) . "', $consultationFee, '$availability', 1, NOW(), NOW())"
            );
        }

        $adminId = self::getAdminIdByUserId($adminUserId);
        $notes = self::esc('Application approved and counselor account created');
        Database::iud(
            "UPDATE counselor_applications
             SET status = 'approved',
                 admin_notes = '$notes',
                 reviewed_by = " . max(0, $adminId) . ",
                 review_date = NOW(),
                 updated_at = NOW()
             WHERE application_id = $applicationId"
        );

        return ['ok' => true, 'message' => 'Application approved and counselor account created successfully.'];
    }

    public static function rejectCounselorApplication(int $applicationId, int $adminUserId, string $notes): array
    {
        $adminId = self::getAdminIdByUserId($adminUserId);
        $safeNotes = self::esc($notes);
        Database::iud(
            "UPDATE counselor_applications
             SET status = 'rejected',
                 admin_notes = '$safeNotes',
                 reviewed_by = " . max(0, $adminId) . ",
                 review_date = NOW(),
                 updated_at = NOW()
             WHERE application_id = $applicationId"
        );

        return ['ok' => true, 'message' => 'Application rejected successfully.'];
    }

    public static function getCounselors(array $filters = []): array
    {
        $where = ["u.role = 'counselor'"];

        $specialization = trim((string) ($filters['specialization'] ?? 'all'));
        if ($specialization !== '' && $specialization !== 'all') {
            $where[] = "c.specialty = '" . self::esc($specialization) . "'";
        }

        $status = trim((string) ($filters['counselorStatus'] ?? 'all'));
        if ($status === 'active') {
            $where[] = "u.is_active = 1";
        } elseif ($status === 'inactive') {
            $where[] = "u.is_active = 0";
        }

        $rs = Database::search(
            "SELECT c.counselor_id, c.title, c.specialty, c.languages_spoken, c.rating, c.total_reviews,
                    u.email, u.display_name, u.first_name, u.last_name, u.is_active
             FROM counselors c
             INNER JOIN users u ON u.user_id = c.user_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY c.created_at DESC, c.counselor_id DESC"
        );

        $counselors = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $name = $row['display_name']
                ?: trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))
                ?: ($row['email'] ?? 'Counselor');

            $counselors[] = [
                'counselorId' => (int) $row['counselor_id'],
                'fullName' => $name,
                'email' => $row['email'] ?? '',
                'title' => $row['title'] ?? '',
                'specialty' => $row['specialty'] ?? '',
                'languagesSpoken' => $row['languages_spoken'] ?? '',
                'rating' => (float) ($row['rating'] ?? 0),
                'totalReviews' => (int) ($row['total_reviews'] ?? 0),
                'active' => !empty($row['is_active']),
            ];
        }

        return $counselors;
    }

    public static function getCounselorsPaginated(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $safePage = Pagination::sanitizePage($page);
        $safePerPage = Pagination::sanitizePerPage($perPage, 15, 100);

        $allCounselors = self::getCounselors($filters);
        $totalRows = count($allCounselors);
        $meta = Pagination::meta($totalRows, $safePage, $safePerPage);

        $items = array_slice($allCounselors, $meta['offset'], $meta['perPage']);

        return [
            'items' => $items,
            'pagination' => $meta,
        ];
    }
}
