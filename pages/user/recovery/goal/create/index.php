<?php
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../../recovery.model.php';

if (!Request::isPost()) {
    Response::redirect('/user/recovery/goals');
}

$title       = trim(Request::post('title') ?? '');
$goalType    = Request::post('goal_type') ?? 'short_term';
$targetDays  = (int)(Request::post('target_days') ?? 0);
$description = trim(Request::post('description') ?? '');

$ok = RecoveryModel::createGoal((int)$user['id'], $title, $goalType, $targetDays, $description);

Response::redirect($ok ? '/user/recovery/goals?created=1' : '/user/recovery/goals?error=no_active_plan');
