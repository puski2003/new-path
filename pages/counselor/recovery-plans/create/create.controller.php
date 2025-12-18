<?php

$errorMessage = null;
$clients = CounselorRecoveryCreateModel::getClients((int) ($user['counselorId'] ?? 0));
if (Request::isPost()) {
    if (CounselorRecoveryCreateModel::create((int) ($user['counselorId'] ?? 0), $_POST)) {
        Response::redirect('/counselor/recovery-plans');
    }
    $errorMessage = 'Failed to create recovery plan.';
}
