<?php

class RejectApplicationModel
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

    public static function reject(int $applicationId, int $adminUserId, string $notes): array
    {
        $application = self::getApplication($applicationId);
        if (!$application) {
            return ['ok' => false, 'message' => 'Application not found or already processed.'];
        }

        $adminRs  = Database::search("SELECT admin_id FROM admin WHERE user_id = " . max(0, $adminUserId) . " LIMIT 1");
        $adminRow = $adminRs ? $adminRs->fetch_assoc() : null;
        $reviewedBy = ($adminRow && !empty($adminRow['admin_id'])) ? (int) $adminRow['admin_id'] : null;
        $safeNotes = self::esc($notes);

        Database::iud(
            "UPDATE counselor_applications
             SET status = 'rejected',
                 admin_notes = '$safeNotes',
                 reviewed_by = " . ($reviewedBy !== null ? $reviewedBy : 'NULL') . ",
                 review_date = NOW(),
                 updated_at = NOW()
             WHERE application_id = $applicationId"
        );

        return [
            'ok' => true,
            'message' => 'Application rejected successfully.',
            'email' => $application['email'],
            'name' => $application['fullName'],
        ];
    }
}
