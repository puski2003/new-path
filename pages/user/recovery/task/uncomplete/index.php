<?php
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/task-uncomplete.model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::redirect('/user/recovery');
    exit;
}

$taskId = (int)($_POST['taskId'] ?? 0);
if ($taskId > 0) {
    TaskUncompleteModel::uncomplete($taskId, (int)$user['id']);
}

Response::redirect('/user/recovery?status=success');