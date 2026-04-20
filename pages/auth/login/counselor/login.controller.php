<?php

/**
 * Counselor Login Controller
 */
$error = null;

if (Request::isPost()) {
    $email    = trim(Request::post('email') ?? '');
    $password = Request::post('password') ?? '';

    if ($email === '' || $password === '') {
        $error = 'Professional email and password are required.';
    } else {
        require_once __DIR__ . '/login.model.php';
        $user = LoginModel::findByEmail($email);

        // Normalize Java BCrypt prefix ($2a$ -> $2y$)
        $hash = $user['password_hash'] ?? '';
        if (str_starts_with($hash, '$2a$')) {
            $hash = '$2y$' . substr($hash, 4);
        }

        if ($user === null || !password_verify($password, $hash)) {
            $error = 'Invalid credentials or non-counselor account.';
        } else {
            $displayName = $user['display_name'] ?: ($user['first_name'] ?: 'Counselor');

            $token = Auth::sign([
                'id'   => $user['user_id'],
                'name' => $displayName,
                'role' => $user['role'],
            ]);

            Auth::setTokenCookie($token);

            if (!empty($user['must_change_password'])) {
                Response::redirect('/counselor/set-password');
            } else {
                Response::redirect('/counselor/dashboard');
            }
        }
    }
}
