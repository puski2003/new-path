<?php
$authUser = Auth::requireRole('counselor');
$userId   = (int) ($authUser['id'] ?? 0);

Database::setUpConnection();
$rs   = Database::search("SELECT must_change_password FROM users WHERE user_id = $userId LIMIT 1");
$row  = $rs ? $rs->fetch_assoc() : null;

if (!$row || empty($row['must_change_password'])) {
    Response::redirect('/counselor/dashboard');
    exit;
}

require_once __DIR__ . '/set-password.model.php';

$error   = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword     = Request::post('new_password') ?? '';
    $confirmPassword = Request::post('confirm_password') ?? '';

    if (strlen($newPassword) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        SetPasswordModel::update($userId, $newPassword);
        Response::redirect('/counselor/dashboard');
        exit;
    }
}

require __DIR__ . '/set-password.view.php';
