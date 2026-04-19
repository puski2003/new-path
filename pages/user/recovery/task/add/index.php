<?php
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/task-add.model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::redirect('/user/recovery');
    exit;
}

$planId = (int)($_POST['planId'] ?? 0);
$userId = (int)$user['id'];

TaskAddModel::add($planId, $userId, [
    'title' => $_POST['title'] ?? '',
    'taskType' => $_POST['taskType'] ?? 'custom',
    'priority' => $_POST['priority'] ?? 'medium',
    'phase' => (int)($_POST['phase'] ?? 1),
]);

Response::redirect('/user/recovery/view?planId=' . $planId);