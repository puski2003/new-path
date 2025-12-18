<?php

$planId = (int) (Request::get('planId') ?? Request::post('planId') ?? 0);
if ($planId <= 0) {
    Response::redirect('/counselor/recovery-plans');
}

$errorMessage = null;
if (Request::isPost()) {
    if (CounselorRecoveryViewModel::update((int) ($user['counselorId'] ?? 0), $planId, $_POST)) {
        Response::redirect('/counselor/recovery-plans?updated=1');
    }
    $errorMessage = 'Failed to update recovery plan.';
}

$plan = CounselorRecoveryViewModel::getPlan((int) ($user['counselorId'] ?? 0), $planId);
if (!$plan) {
    Response::redirect('/counselor/recovery-plans');
}
$tasks = CounselorRecoveryViewModel::getTasks($planId);
$goals = CounselorRecoveryViewModel::getGoals($planId);
$clients = CounselorRecoveryViewModel::getClients((int) ($user['counselorId'] ?? 0));
