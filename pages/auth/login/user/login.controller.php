<?php

/**
 * Login Controller — GET: show form, POST: authenticate user.
 *
 * DB schema notes:
 *   - Primary key: user_id
 *   - Password column: password_hash (BCrypt, $2a$ from Java — PHP accepts it)
 *   - Name: display_name (falls back to first_name if empty)
 */
$error = null;

if (Request::isPost()) {
    $email    = trim(Request::post('email') ?? '');
    $password = Request::post('password') ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email and password are required.';
    } else {
        require_once __DIR__ . '/login.model.php';
        $user = LoginModel::findByEmail($email);

        // Java stores BCrypt as $2a$; PHP password_verify works with both $2a$ and $2y$
        $hash = $user['password_hash'] ?? '';
        // Normalise $2a$ → $2y$ so password_verify accepts it
        if (str_starts_with($hash, '$2a$')) {
            $hash = '$2y$' . substr($hash, 4);
        }

        if ($user === null || !password_verify($password, $hash)) {
            $error = 'Invalid email or password.';
        } elseif (($user['status'] ?? 'active') === 'banned') {
            $error = 'Your account has been banned. Please contact admin@newpath.com for assistance.';
        } else {
            Database::iud("UPDATE users SET last_login = NOW() WHERE user_id = " . (int) $user['user_id']);

            $displayName = $user['display_name'] ?: ($user['first_name'] ?: 'User');

            $token = Auth::sign([
                'id'   => $user['user_id'],
                'name' => $displayName,
                'role' => $user['role'],
            ]);

            Auth::setTokenCookie($token);

            $destinations = [
                'admin'     => '/admin/dashboard',
                'counselor' => '/counselor/dashboard',
                'user'      => '/user/dashboard',
            ];

            // Specific logic for "user" role regarding onboarding
            if ($user['role'] === 'user') {
                if (!$user['onboarding_completed']) {
                    $step = $user['current_onboarding_step'] ?? 1;
                    Response::redirect('/auth/onboarding/step' . $step);
                } else {
                    Response::redirect('/user/dashboard');
                }
            } else {
                Response::redirect($destinations[$user['role']] ?? '/auth/login');
            }
        }
    }
}
