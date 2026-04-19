<?php
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/goal-update.model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::redirect('/user/recovery/goals');
    exit;
}

$goalId = (int)($_POST['goal_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$goalType = $_POST['goal_type'] ?? 'short_term';
$targetDays = (int)($_POST['target_days'] ?? 0);
$description = trim($_POST['description'] ?? '');

GoalUpdateModel::update($goalId, (int)$user['id'], $title, $goalType, $targetDays, $description);

Response::redirect('/user/recovery/goals?status=success&msg=updated');