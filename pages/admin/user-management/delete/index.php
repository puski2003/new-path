<?php
require_once __DIR__ . '/../../common/admin.head.php';
require_once __DIR__ . '/../user-management.model.php';

if (!Request::isPost()) {
    Response::redirect('/admin/user-management?alertType=warning&alertMessage=' . urlencode('Invalid request method.'));
}

$userId = (int) (Request::post('userId') ?? 0);
$result = UserManagementModel::banUser($userId, (int) $user['id']);

$type = urlencode((string) ($result['type'] ?? 'error'));
$message = urlencode((string) ($result['message'] ?? 'Delete failed.'));

Response::redirect("/admin/user-management?alertType={$type}&alertMessage={$message}");
