<?php
require_once __DIR__ . '/../common/user.head.php';
require_once __DIR__ . '/../community/community.model.php';

require_once __DIR__ . '/../common/user-profile.model.php';

$userId = (int)$user['id'];
$profileUserId = (int)(Request::get('id') ?? 0);

if ($profileUserId <= 0) {
    header('Location: /user/community/find-people');
    exit;
}

$profile = UserProfileModel::getProfile($profileUserId, $userId);

if (!$profile) {
    header('Location: /user/community/find-people');
    exit;
}

$pageTitle = $profile['isOwnProfile'] ? 'My Profile' : $profile['displayName'];

$dmConversations = DirectMessageModel::getConversations($userId);
$pendingRequests = DirectMessageModel::getPendingConnectionRequests($userId);
$supportGroups = SupportGroupModel::getUserGroups($userId);
$totalDmUnread = DirectMessageModel::getUnreadCount($userId);
$totalGroupUnread = SupportGroupModel::getUnreadGroupMessageCount($userId);

$ajaxAction = Request::get('ajax');
if ($ajaxAction) {
    header('Content-Type: application/json');
    
    switch ($ajaxAction) {
        case 'update_profile':
            $data = [
                'display_name' => Request::post('display_name') ?? '',
                'bio' => Request::post('bio') ?? '',
            ];
            $result = UserProfileModel::updateProfile($userId, $data);
            echo json_encode(['success' => $result]);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
    }
    exit;
}

$pageStyle = ['user/dashboard', 'user/community', 'user/profile','user/chat'];
require_once __DIR__ . '/profile.layout.php';
