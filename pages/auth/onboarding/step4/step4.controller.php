<?php

/**
 * Step 4 Onboarding Controller
 */
$error = null;

require_once __DIR__ . '/step4.model.php';
$token   = $_COOKIE['jwt'] ?? '';
$payload = Auth::decode($token);
$riskScore = 5;
if ($payload) {
    $riskScore = Step4Model::getRiskScore((int) $payload['id']);
}

if ($riskScore >= 12) {
    $riskBand    = 'HIGH';
    $defaultPlan = 'counselor';
} elseif ($riskScore >= 8) {
    $riskBand    = 'MODERATE';
    $defaultPlan = 'system';
} else {
    $riskBand    = 'LOW';
    $defaultPlan = 'system';
}

if (Request::isPost()) {
    $selectedPlan = trim(Request::post('selectedPlan') ?? '');

    if ($selectedPlan === '') {
        $error = 'Please select a recovery path to continue.';
    } else {
        if (!$payload) $payload = Auth::decode($_COOKIE['jwt'] ?? '');

        if ($payload) {
            $userId = (int)$payload['id'];

            // Mark onboarding complete regardless of path chosen
            Database::iud(
                "UPDATE users SET onboarding_completed = 1, current_onboarding_step = 5
                 WHERE user_id = $userId"
            );

            if ($selectedPlan === 'counselor') {
                Response::redirect('/user/counselors');
            } else {
                // 'system' — send to browse page to pick a plan
                Response::redirect('/user/recovery/browse');
            }
        } else {
            Response::redirect('/auth/login/user');
        }
    }
}
