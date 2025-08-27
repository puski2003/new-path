<?php
/**
 * Route: /user/community/posts/report
 *
 * POST (JSON body) — submit a report for a post
 */
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../../community.model.php';

header('Content-Type: application/json');

if (!Request::isPost()) {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$raw     = file_get_contents('php://input');
$payload = json_decode($raw, true);
$postId  = (int)($payload['postId'] ?? 0);
$reason  = trim((string)($payload['reason'] ?? ''));
$description = trim((string)($payload['description'] ?? ''));

if ($postId <= 0 || $reason === '') {
    echo json_encode(['success' => false, 'message' => 'Please select a reason']);
    exit;
}

$ok = CommunityModel::reportPost((int)$user['id'], $postId, $reason, $description);
echo json_encode(['success' => $ok]);
exit;
