<?php
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../../recovery.model.php';

if (!Request::isPost()) {
    Response::redirect('/user/recovery');
}

$taskId   = (int)(Request::post('taskId') ?? 0);
$returnTo = Request::post('returnTo') ?? '';

$result = false;
if ($taskId > 0) {
    $result = RecoveryModel::completeTask($taskId, (int)$user['id']);
}

if ($result) {
    RecoveryModel::checkAndAwardAchievements((int)$user['id']);

    // Check if completing this task finished the whole plan
    $planRs = Database::search(
        "SELECT rp.plan_id, rp.status
         FROM recovery_tasks rt
         INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
         WHERE rt.task_id = $taskId
         LIMIT 1"
    );
    if ($planRs && ($planRow = $planRs->fetch_assoc()) && $planRow['status'] === 'completed') {
        Response::redirect('/user/recovery/plan-completed?planId=' . (int)$planRow['plan_id']);
    }
}

if ($returnTo === 'dashboard') {
    Response::redirect($result ? '/user/dashboard?taskCompleted=1' : '/user/dashboard?taskBlocked=1');
} else {
    Response::redirect($result ? '/user/recovery?taskCompleted=1' : '/user/recovery?taskBlocked=1');
}
