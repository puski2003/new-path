<?php
/**
 * Route: /user/community/posts/comments
 *
 * GET  ?post_id=X  — fetch comments for a post
 * POST (JSON body)  — add a comment to a post
 */
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../../community.model.php';

header('Content-Type: application/json');

$userId = (int)$user['id'];

if (Request::isPost()) {
    $raw     = file_get_contents('php://input');
    $payload = json_decode($raw, true);
    $postId  = (int)($payload['postId'] ?? 0);
    $content = trim((string)($payload['content'] ?? ''));

    if ($postId <= 0 || $content === '' || strlen($content) > 1000) {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
        exit;
    }

    $comment = CommunityModel::addComment($userId, $postId, $content);
    if ($comment) {
        echo json_encode(['success' => true, 'comment' => $comment]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add comment']);
    }
    exit;
}

// GET — load comments for a post
$postId = (int)(Request::get('post_id') ?? 0);
if ($postId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid post id']);
    exit;
}

$comments = CommunityModel::getComments($postId);
echo json_encode(['success' => true, 'comments' => $comments]);
exit;
