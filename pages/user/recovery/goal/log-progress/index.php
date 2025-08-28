<?php
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../../recovery.model.php';

if (!Request::isPost()) {
    Response::redirect('/user/recovery/goals');
}

$goalId  = (int)(Request::post('goal_id') ?? 0);
$days    = max(1, (int)(Request::post('days') ?? 1));
$returnTo = Request::post('returnTo') ?? 'goals';

RecoveryModel::logGoalProgress($goalId, (int)$user['id'], $days);

$base = $returnTo === 'dashboard' ? '/user/dashboard' : '/user/recovery/goals';
Response::redirect($base . '?progressLogged=1');
