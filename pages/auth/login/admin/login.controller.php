<?php

/**
 * Admin Login Controller
 */
$error = null;

if (Request::isPost()) {
    $email    = trim(Request::post('email') ?? '');
    $password = Request::post('password') ?? '';

    if ($email === '' || $password === '') {
        $error = 'Admin email and password are required.';
    } else {
        require_once __DIR__ . '/login.model.php';
        $user = LoginModel::findByEmail($email);

       
        $hash = $user['password_hash'] ?? '';
        if (str_starts_with($hash, '$2a$')) {
            $hash = '$2y$' . substr($hash, 4);
        }

        if ($user === null || !password_verify($password, $hash)) {
            $error = 'Invalid admin credentials or insufficient permissions.';
        } else {
            $displayName = $user['display_name'] ?: ($user['first_name'] ?: 'Admin');

            $token = Auth::sign([
                'id'   => $user['user_id'],
                'name' => $displayName,
                'role' => $user['role'],
            ]);

            Auth::setTokenCookie($token);
            Response::redirect('/admin/dashboard');
        }
    }
}
