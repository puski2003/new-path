<?php

/**
 * Step 3 Onboarding Controller
 */
$error = null;

if (Request::isPost()) {
    $action = Request::post('action') ?? 'submit';

    require_once __DIR__ . '/step3.model.php';
    $token = $_COOKIE['jwt'] ?? '';
    $payload = Auth::decode($token);

    if ($payload) {
        $userId = $payload['id'];

        if ($action === 'skip') {
            // Give 0 score or set a null value for assessment
            if (Step3Model::saveAssessmentScore($userId, 0)) {
                Response::redirect('/auth/onboarding/step4');
            } else {
                $error = 'Failed to skip assessment. Please try again.';
            }
        } else {
            // Calculate a score manually since we don't have a specific table for individual answers.
            // (Assuming we save to a theoretical column or we just skip this DB insert and move step)
            $q1 = (int)(Request::post('q1') ?? 0);
            $q2 = (int)(Request::post('q2') ?? 0);
            $q3 = (int)(Request::post('q3') ?? 0);
            $q4 = (int)(Request::post('q4') ?? 0);
            $q5 = (int)(Request::post('q5') ?? 0);

            if (!$q1 || !$q2 || !$q3 || !$q4 || !$q5) {
                $error = 'Please answer all questions or choose to skip the assessment.';
            } else {
                $score = $q1 + $q2 + $q3 + $q4 + $q5;
                if (Step3Model::saveAssessmentScore($userId, $score)) {
                    Response::redirect('/auth/onboarding/step4');
                } else {
                    $error = 'Failed to save assessment. Please try again.';
                }
            }
        }
    } else {
        Response::redirect('/auth/login/user');
    }
}
