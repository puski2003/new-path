<?php
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/task-edit.model.php';

$userId = (int)$user['id'];
$taskId = (int)($_GET['taskId'] ?? $_POST['taskId'] ?? 0);
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $planId = (int)($_POST['planId'] ?? 0);
    TaskEditModel::update($taskId, $userId, [
        'title' => $_POST['title'] ?? '',
        'taskType' => $_POST['taskType'] ?? 'custom',
        'priority' => $_POST['priority'] ?? 'medium',
    ]);
    Response::redirect('/user/recovery/view?planId=' . $planId);
}

$task = TaskEditModel::getTask($taskId, $userId);
if (!$task) {
    Response::redirect('/user/recovery');
}

$data = ['task' => $task, 'error' => $error];
$pageTitle = 'Edit Task';
$pageStyle = ['user/dashboard'];

require __DIR__ . '/task-edit.view.php';