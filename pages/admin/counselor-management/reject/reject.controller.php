<?php
require_once __DIR__ . '/reject.model.php';
require_once ROOT . '/core/EmailTemplates.php';

$applicationId = (int) (Request::get('id') ?? 0);
$application = RejectApplicationModel::getApplication($applicationId);
$error = '';
$preview = false;
$success = false;

if (!$application) {
    $error = 'Application not found or already processed.';
} else {
    $rejectionNotes = trim(Request::post('notes') ?? '');

    if (Request::isPost()) {
        $subject = trim(Request::post('subject') ?? '');
        $body = Request::post('body') ?? '';

        if (empty($subject) || empty($body)) {
            $error = 'Subject and body are required.';
            $preview = true;
        } else {
            require_once ROOT . '/core/Mailer.php';

            $result = RejectApplicationModel::reject($applicationId, (int) $user['id'], $rejectionNotes);

            if ($result['ok']) {
                $mailResult = Mailer::send($application['email'], $subject, $body, $application['fullName']);

                if ($mailResult) {
                    $_SESSION['flash_success'] = 'Application rejected and email sent successfully.';
                } else {
                    $_SESSION['flash_warning'] = 'Application rejected but email failed to send. You may need to contact the applicant manually.';
                }

                Response::redirect('/admin/counselor-management');
            } else {
                $error = $result['message'];
                $preview = true;
            }
        }
    } else {
        $preview = true;
    }
}

if ($preview) {
    $emailTemplate = EmailTemplates::counselorRejection([
        'name' => $application['fullName'],
        'reason' => $rejectionNotes,
    ]);
    $defaultSubject = $emailTemplate['subject'];
    $defaultBody = $emailTemplate['body'];
}
