<?php
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/goal-create.model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::redirect('/user/recovery/goals');
    exit;
}

$title = trim($_POST['title'] ?? '');
$goalType = $_POST['goal_type'] ?? 'short_term';
$targetDays = (int)($_POST['target_days'] ?? 0);
$description = trim($_POST['description'] ?? '');

$ok = GoalCreateModel::create((int)$user['id'], $title, $goalType, $targetDays, $description);

Response::redirect($ok ? '/user/recovery/goals?status=success&msg=created' : '/user/recovery/goals?status=error&msg=no_active_plan');