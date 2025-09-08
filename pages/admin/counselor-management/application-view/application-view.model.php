<?php

class ApplicationViewModel
{
    public static function getApplication(int $applicationId): ?array
    {
        $safeId = max(0, $applicationId);
        $rs = Database::search(
            "SELECT * FROM counselor_applications WHERE application_id = $safeId LIMIT 1"
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
            'status' => $row['status'] ?? 'pending',
            'adminNotes' => $row['admin_notes'] ?? '',
            'applicationDate' => $row['created_at'] ?? '',
            'reviewDate' => $row['review_date'] ?? '',
            'reviewerName' => $row['reviewed_by'] ?? '',
        ];
    }
}
