<?php
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../../recovery.model.php';

if (!Request::isPost()) {
    Response::redirect('/user/recovery/goals');
}

$goalId = (int)(Request::post('goal_id') ?? 0);
RecoveryModel::deleteGoal($goalId, (int)$user['id']);

Response::redirect('/user/recovery/goals?deleted=1');
