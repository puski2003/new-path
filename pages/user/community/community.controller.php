<?php


$userId = (int)$user['id'];

$searchQuery = Request::get('q') ?? '';
$tab = 'posts';

$scope = Request::get('scope') ?? 'all';
if (!in_array($scope, ['all', 'mine', 'trending', 'saved'], true)) {
    $scope = 'all';
}

$posts = CommunityModel::getPosts($userId, [
    'q' => $searchQuery,
    'scope' => $scope,
]);

$users = [];

$dmConversations = DirectMessageModel::getConversations($userId);
$pendingRequests = DirectMessageModel::getPendingConnectionRequests($userId);
$supportGroups = SupportGroupModel::getUserGroups($userId);

$totalDmUnread = DirectMessageModel::getUnreadCount($userId);
$totalGroupUnread = SupportGroupModel::getUnreadGroupMessageCount($userId);

$pageTitle = 'Community';
$pageStyle = ['user/dashboard', 'user/community', 'user/chat'];

$renderChatHtml = static function (array $messages, string $itemPartial, array $emptyState): string {
    ob_start();

    if (empty($messages)) {
        $emptyIcon = $emptyState['icon'] ?? 'message-circle';
        $emptyTitle = $emptyState['title'] ?? 'Nothing here yet';
        $emptyText = $emptyState['text'] ?? '';
        require __DIR__ . '/../common/user.chat-empty-state.php';
    } else {
        foreach ($messages as $message) {
            require $itemPartial;
        }
    }

    return (string)ob_get_clean();
};

$ajaxAction = Request::get('ajax');
if ($ajaxAction) {
    header('Content-Type: application/json');
    
    switch ($ajaxAction) {
        case 'get_dm_messages':
            $conversationId = (int)Request::get('conversation_id');
            $messages = DirectMessageModel::getConversationMessages($userId, $conversationId);
            $lastMsgId = !empty($messages) ? max(array_column($messages, 'messageId')) : 0;
            $html = $renderChatHtml(
                $messages,
                __DIR__ . '/../common/user.chat-dm-message-item.php',
                [
                    'icon' => 'message-circle',
                    'title' => 'No messages yet',
                    'text' => 'Start the conversation!',
                ]
            );
            echo json_encode(['success' => true, 'html' => $html, 'hasMessages' => !empty($messages), 'lastMsgId' => $lastMsgId]);
            exit;

        case 'poll_dm_messages':
            $conversationId = (int)Request::get('conversation_id');
            $afterId        = (int)Request::get('last_id');
            $messages = DirectMessageModel::getConversationMessages($userId, $conversationId, 50, $afterId);
            $lastMsgId = $afterId;
            $html = '';
            if (!empty($messages)) {
                $lastMsgId = max(array_column($messages, 'messageId'));
                $html = $renderChatHtml(
                    $messages,
                    __DIR__ . '/../common/user.chat-dm-message-item.php',
                    []
                );
            }
            echo json_encode(['success' => true, 'html' => $html, 'lastMsgId' => $lastMsgId]);
            exit;
            
        case 'send_dm_message':
            $conversationId = (int)Request::post('conversation_id');
            $content = trim((string)Request::post('content'));
            if ($conversationId > 0 && $content !== '') {
                $message = DirectMessageModel::sendMessage($userId, $conversationId, $content);
                if ($message) {
                    echo json_encode(['success' => true, 'message' => $message]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to send message']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid data']);
            }
            exit;
            
        case 'get_group_messages':
            $groupId = (int)Request::get('group_id');
            $messages = SupportGroupModel::getGroupMessages($groupId, $userId);
            $group = SupportGroupModel::getGroupDetails($groupId, $userId);
            $lastMsgId = !empty($messages) ? max(array_column($messages, 'messageId')) : 0;
            $html = $renderChatHtml(
                $messages,
                __DIR__ . '/../common/user.chat-group-message-item.php',
                [
                    'icon' => 'message-circle',
                    'title' => 'No messages yet',
                    'text' => 'Be the first to say hello!',
                ]
            );
            echo json_encode(['success' => true, 'html' => $html, 'hasMessages' => !empty($messages), 'group' => $group, 'lastMsgId' => $lastMsgId]);
            exit;

        case 'poll_group_messages':
            $groupId = (int)Request::get('group_id');
            $afterId = (int)Request::get('last_id');
            $messages = SupportGroupModel::getGroupMessages($groupId, $userId, 50, $afterId);
            $lastMsgId = $afterId;
            $html = '';
            if (!empty($messages)) {
                $lastMsgId = max(array_column($messages, 'messageId'));
                $html = $renderChatHtml(
                    $messages,
                    __DIR__ . '/../common/user.chat-group-message-item.php',
                    []
                );
            }
            echo json_encode(['success' => true, 'html' => $html, 'lastMsgId' => $lastMsgId]);
            exit;
            
        case 'send_group_message':
            $groupId = (int)Request::post('group_id');
            $content = trim((string)Request::post('content'));
            if ($groupId > 0 && $content !== '') {
                $message = SupportGroupModel::sendMessage($userId, $groupId, $content);
                if ($message) {
                    echo json_encode(['success' => true, 'message' => $message]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to send message']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid data']);
            }
            exit;
            
        case 'send_connection_request':
            $targetUserId = (int)Request::post('user_id');
            $result = DirectMessageModel::sendConnectionRequest($userId, $targetUserId);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'accept_connection':
            $connectionId = (int)Request::post('connection_id');
            $result = DirectMessageModel::acceptConnection($userId, $connectionId);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'decline_connection':
            $connectionId = (int)Request::post('connection_id');
            $result = DirectMessageModel::declineConnection($userId, $connectionId);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'join_group':
            $groupId = (int)Request::post('group_id');
            $result = SupportGroupModel::joinGroup($userId, $groupId);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'leave_group':
            $groupId = (int)Request::post('group_id');
            $result = SupportGroupModel::leaveGroup($userId, $groupId);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'get_available_groups':
            $groups = SupportGroupModel::getAvailableGroups($userId);
            echo json_encode(['success' => true, 'groups' => $groups]);
            exit;
            
        case 'get_connections':
            $connections = DirectMessageModel::getUserConnections($userId);
            echo json_encode(['success' => true, 'connections' => $connections]);
            exit;
            
        case 'start_conversation':
            $otherUserId = (int)Request::post('user_id');
            $conversationId = DirectMessageModel::getOrCreateConversation($userId, $otherUserId);
            echo json_encode(['success' => $conversationId !== null, 'conversation_id' => $conversationId]);
            exit;
            
        case 'follow':
            $targetUserId = (int)Request::post('user_id');
            $result = DirectMessageModel::followUser($userId, $targetUserId);
            echo json_encode(['success' => $result]);
            exit;

        case 'unfollow':
            $targetUserId = (int)Request::post('user_id');
            $result = DirectMessageModel::unfollowUser($userId, $targetUserId);
            echo json_encode(['success' => $result]);
            exit;

        case 'block':
            $targetUserId = (int)Request::post('user_id');
            $result = DirectMessageModel::blockUser($userId, $targetUserId);
            echo json_encode(['success' => $result]);
            exit;

        default:
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
            exit;
    }
}
