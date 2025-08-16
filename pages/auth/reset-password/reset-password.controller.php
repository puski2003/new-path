<?php

/**
 * Reset Password Controller
 *
 * GET  /auth/reset-password?token=<hex>
 *   — Validate token, show form if valid, show expiry error if not.
 *
 * POST /auth/reset-password?token=<hex>
 *   — Validate token again, hash new password, update DB, redirect to login.
 */
require_once __DIR__ . '/reset-password.model.php';

$error    = null;
$done     = false;   // true after a successful reset
$token    = trim($_GET['token'] ?? '');
$tokenUser = null;   // the user row if token is valid

// Always validate token on every request (GET and POST)
if ($token !== '') {
    $tokenUser = ResetPasswordModel::findByToken($token);
}

if (Request::isPost()) {

    if ($tokenUser === null) {
        // Token missing, invalid, or expired — nothing to do
        $error = 'This reset link is invalid or has expired. Please request a new one.';
    } else {
        $password        = Request::post('password') ?? '';
        $passwordConfirm = Request::post('password_confirm') ?? '';

        if (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $passwordConfirm) {
            $error = 'Passwords do not match.';
        } else {
            // Hash with BCrypt using $2y$ prefix (PHP native)
            $hash = password_hash($password, PASSWORD_BCRYPT);
            ResetPasswordModel::updatePassword((int) $tokenUser['user_id'], $hash);
            $done = true;
        }
    }
}
