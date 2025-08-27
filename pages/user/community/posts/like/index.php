<?php
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../../community.model.php';

header('Content-Type: application/json');

if (!Request::isPost()) {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
$postId = (int)($payload['postId'] ?? 0);

if ($postId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid post id']);
    exit;
}

$result = CommunityModel::toggleLike($postId, (int)$user['id']);
echo json_encode(['success' => true, 'liked' => $result['liked']]);
exit;
