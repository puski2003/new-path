<?php

require_once __DIR__ . '/../common/counselor.data.php';

class CounselorApplicationsModel
{
    public static function submit(array $input): array
    {
        return CounselorData::createApplication($input);
    }

    /**
     * Upload a supporting document (certificate / license) for a counselor application.
     * Accepts JPG, PNG, PDF, DOC, DOCX. Max 10 MB.
     * Returns the stored path on success, null on failure.
     */
    public static function handleDocumentUpload(array $file): ?string
    {
        if (empty($file) || !isset($file['tmp_name']) || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        $maxBytes = 10 * 1024 * 1024; // 10 MB
        if ((int)$file['size'] > $maxBytes || (int)$file['size'] <= 0) {
            return null;
        }

        $allowedMimes = [
            'image/jpeg', 'image/png',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        $mime = mime_content_type($file['tmp_name']) ?: '';
        if (!in_array($mime, $allowedMimes, true)) {
            return null;
        }

        $extMap = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        ];
        $ext = $extMap[$mime] ?? 'bin';

        $filename  = bin2hex(random_bytes(16)) . '.' . $ext;
        $targetDir = ROOT . '/public/uploads/applications';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }

        $target = $targetDir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return null;
        }

        return '/uploads/applications/' . $filename;
    }
}
