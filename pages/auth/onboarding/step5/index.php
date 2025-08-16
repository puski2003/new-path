<?php

/**
 * /auth/onboarding/step5 — onboarding now ends at step 4; redirect to dashboard.
 */
require_once __DIR__ . '/../../../../core/Auth.php';

Auth::requireRole('user');

Response::redirect('/user/dashboard');
