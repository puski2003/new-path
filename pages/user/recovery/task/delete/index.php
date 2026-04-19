<?php
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/task-delete.model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::redirect('/user/recovery');
    exit;
}

$taskId = (int)($_POST['taskId'] ?? 0);
$planId = (int)($_POST['planId'] ?? 0);

TaskDeleteModel::delete($taskId, (int)$user['id']);

Response::redirect('/user/recovery/view?planId=' . $planId);