<?php
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/task-request-change.model.php';

$userId = (int)$user['id'];
$taskId = (int)($_GET['taskId'] ?? $_POST['taskId'] ?? 0);

if ($taskId <= 0) {
    Response::redirect('/user/recovery');
}

$taskInfo = TaskRequestChangeModel::getTaskTitle($taskId, $userId);
$taskTitle = $taskInfo['title'] ?? 'Task';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = trim($_POST['reason'] ?? '');
    $change = trim($_POST['requested_change'] ?? '');

    if (strlen($reason) < 5) {
        $error = 'Please explain why you want to change this task.';
    } elseif (strlen($change) < 3) {
        $error = 'Please provide the new task title.';
    } else {
        $ok = TaskRequestChangeModel::create($taskId, $userId, $reason, $change);
        if ($ok) {
            Response::redirect('/user/recovery/task/change-requests?status=success');
        } else {
            $error = 'This task cannot be changed — it may not be part of a counselor-assigned plan.';
        }
    }
}

$data = [
    'taskId' => $taskId,
    'taskTitle' => $taskTitle,
    'error' => $error,
    'postedReason' => $_POST['reason'] ?? '',
    'postedChange' => $_POST['requested_change'] ?? '',
];

$pageTitle = 'Request Task Change';
$pageStyle = ['user/recovery', 'user/checkin'];

require __DIR__ . '/task-request-change.view.php';