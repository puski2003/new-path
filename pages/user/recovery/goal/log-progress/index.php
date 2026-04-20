<?php
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/goal-log-progress.model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::redirect('/user/recovery/goals');
    exit;
}

$goalId = (int)($_POST['goal_id'] ?? 0);
$days = max(1, (int)($_POST['days'] ?? 1));
$returnTo = $_POST['returnTo'] ?? 'goals';

GoalLogProgressModel::logProgress($goalId, (int)$user['id'], $days);

$base = $returnTo === 'dashboard' ? '/user/dashboard' : '/user/recovery/goals';
Response::redirect($base . ($returnTo === 'goals' ? '?status=success&msg=progress_logged' : '?status=success'));