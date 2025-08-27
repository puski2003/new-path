<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/../community.model.php';

if (!Request::isPost()) {
    Response::redirect('/user/community');
}

$userId = (int)$user['id'];
$imageUrl = null;
if (!empty($_FILES['image'])) {
    $imageUrl = CommunityModel::handleUpload($_FILES['image']);
}

CommunityModel::createPost($userId, [
    'title' => Request::post('title') ?? '',
    'content' => Request::post('content') ?? '',
    'postType' => Request::post('postType') ?? 'general',
    'privacy' => Request::post('privacy') ?? '',
    'imageUrl' => $imageUrl,
]);

Response::redirect('/user/community');
