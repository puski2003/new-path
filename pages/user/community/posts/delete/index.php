<?php
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../../community.model.php';

if (!Request::isPost()) {
    Response::redirect('/user/community');
}

$userId = (int)$user['id'];
$postId = (int)(Request::post('postId') ?? 0);
CommunityModel::deletePost($userId, $postId);

Response::redirect('/user/community');
