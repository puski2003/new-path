<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/../recovery.model.php';

if (!Request::isPost()) {
    Response::redirect('/user/recovery');
}

$planId = (int)(Request::post('planId') ?? 0);
$result = false;
if ($planId > 0) {
    $result = RecoveryModel::acceptAssignedPlan($planId, (int)$user['id']);
}

if ($result) {
    Response::redirect('/user/recovery?accepted=1');
} else {
    Response::redirect('/user/recovery/manage?error=already_active');
}
