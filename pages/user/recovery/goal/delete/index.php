<?php
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/goal-delete.model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::redirect('/user/recovery/goals');
    exit;
}

$goalId = (int)($_POST['goal_id'] ?? 0);
GoalDeleteModel::delete($goalId, (int)$user['id']);

Response::redirect('/user/recovery/goals?status=success&msg=deleted');