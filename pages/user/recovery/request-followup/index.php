<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/../recovery.model.php';

if (!Request::isPost()) {
    Response::redirect('/user/recovery');
}

$userId = (int)$user['id'];
$planId = (int)(Request::post('planId') ?? 0);

if ($planId > 0) {
    RecoveryModel::requestFollowUpPlan($userId, $planId);
}

Response::redirect('/user/recovery?followupRequested=1');
