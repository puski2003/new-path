<?php

/**
 * Step 2 Onboarding Controller
 */
$error = null;
$userProfile = null;
$evaluation = null;
$addictionModules = [];

$token   = $_COOKIE['jwt'] ?? '';
$payload = Auth::decode($token);

if ($payload) {
    $userId = $payload['id'];

    $rs = Database::search("SELECT * FROM user_profiles WHERE user_id = $userId");
    $userProfile = $rs->fetch_assoc();

    $rsEval = Database::search("SELECT * FROM onboarding_evaluation WHERE user_id = $userId");
    $evaluation = $rsEval->fetch_assoc();

    $addictionModules = Database::search("SELECT module_key, display_name FROM addiction_type_module ORDER BY display_name")->fetch_all(MYSQLI_ASSOC);
}

if (Request::isPost()) {
    $usesSubstances   = Request::post('usesSubstances') ?? '';
    $primarySubstance = Request::post('primarySubstance') ?? '';
    $frequency        = Request::post('frequency') ?? '';
    $lastUsed         = Request::post('lastUsed') ?? '';
    $quitAttemptsStr  = Request::post('quitAttempts') ?? '';
    $motivation       = Request::post('motivation') ?? '';
    $addictions     = $_POST['addictions'] ?? [];

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
    } elseif ($motivation !== 'exploring' && empty($addictions)) {
        $error = 'Please select at least one addiction type.';
    } else {
        require_once __DIR__ . '/step2.model.php';

        if ($payload) {
            $userId       = $payload['id'];
            $quitAttempts = ($quitAttemptsStr !== '') ? (int) $quitAttemptsStr : 0;

            if (Step2Model::saveSubstanceInfo($userId, $primarySubstance, $frequency, $lastUsed, $quitAttempts, $motivation)) {
                Step2Model::saveEvaluation($userId, $addictions);
                Response::redirect('/auth/onboarding/step3');
            } else {
                $error = 'Failed to save information. Please try again.';
            }
        } else {
            Response::redirect('/auth/login/user');
        }
    }
}
