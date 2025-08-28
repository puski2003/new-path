<?php
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../../recovery.model.php';

header('Content-Type: application/json');

if (!Request::isPost()) {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$taskId = (int)(Request::post('taskId') ?? 0);
if ($taskId <= 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid task.']);
    exit;
}

$reverted = RecoveryModel::uncompleteTask($taskId, (int)$user['id']);
if (!$reverted) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Task could not be reverted.']);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Task reverted to pending.',
    'taskId'  => $taskId,
]);
