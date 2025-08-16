<?php

/**
 * Step 1 Onboarding Controller
 */
$error = null;

// Allow GET requests normally to render the layout.

if (Request::isPost()) {
    $email       = trim(Request::post('email') ?? '');
    $password    = Request::post('password') ?? '';
    $rePassword  = Request::post('rePassword') ?? '';
    $displayName = trim(Request::post('displayName') ?? '');
    $ageStr      = trim(Request::post('age') ?? '');
    $gender      = trim(Request::post('gender') ?? '');

    if ($email === '' || $password === '' || $displayName === '') {
        $error = 'Email, password, and display name are required.';
    } elseif ($password !== $rePassword) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        require_once __DIR__ . '/step1.model.php';

        if (Step1Model::emailExists($email)) {
            $error = 'Email is already registered.';
        } else {
            $age = ($ageStr !== '') ? (int)$ageStr : null;
            $user = Step1Model::createUser($email, $password, $displayName, $age, $gender);

            if ($user) {
                // Generate JWT and "login"
                $token = Auth::sign([
                    'id'   => $user['user_id'],
                    'name' => $user['display_name'],
                    'role' => $user['role'],
                ]);

                Auth::setTokenCookie($token);

                // Immediately redirect to step 2 after initial registration
                Response::redirect('/auth/onboarding/step2');
            } else {
                $error = 'Failed to create account. Please try again.';
            }
        }
    }
}
