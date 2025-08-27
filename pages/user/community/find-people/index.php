<?php
/**
 * Route: /user/community/find-people
 */
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/../community.model.php';
require_once __DIR__ . '/../../common/user-profile.model.php';

$userId = (int)$user['id'];
$searchQuery = Request::get('q') ?? '';
$tab = 'people';
$scope = 'all';
$posts = [];

$users = UserProfileModel::getUsers($userId, ['q' => $searchQuery]);

$dmConversations = DirectMessageModel::getConversations($userId);
$pendingRequests  = DirectMessageModel::getPendingConnectionRequests($userId);
$supportGroups    = SupportGroupModel::getUserGroups($userId);
$totalDmUnread    = DirectMessageModel::getUnreadCount($userId);
$totalGroupUnread = SupportGroupModel::getUnreadGroupMessageCount($userId);

$pageTitle = 'Find People';
$pageStyle  = ['user/dashboard', 'user/community', 'user/chat', 'user/find-people'];

require_once __DIR__ . '/../community.layout.php';
