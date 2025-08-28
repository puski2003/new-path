<?php
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../../recovery.model.php';

if (!Request::isPost()) {
    Response::redirect('/user/recovery');
}

$planId = (int)(Request::post('planId') ?? 0);
$userId = (int)$user['id'];

RecoveryModel::addUserTask($planId, $userId, [
    'title'    => Request::post('title') ?? '',
    'taskType' => Request::post('taskType') ?? 'custom',
    'priority' => Request::post('priority') ?? 'medium',
    'phase'    => (int)(Request::post('phase') ?? 1),
]);

Response::redirect('/user/recovery/view?planId=' . $planId);
