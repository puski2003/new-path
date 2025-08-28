<?php
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../../recovery.model.php';

if (!Request::isPost()) {
    Response::redirect('/user/recovery/goals');
}

$goalId      = (int)(Request::post('goal_id') ?? 0);
$title       = trim(Request::post('title') ?? '');
$goalType    = Request::post('goal_type') ?? 'short_term';
$targetDays  = (int)(Request::post('target_days') ?? 0);
$description = trim(Request::post('description') ?? '');

RecoveryModel::updateGoal($goalId, (int)$user['id'], $title, $goalType, $targetDays, $description);

Response::redirect('/user/recovery/goals?updated=1');
