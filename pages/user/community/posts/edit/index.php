<?php
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../../community.model.php';

if (!Request::isPost()) {
    Response::redirect('/user/community');
}

$userId = (int)$user['id'];
$postId = (int)(Request::post('postId') ?? 0);
$imageUrl = null;
$hasImageUpload = !empty($_FILES['image']) && (int)($_FILES['image']['error'] ?? 1) === 0;
if ($hasImageUpload) {
    $imageUrl = CommunityModel::handleUpload($_FILES['image']);
}

$data = [
    'title' => Request::post('title') ?? '',
    'content' => Request::post('content') ?? '',
    'postType' => Request::post('postType') ?? 'general',
    'privacy' => Request::post('privacy') ?? '',
];
if ($hasImageUpload) {
    $data['imageUrl'] = $imageUrl ?? '';
}

CommunityModel::updatePost($userId, $postId, $data);
Response::redirect('/user/community');
