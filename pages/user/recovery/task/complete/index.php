<?php
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/task-complete.model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::redirect('/user/recovery');
    exit;
}

$taskId = (int)($_POST['taskId'] ?? 0);
$returnTo = $_POST['returnTo'] ?? '';
$userId = (int)$user['id'];

$result = false;
if ($taskId > 0) {
    $result = TaskCompleteModel::complete($taskId, $userId);
}

if ($result) {
    TaskCompleteModel::checkAndAwardAchievements($userId);
    $completedPlan = TaskCompleteModel::checkPlanCompleted($taskId);
    if ($completedPlan) {
        Response::redirect('/user/recovery/plan-completed?planId=' . (int)$completedPlan['plan_id']);
    }
}

if ($returnTo === 'dashboard') {
    Response::redirect($result ? '/user/dashboard?status=success' : '/user/dashboard?status=error');
} else {
    Response::redirect($result ? '/user/recovery?status=success' : '/user/recovery?status=error&msg=task_blocked');
}