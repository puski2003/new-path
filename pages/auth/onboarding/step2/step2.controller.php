<?php

/**
 * Step 2 Onboarding Controller
 */
$error = null;

if (Request::isPost()) {
    $usesSubstances   = Request::post('usesSubstances') ?? '';
    $primarySubstance = Request::post('primarySubstance') ?? '';
    $frequency        = Request::post('frequency') ?? '';
    $lastUsed         = Request::post('lastUsed') ?? '';
    $quitAttemptsStr  = Request::post('quitAttempts') ?? '';
    $motivation       = Request::post('motivation') ?? '';

    if ($usesSubstances === 'no') {
        $primarySubstance = 'None';
        $frequency        = 'None';
        $lastUsed         = 'Never';
        $quitAttemptsStr  = '0';
    }

    if ($usesSubstances === '') {
        $error = 'Please answer if you use alcohol or drugs.';
    } elseif ($usesSubstances === 'yes' && ($primarySubstance === '' || $frequency === '' || $lastUsed === '')) {
        $error = 'Please fill out all the details about your substance use.';
    } elseif ($motivation === '') {
        $error = 'Please tell us about your recovery motivation.';
    } else {
        require_once __DIR__ . '/step2.model.php';

        $token   = $_COOKIE['jwt'] ?? '';
        $payload = Auth::decode($token);

        if ($payload) {
            $userId       = $payload['id'];
            $quitAttempts = ($quitAttemptsStr !== '') ? (int) $quitAttemptsStr : 0;

            if (Step2Model::saveSubstanceInfo($userId, $primarySubstance, $frequency, $lastUsed, $quitAttempts, $motivation)) {
                Response::redirect('/auth/onboarding/step3');
            } else {
                $error = 'Failed to save information. Please try again.';
            }
        } else {
            Response::redirect('/auth/login/user');
        }
    }
}
