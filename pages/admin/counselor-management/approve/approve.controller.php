<?php
require_once __DIR__ . '/approve.model.php';
require_once ROOT . '/core/EmailTemplates.php';

$applicationId = (int) (Request::get('id') ?? 0);
$application = ApproveApplicationModel::getApplication($applicationId);
$error = '';
$preview = false;
$success = false;

if (!$application) {
    $error = 'Application not found or already processed.';
} else {
    if (Request::isPost()) {
        $username = trim(Request::post('generated_username') ?? '');
        $password = trim(Request::post('generated_password') ?? '');
        if ($username === '') $username = ApproveApplicationModel::generateUsername($application['fullName']);
        if ($password === '') $password = PasswordPool::getRandom();
    } else {
        $username = ApproveApplicationModel::generateUsername($application['fullName']);
        $password = PasswordPool::getRandom();
    }

    if (Request::isPost()) {
        $subject = trim(Request::post('subject') ?? '');
        $body = Request::post('body') ?? '';

        if (empty($subject) || empty($body)) {
            $error = 'Subject and body are required.';
            $preview = true;
        } else {
            require_once ROOT . '/core/Mailer.php';

            $result = ApproveApplicationModel::approve($applicationId, (int) $user['id'], $username, $password);

            if ($result['ok']) {
                $mailResult = Mailer::send($application['email'], $subject, $body, $application['fullName']);

                if ($mailResult) {
                    $_SESSION['flash_success'] = 'Application approved and email sent successfully.';
                } else {
                    $_SESSION['flash_warning'] = 'Application approved but email failed to send. You may need to contact the applicant manually.';
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

if ($preview && isset($username, $password)) {
    $emailTemplate = EmailTemplates::counselorApproval([
        'name' => $application['fullName'],
        'email' => $application['email'],
        'password' => $password,
    ]);
    $defaultSubject = $emailTemplate['subject'];
    $defaultBody = $emailTemplate['body'];
}
