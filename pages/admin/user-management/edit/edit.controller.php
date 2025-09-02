<?php
require_once __DIR__ . '/edit.model.php';

$userId = (int) (Request::get('id') ?? Request::post('userId') ?? 0);
$editUser = EditUserModel::getUser($userId);

$error = '';

if (!$editUser) {
    $error = 'User not found.';
} elseif (Request::isPost()) {
    // Enforce actor identity from server-side auth, not from client payload.
    $_POST['actorUserId'] = (int) ($user['id'] ?? 0);
    $result = EditUserModel::update($userId, $_POST);

    if (!empty($result['ok'])) {
        Response::redirect('/admin/user-management?alertType=' . urlencode($result['type']) . '&alertMessage=' . urlencode($result['message']));
    }

    $error = (string) ($result['message'] ?? 'Update failed.');
    $editUser = EditUserModel::getUser($userId);
}
