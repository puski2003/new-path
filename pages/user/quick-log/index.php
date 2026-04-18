<?php
require_once __DIR__ . '/../common/user.head.php';

header('Content-Type: application/json');

if (!Request::isPost()) {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

Database::setUpConnection();

$userId = (int)$user['id'];
$logText = trim((string)(Request::post('logText') ?? ''));

if ($logText === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please enter something before saving.']);
    exit;
}

$safeContent = Database::$connection->real_escape_string($logText);
$safeTitle = Database::$connection->real_escape_string('Quick Log - ' . date('M j, Y'));

Database::iud(
    "INSERT INTO journal_entries (user_id, title, content, category_id, mood, is_highlight, is_private)
     VALUES ($userId, '$safeTitle', '$safeContent', NULL, NULL, 0, 1)"
);

echo json_encode([
    'success' => true,
    'message' => 'Quick log saved successfully.',
]);
