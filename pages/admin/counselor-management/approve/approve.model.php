<?php

require_once ROOT . '/config/password-pool.php';

class ApproveApplicationModel
{
    private static function esc(string $value): string
    {
        Database::setUpConnection();
        return Database::$connection->real_escape_string($value);
    }

    public static function getApplication(int $applicationId): ?array
    {
        $safeId = max(0, $applicationId);
        $rs = Database::search(
            "SELECT * FROM counselor_applications WHERE application_id = $safeId AND status = 'pending' LIMIT 1"
        );

        if (!$rs || $rs->num_rows === 0) {
            return null;
        }

        $row = $rs->fetch_assoc();
        return [
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
        ];
    }

    public static function generateUsername(string $fullName): string
    {
        $usernameBase = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $fullName)) ?: 'counselor';
        $username = trim($usernameBase, '_');
        if ($username === '') {
            $username = 'counselor';
        }
        $safeUsername = self::esc($username);
        $dup = Database::search("SELECT COUNT(*) AS total FROM users WHERE username LIKE '$safeUsername%'");
        $dupCount = (int) (($dup ? $dup->fetch_assoc()['total'] : 0) ?? 0);
        if ($dupCount > 0) {
            $username .= '_' . ($dupCount + 1);
        }
        return $username;
    }

    public static function approve(int $applicationId, int $adminUserId, string $username, string $password): array
    {
        $application = self::getApplication($applicationId);
        if (!$application) {
            return ['ok' => false, 'message' => 'Application not found or already processed.'];
        }

        $safeEmail = self::esc($application['email']);

        $existingUser = Database::search("SELECT user_id FROM users WHERE email = '$safeEmail' LIMIT 1");
        if ($existingUser && $existingUser->num_rows > 0) {
            $row = $existingUser->fetch_assoc();
            $userId = (int) $row['user_id'];
        } else {
            $safeUsername = self::esc($username);
            $passwordHash = self::esc(password_hash($password, PASSWORD_BCRYPT));
            $displayName = self::esc($application['fullName']);

            Database::iud(
                "INSERT INTO users (email, username, password_hash, salt, role, display_name, phone_number, is_active, must_change_password, created_at, updated_at)
                 VALUES ('$safeEmail', '$safeUsername', '$passwordHash', '', 'counselor', '$displayName', '" . self::esc($application['phoneNumber']) . "', 1, 1, NOW(), NOW())"
            );
            $userId = (int) Database::$connection->insert_id;
        }

        $checkCounselor = Database::search("SELECT counselor_id FROM counselors WHERE user_id = $userId LIMIT 1");
        if (!($checkCounselor && $checkCounselor->num_rows > 0)) {
            $rawAvail = $application['availabilitySchedule'];
            $availability = self::esc(
                (json_decode($rawAvail) !== null || $rawAvail === 'null') ? $rawAvail : '{}'
            );
            $consultationFee = $application['consultationFee'] !== null ? (float) $application['consultationFee'] : 'NULL';
            Database::iud(
                "INSERT INTO counselors
                    (user_id, title, specialty, specialty_short, bio, experience_years, education, certifications, languages_spoken, consultation_fee, availability_schedule, is_verified, created_at, updated_at)
                 VALUES
                    ($userId, '" . self::esc($application['title']) . "', '" . self::esc($application['specialty']) . "', '', '" . self::esc($application['bio']) . "', " . (int) ($application['experienceYears'] ?? 0) . ", '" . self::esc($application['education']) . "', '" . self::esc($application['certifications']) . "', '" . self::esc($application['languagesSpoken']) . "', $consultationFee, '$availability', 1, NOW(), NOW())"
            );
        }

        $adminRs  = Database::search("SELECT admin_id FROM admin WHERE user_id = " . max(0, $adminUserId) . " LIMIT 1");
        $adminRow = $adminRs ? $adminRs->fetch_assoc() : null;
        $reviewedBy = ($adminRow && !empty($adminRow['admin_id'])) ? (int) $adminRow['admin_id'] : null;
        $notes = self::esc('Application approved and counselor account created');

        Database::iud(
            "UPDATE counselor_applications
             SET status = 'approved',
                 admin_notes = '$notes',
                 reviewed_by = " . ($reviewedBy !== null ? $reviewedBy : 'NULL') . ",
                 review_date = NOW(),
                 updated_at = NOW()
             WHERE application_id = $applicationId"
        );

        return [
            'ok' => true,
            'message' => 'Application approved and counselor account created successfully.',
            'userId' => $userId,
            'email' => $application['email'],
            'name' => $application['fullName'],
        ];
    }
}
