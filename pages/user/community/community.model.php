<?php

class CommunityModel
{
    public static function getPosts(int $userId, array $params = []): array
    {
        $q = trim((string)($params['q'] ?? ''));
        $scope = trim((string)($params['scope'] ?? 'all')); // all|mine|trending|saved

        $where = "WHERE p.is_active = 1";
        if ($q !== '') {
            $sq = addslashes($q);
            $where .= " AND (p.title LIKE '%$sq%' OR p.content LIKE '%$sq%')";
        }
        if ($scope === 'mine') {
            $where .= " AND p.user_id = $userId";
        } elseif ($scope === 'saved') {
            $where .= " AND EXISTS (SELECT 1 FROM saved_posts sp WHERE sp.post_id = p.post_id AND sp.user_id = $userId)";
        }

        $blockedUsers = DirectMessageModel::getBlockedUsers($userId);
        if (!empty($blockedUsers)) {
            $blockedIds = implode(',', $blockedUsers);
            $where .= " AND p.user_id NOT IN ($blockedIds)";
        }

        $order = $scope === 'trending'
            ? "ORDER BY p.likes_count DESC, p.comments_count DESC, p.created_at DESC"
            : "ORDER BY p.created_at DESC";

        $sql = "
            SELECT
                p.post_id,
                p.user_id,
                p.title,
                p.content,
                p.image_url,
                p.post_type,
                p.is_anonymous,
                p.likes_count,
                p.comments_count,
                p.shares_count,
                p.created_at,
                COALESCE(u.display_name, CONCAT(u.first_name, ' ', u.last_name), u.username, 'User') AS display_name,
                u.username,
                u.profile_picture,
                (SELECT COUNT(1) FROM saved_posts sp WHERE sp.post_id = p.post_id AND sp.user_id = $userId) AS is_saved
            FROM community_posts p
            JOIN users u ON u.user_id = p.user_id
            $where
            $order
            LIMIT 100
        ";

        $rs = Database::search($sql);
        $posts = [];
        while ($row = $rs->fetch_assoc()) {
            $isAnonymous = (bool)$row['is_anonymous'];

            $postUserId = (int)$row['user_id'];
            $isFollowing = DirectMessageModel::isConnected($userId, $postUserId);

            // Per PRD §2.5: anonymous posts must not reveal the author's identity
            $displayName = $isAnonymous ? 'Anonymous' : ($row['display_name'] ?? 'User');
            $username    = $isAnonymous ? 'anonymous' : ($row['username'] ?? 'user');
            $profilePic  = $isAnonymous ? '' : ($row['profile_picture'] ?? '');

            $posts[] = [
                'postId' => (int)$row['post_id'],
                'userId' => $postUserId,
                'title' => $row['title'] ?? '',
                'content' => $row['content'] ?? '',
                'imageUrl' => $row['image_url'] ?? '',
                'postType' => $row['post_type'] ?? 'general',
                'anonymous' => $isAnonymous,
                'likesCount' => (int)($row['likes_count'] ?? 0),
                'commentsCount' => (int)($row['comments_count'] ?? 0),
                'sharesCount' => (int)($row['shares_count'] ?? 0),
                'createdAt' => $row['created_at'],
                'displayName' => $displayName,
                'username' => $username,
                'profilePictureUrl' => $profilePic,
                'active' => false,
                'isFollowing' => $isFollowing,
                'isSaved' => (bool)($row['is_saved'] ?? false),
            ];
        }

        return $posts;
    }

    public static function createPost(int $userId, array $data): bool
    {
        $title = addslashes(trim((string)($data['title'] ?? '')));
        $content = addslashes(trim((string)($data['content'] ?? '')));
        $postType = addslashes(trim((string)($data['postType'] ?? 'general')));
        $isAnonymous = !empty($data['privacy']) && $data['privacy'] === 'anonymous' ? 1 : 0;
        $imageUrl = addslashes((string)($data['imageUrl'] ?? ''));

        if ($content === '') return false;

        Database::iud(
            "INSERT INTO community_posts
                (user_id, title, content, image_url, post_type, is_anonymous, is_active, likes_count, comments_count, shares_count, created_at, updated_at)
             VALUES
                ($userId, " . ($title !== '' ? "'$title'" : "NULL") . ", '$content', " . ($imageUrl !== '' ? "'$imageUrl'" : "NULL") . ", '$postType', $isAnonymous, 1, 0, 0, 0, NOW(), NOW())"
        );

        return true;
    }

    public static function updatePost(int $userId, int $postId, array $data): bool
    {
        if ($postId <= 0) return false;

        $title = addslashes(trim((string)($data['title'] ?? '')));
        $content = addslashes(trim((string)($data['content'] ?? '')));
        $postType = addslashes(trim((string)($data['postType'] ?? 'general')));
        $isAnonymous = !empty($data['privacy']) && $data['privacy'] === 'anonymous' ? 1 : 0;
        $imageUrl = isset($data['imageUrl']) ? addslashes((string)$data['imageUrl']) : null;

        if ($content === '') return false;

        $setImage = '';
        if ($imageUrl !== null) {
            $setImage = ", image_url = " . ($imageUrl !== '' ? "'$imageUrl'" : "NULL");
        }

        Database::iud(
            "UPDATE community_posts
             SET title = " . ($title !== '' ? "'$title'" : "NULL") . ",
                 content = '$content',
                 post_type = '$postType',
                 is_anonymous = $isAnonymous,
                 updated_at = NOW()
                 $setImage
             WHERE post_id = $postId
               AND user_id = $userId"
        );

        return true;
    }

    public static function deletePost(int $userId, int $postId): bool
    {
        if ($postId <= 0) return false;
        Database::iud("UPDATE community_posts SET is_active = 0, updated_at = NOW() WHERE post_id = $postId AND user_id = $userId");
        return true;
    }

    /**
     * Toggle a like for a user on a post.
     * Uses the post_likes table to prevent duplicate likes (unique index on post_id, user_id).
     * Keeps likes_count on community_posts in sync.
     * Returns ['liked' => bool] — true if the like was added, false if it was removed.
     */
    public static function toggleLike(int $postId, int $userId): array
    {
        if ($postId <= 0 || $userId <= 0) {
            return ['liked' => false];
        }

        // Check if user already liked this post
        $existing = Database::search(
            "SELECT like_id FROM post_likes WHERE post_id = $postId AND user_id = $userId LIMIT 1"
        );

        if ($existing && $existing->num_rows > 0) {
            // Already liked — remove the like (toggle off)
            Database::iud("DELETE FROM post_likes WHERE post_id = $postId AND user_id = $userId");
            Database::iud(
                "UPDATE community_posts
                 SET likes_count = GREATEST(0, likes_count - 1), updated_at = NOW()
                 WHERE post_id = $postId AND is_active = 1"
            );
            
            return ['liked' => false];
        } else {
            // Not yet liked — add the like (toggle on)
            Database::iud(
                "INSERT IGNORE INTO post_likes (post_id, user_id, created_at)
                 VALUES ($postId, $userId, NOW())"
            );
            Database::iud(
                "UPDATE community_posts
                 SET likes_count = likes_count + 1, updated_at = NOW()
                 WHERE post_id = $postId AND is_active = 1"
            );
            return ['liked' => true];
        }
    }

    // ------------------------------------------------------------------
    // Comments
    // ------------------------------------------------------------------

    public static function getComments(int $postId): array
    {
        if ($postId <= 0) return [];

        $rs = Database::search("
            SELECT c.comment_id, c.user_id, c.content, c.is_anonymous, c.created_at,
                   COALESCE(u.display_name, CONCAT(u.first_name,' ',u.last_name), u.username, 'User') AS display_name,
                   u.profile_picture
            FROM post_comments c
            JOIN users u ON u.user_id = c.user_id
            WHERE c.post_id = $postId
              AND c.is_active = 1
              AND c.parent_comment_id IS NULL
            ORDER BY c.created_at ASC
            LIMIT 50
        ");

        $comments = [];
        if (!$rs) return [];
        while ($row = $rs->fetch_assoc()) {
            $isAnon = (bool)$row['is_anonymous'];
            $comments[] = [
                'commentId'      => (int)$row['comment_id'],
                'userId'         => (int)$row['user_id'],
                'content'        => $row['content'],
                'anonymous'      => $isAnon,
                'displayName'    => $isAnon ? 'Anonymous' : ($row['display_name'] ?? 'User'),
                'profilePicture' => $isAnon ? '' : ($row['profile_picture'] ?? ''),
                'createdAt'      => $row['created_at'],
            ];
        }
        return $comments;
    }

    public static function addComment(int $userId, int $postId, string $content): ?array
    {
        if ($userId <= 0 || $postId <= 0 || trim($content) === '') return null;

        $safe = addslashes(trim($content));
        Database::iud(
            "INSERT INTO post_comments (post_id, user_id, content, is_anonymous, is_active, likes_count, created_at, updated_at)
             VALUES ($postId, $userId, '$safe', 0, 1, 0, NOW(), NOW())"
        );
        Database::iud(
            "UPDATE community_posts
             SET comments_count = comments_count + 1, updated_at = NOW()
             WHERE post_id = $postId AND is_active = 1"
        );

        $rs = Database::search("
            SELECT c.comment_id, c.user_id, c.content, c.is_anonymous, c.created_at,
                   COALESCE(u.display_name, CONCAT(u.first_name,' ',u.last_name), u.username, 'User') AS display_name,
                   u.profile_picture
            FROM post_comments c
            JOIN users u ON u.user_id = c.user_id
            WHERE c.user_id = $userId AND c.post_id = $postId AND c.is_active = 1
            ORDER BY c.comment_id DESC LIMIT 1
        ");
        $row = $rs ? $rs->fetch_assoc() : null;
        if (!$row) return null;

        return [
            'commentId'      => (int)$row['comment_id'],
            'userId'         => (int)$row['user_id'],
            'content'        => $row['content'],
            'anonymous'      => false,
            'displayName'    => $row['display_name'] ?? 'User',
            'profilePicture' => $row['profile_picture'] ?? '',
            'createdAt'      => $row['created_at'],
        ];
    }

    // ------------------------------------------------------------------
    // Save
    // ------------------------------------------------------------------

    public static function toggleSave(int $userId, int $postId): array
    {
        if ($userId <= 0 || $postId <= 0) return ['saved' => false];

        $existing = Database::search(
            "SELECT saved_id FROM saved_posts WHERE user_id = $userId AND post_id = $postId LIMIT 1"
        );
        if ($existing && $existing->num_rows > 0) {
            Database::iud("DELETE FROM saved_posts WHERE user_id = $userId AND post_id = $postId");
            return ['saved' => false];
        }

        Database::iud(
            "INSERT IGNORE INTO saved_posts (user_id, post_id, created_at) VALUES ($userId, $postId, NOW())"
        );
        return ['saved' => true];
    }

    // ------------------------------------------------------------------
    // Report
    // ------------------------------------------------------------------

    public static function reportPost(int $userId, int $postId, string $reason, string $description): bool
    {
        if ($userId <= 0 || $postId <= 0 || trim($reason) === '') return false;

        // Idempotent: one pending report per user per post is enough
        $existing = Database::search(
            "SELECT report_id FROM post_reports
             WHERE reporter_id = $userId AND post_id = $postId AND status = 'pending'
             LIMIT 1"
        );
        if ($existing && $existing->num_rows > 0) return true;

        $safeReason = addslashes(trim($reason));
        $safeDesc   = addslashes(trim($description));
        $descSql    = $safeDesc !== '' ? "'$safeDesc'" : 'NULL';

        Database::iud(
            "INSERT INTO post_reports (post_id, reporter_id, reason, description, status, created_at)
             VALUES ($postId, $userId, '$safeReason', $descSql, 'pending', NOW())"
        );
        return true;
    }

    // ------------------------------------------------------------------
    // Share (counter only — no persistent per-user record in schema)
    // ------------------------------------------------------------------

    public static function incrementShareCount(int $postId): void
    {
        if ($postId <= 0) return;
        Database::iud(
            "UPDATE community_posts
             SET shares_count = shares_count + 1, updated_at = NOW()
             WHERE post_id = $postId AND is_active = 1"
        );
    }

    public static function handleUpload(array $file): ?string
    {
        if (empty($file) || !isset($file['tmp_name']) || (int)($file['error'] ?? 1) !== 0) {
            return null;
        }

        if ((int)$file['size'] <= 0) return null;
        $mime = mime_content_type($file['tmp_name']) ?: '';
        if (strpos($mime, 'image/') !== 0) return null;

        $ext = pathinfo((string)$file['name'], PATHINFO_EXTENSION);
        if ($ext === '') $ext = 'jpg';
        $safeExt = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($ext));
        if ($safeExt === '') $safeExt = 'jpg';

        $filename = bin2hex(random_bytes(16)) . '.' . $safeExt;
        $targetDir = ROOT . '/public/uploads/posts';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }

        $target = $targetDir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return null;
        }

        return '/uploads/posts/' . $filename;
    }
}
class DirectMessageModel
{
    public static function getUserConnections(int $userId): array
    {
        $sql = "
            SELECT 
                c.connection_id,
                c.status,
                c.created_at,
                c.updated_at,
                u.user_id,
                COALESCE(u.display_name, CONCAT(u.first_name, ' ', u.last_name), u.username) AS display_name,
                u.username,
                u.profile_picture,
                CASE 
                    WHEN c.user_id = $userId THEN c.connected_user_id 
                    ELSE c.user_id 
                END AS other_user_id
            FROM user_connections c
            JOIN users u ON (
                u.user_id = CASE 
                    WHEN c.user_id = $userId THEN c.connected_user_id 
                    ELSE c.user_id 
                END
            )
            WHERE (c.user_id = $userId OR c.connected_user_id = $userId)
            AND c.status = 'accepted'
            ORDER BY c.updated_at DESC
        ";
        
        $rs = Database::search($sql);
        $connections = [];
        while ($row = $rs->fetch_assoc()) {
            $connections[] = [
                'connectionId' => (int)$row['connection_id'],
                'otherUserId' => (int)$row['other_user_id'],
                'displayName' => $row['display_name'] ?? 'User',
                'username' => $row['username'] ?? 'user',
                'profilePicture' => $row['profile_picture'] ?? '',
                'status' => $row['status'],
            ];
        }
        return $connections;
    }

    public static function getPendingConnectionRequests(int $userId): array
    {
        $sql = "
            SELECT 
                c.connection_id,
                c.created_at,
                u.user_id,
                COALESCE(u.display_name, CONCAT(u.first_name, ' ', u.last_name), u.username) AS display_name,
                u.username,
                u.profile_picture
            FROM user_connections c
            JOIN users u ON u.user_id = c.user_id
            WHERE c.connected_user_id = $userId
            AND c.status = 'pending'
            ORDER BY c.created_at DESC
        ";
        
        $rs = Database::search($sql);
        $requests = [];
        while ($row = $rs->fetch_assoc()) {
            $requests[] = [
                'connectionId' => (int)$row['connection_id'],
                'userId' => (int)$row['user_id'],
                'displayName' => $row['display_name'] ?? 'User',
                'username' => $row['username'] ?? 'user',
                'profilePicture' => $row['profile_picture'] ?? '',
                'createdAt' => $row['created_at'],
            ];
        }
        return $requests;
    }

    public static function sendConnectionRequest(int $userId, int $targetUserId): bool
    {
        if ($userId === $targetUserId) return false;
        
        $check = Database::search(
            "SELECT connection_id FROM user_connections 
             WHERE (user_id = $userId AND connected_user_id = $targetUserId)
             OR (user_id = $targetUserId AND connected_user_id = $userId)
             LIMIT 1"
        );
        
        if ($check && $check->num_rows > 0) {
            return false;
        }
        
        Database::iud(
            "INSERT INTO user_connections (user_id, connected_user_id, status, created_at)
             VALUES ($userId, $targetUserId, 'pending', NOW())"
        );
        
        return true;
    }

    public static function acceptConnection(int $userId, int $connectionId): bool
    {
        $check = Database::search(
            "SELECT user_id, connected_user_id FROM user_connections 
             WHERE connection_id = $connectionId 
             AND connected_user_id = $userId 
             AND status = 'pending'
             LIMIT 1"
        );
        
        if (!$check || $check->num_rows === 0) {
            return false;
        }
        
        $row = $check->fetch_assoc();
        
        Database::iud(
            "UPDATE user_connections SET status = 'accepted', updated_at = NOW() 
             WHERE connection_id = $connectionId"
        );
        
        $user1Id = min((int)$row['user_id'], (int)$row['connected_user_id']);
        $user2Id = max((int)$row['user_id'], (int)$row['connected_user_id']);
        
        $checkConv = Database::search(
            "SELECT conversation_id FROM dm_conversations 
             WHERE user1_id = $user1Id AND user2_id = $user2Id LIMIT 1"
        );
        
        if (!$checkConv || $checkConv->num_rows === 0) {
            Database::iud(
                "INSERT INTO dm_conversations (user1_id, user2_id, created_at)
                 VALUES ($user1Id, $user2Id, NOW())"
            );
        }
        
        return true;
    }

    public static function declineConnection(int $userId, int $connectionId): bool
    {
        $check = Database::search(
            "SELECT connection_id FROM user_connections 
             WHERE connection_id = $connectionId 
             AND connected_user_id = $userId 
             AND status = 'pending'
             LIMIT 1"
        );
        
        if (!$check || $check->num_rows === 0) {
            return false;
        }
        
        Database::iud(
            "UPDATE user_connections SET status = 'declined', updated_at = NOW() 
             WHERE connection_id = $connectionId"
        );
        
        return true;
    }

    public static function removeConnection(int $userId, int $connectionId): bool
    {
        $check = Database::search(
            "SELECT user_id, connected_user_id FROM user_connections 
             WHERE connection_id = $connectionId 
             AND (user_id = $userId OR connected_user_id = $userId)
             LIMIT 1"
        );
        
        if (!$check || $check->num_rows === 0) {
            return false;
        }
        
        $row = $check->fetch_assoc();
        $otherUserId = (int)$row['user_id'] === $userId 
            ? (int)$row['connected_user_id'] 
            : (int)$row['user_id'];
        
        Database::iud("DELETE FROM user_connections WHERE connection_id = $connectionId");
        
        $user1Id = min($userId, $otherUserId);
        $user2Id = max($userId, $otherUserId);
        Database::iud(
            "DELETE FROM dm_conversations WHERE user1_id = $user1Id AND user2_id = $user2Id"
        );
        
        return true;
    }

    public static function getConversations(int $userId): array
    {
        $sql = "
            SELECT 
                c.conversation_id,
                c.last_message_at,
                c.last_message_preview,
                u.user_id,
                COALESCE(u.display_name, CONCAT(u.first_name, ' ', u.last_name), u.username) AS display_name,
                u.username,
                u.profile_picture,
                (SELECT COUNT(*) FROM direct_messages dm 
                 WHERE dm.conversation_id = c.conversation_id 
                 AND dm.sender_id != $userId 
                 AND dm.is_read = 0) AS unread_count
            FROM dm_conversations c
            JOIN users u ON (
                u.user_id = CASE 
                    WHEN c.user1_id = $userId THEN c.user2_id 
                    ELSE c.user1_id 
                END
            )
            WHERE c.user1_id = $userId OR c.user2_id = $userId
            ORDER BY COALESCE(c.last_message_at, c.created_at) DESC
        ";
        
        $rs = Database::search($sql);
        $conversations = [];
        while ($row = $rs->fetch_assoc()) {
            $conversations[] = [
                'conversationId' => (int)$row['conversation_id'],
                'userId' => (int)$row['user_id'],
                'displayName' => $row['display_name'] ?? 'User',
                'username' => $row['username'] ?? 'user',
                'profilePicture' => $row['profile_picture'] ?? '',
                'lastMessageAt' => $row['last_message_at'],
                'lastMessagePreview' => $row['last_message_preview'] ?? '',
                'unreadCount' => (int)$row['unread_count'],
            ];
        }
        return $conversations;
    }

    public static function getConversationMessages(int $userId, int $conversationId, int $limit = 50, int $afterId = 0): array
    {
        $check = Database::search(
            "SELECT conversation_id FROM dm_conversations
             WHERE conversation_id = $conversationId
             AND (user1_id = $userId OR user2_id = $userId)
             LIMIT 1"
        );

        if (!$check || $check->num_rows === 0) {
            return [];
        }

        Database::iud(
            "UPDATE direct_messages SET is_read = 1
             WHERE conversation_id = $conversationId
             AND sender_id != $userId
             AND is_read = 0"
        );

        $afterFilter = $afterId > 0 ? "AND m.message_id > $afterId" : '';

        $sql = "
            SELECT
                m.message_id,
                m.sender_id,
                m.content,
                m.is_read,
                m.created_at,
                COALESCE(u.display_name, CONCAT(u.first_name, ' ', u.last_name), u.username) AS sender_name,
                u.profile_picture
            FROM direct_messages m
            JOIN users u ON u.user_id = m.sender_id
            WHERE m.conversation_id = $conversationId
            $afterFilter
            ORDER BY m.created_at ASC
            LIMIT $limit
        ";
        
        $rs = Database::search($sql);
        $messages = [];
        while ($row = $rs->fetch_assoc()) {
            $messages[] = [
                'messageId' => (int)$row['message_id'],
                'senderId' => (int)$row['sender_id'],
                'content' => $row['content'],
                'isRead' => (bool)$row['is_read'],
                'createdAt' => $row['created_at'],
                'senderName' => $row['sender_name'] ?? 'User',
                'profilePicture' => $row['profile_picture'] ?? '',
                'isOwnMessage' => (int)$row['sender_id'] === $userId,
            ];
        }
        return $messages;
    }

    public static function sendMessage(int $userId, int $conversationId, string $content): ?array
    {
        $content = trim($content);
        if ($content === '') return null;
        
        $check = Database::search(
            "SELECT user1_id, user2_id FROM dm_conversations 
             WHERE conversation_id = $conversationId 
             AND (user1_id = $userId OR user2_id = $userId)
             LIMIT 1"
        );
        
        if (!$check || $check->num_rows === 0) {
            return null;
        }
        
        $safeContent = addslashes($content);
        Database::iud(
            "INSERT INTO direct_messages (conversation_id, sender_id, content, created_at)
             VALUES ($conversationId, $userId, '$safeContent', NOW())"
        );
        
        $messageId = Database::$connection->insert_id;
        
        $preview = strlen($content) > 50 ? substr($content, 0, 50) . '...' : $content;
        Database::iud(
            "UPDATE dm_conversations 
             SET last_message_at = NOW(), 
                 last_message_preview = '" . addslashes($preview) . "'
             WHERE conversation_id = $conversationId"
        );
        
        return [
            'messageId' => (int)$messageId,
            'conversationId' => $conversationId,
            'senderId' => $userId,
            'content' => $content,
            'createdAt' => date('Y-m-d H:i:s'),
        ];
    }

    public static function getOrCreateConversation(int $userId, int $otherUserId): ?int
    {
        if ($userId === $otherUserId) return null;
        
        $user1Id = min($userId, $otherUserId);
        $user2Id = max($userId, $otherUserId);
        
        $check = Database::search(
            "SELECT conversation_id FROM dm_conversations 
             WHERE user1_id = $user1Id AND user2_id = $user2Id
             LIMIT 1"
        );
        
        if ($check && $check->num_rows > 0) {
            $row = $check->fetch_assoc();
            return (int)$row['conversation_id'];
        }
        
        Database::iud(
            "INSERT INTO dm_conversations (user1_id, user2_id, created_at)
             VALUES ($user1Id, $user2Id, NOW())"
        );
        
        return (int)Database::$connection->insert_id;
    }

    public static function isConnected(int $userId, int $otherUserId): bool
    {
        $sql = "
            SELECT connection_id FROM user_connections 
            WHERE ((user_id = $userId AND connected_user_id = $otherUserId)
            OR (user_id = $otherUserId AND connected_user_id = $userId))
            AND status = 'accepted'
            LIMIT 1
        ";
        
        $check = Database::search($sql);
        return $check && $check->num_rows > 0;
    }

    public static function getConnectionStatus(int $userId, int $otherUserId): ?string
    {
        $sql = "
            SELECT status FROM user_connections 
            WHERE (user_id = $userId AND connected_user_id = $otherUserId)
            OR (user_id = $otherUserId AND connected_user_id = $userId)
            LIMIT 1
        ";
        
        $check = Database::search($sql);
        if ($check && $check->num_rows > 0) {
            $row = $check->fetch_assoc();
            return $row['status'];
        }
        return null;
    }

    public static function getUnreadCount(int $userId): int
    {
        $sql = "
            SELECT COUNT(*) as cnt FROM direct_messages dm
            JOIN dm_conversations dc ON dc.conversation_id = dm.conversation_id
            WHERE (dc.user1_id = $userId OR dc.user2_id = $userId)
            AND dm.sender_id != $userId
            AND dm.is_read = 0
        ";
        
        $check = Database::search($sql);
        if ($check && $row = $check->fetch_assoc()) {
            return (int)$row['cnt'];
        }
        return 0;
    }

    public static function getBlockedUsers(int $userId): array
    {
        $sql = "
            SELECT user_id FROM user_connections 
            WHERE connected_user_id = $userId AND status = 'blocked'
            UNION
            SELECT connected_user_id FROM user_connections 
            WHERE user_id = $userId AND status = 'blocked'
        ";
        
        $rs = Database::search($sql);
        $blocked = [];
        while ($row = $rs->fetch_assoc()) {
            $blocked[] = (int)$row['user_id'];
        }
        return $blocked;
    }

    public static function followUser(int $userId, int $targetUserId): bool
    {
        if ($userId === $targetUserId) return false;
        
        $existing = Database::search(
            "SELECT connection_id, status FROM user_connections 
             WHERE (user_id = $userId AND connected_user_id = $targetUserId)
             OR (user_id = $targetUserId AND connected_user_id = $userId)
             LIMIT 1"
        );
        
        if ($existing && $existing->num_rows > 0) {
            $row = $existing->fetch_assoc();
            if ($row['status'] === 'blocked') {
                return false;
            }
            if ($row['status'] === 'accepted') {
                return true;
            }
            Database::iud(
                "UPDATE user_connections SET status = 'accepted', updated_at = NOW() 
                 WHERE connection_id = " . (int)$row['connection_id']
            );
        } else {
            Database::iud(
                "INSERT INTO user_connections (user_id, connected_user_id, status, created_at, updated_at)
                 VALUES ($userId, $targetUserId, 'accepted', NOW(), NOW())"
            );
        }
        
        $user1Id = min($userId, $targetUserId);
        $user2Id = max($userId, $targetUserId);
        $checkConv = Database::search(
            "SELECT conversation_id FROM dm_conversations 
             WHERE user1_id = $user1Id AND user2_id = $user2Id LIMIT 1"
        );
        if (!$checkConv || $checkConv->num_rows === 0) {
            Database::iud(
                "INSERT INTO dm_conversations (user1_id, user2_id, created_at)
                 VALUES ($user1Id, $user2Id, NOW())"
            );
        }
        
        return true;
    }

    public static function unfollowUser(int $userId, int $targetUserId): bool
    {
        Database::iud(
            "DELETE FROM user_connections 
             WHERE user_id = $userId AND connected_user_id = $targetUserId AND status = 'accepted'"
        );
        
        $user1Id = min($userId, $targetUserId);
        $user2Id = max($userId, $targetUserId);
        Database::iud(
            "DELETE FROM dm_conversations WHERE user1_id = $user1Id AND user2_id = $user2Id"
        );
        
        return true;
    }

    public static function blockUser(int $userId, int $targetUserId): bool
    {
        $existing = Database::search(
            "SELECT connection_id FROM user_connections 
             WHERE user_id = $userId AND connected_user_id = $targetUserId
             LIMIT 1"
        );
        
        if ($existing && $existing->num_rows > 0) {
            Database::iud(
                "UPDATE user_connections SET status = 'blocked', updated_at = NOW() 
                 WHERE user_id = $userId AND connected_user_id = $targetUserId"
            );
        } else {
            Database::iud(
                "INSERT INTO user_connections (user_id, connected_user_id, status, created_at, updated_at)
                 VALUES ($userId, $targetUserId, 'blocked', NOW(), NOW())"
            );
        }
        
        Database::iud(
            "DELETE FROM user_connections 
             WHERE user_id = $targetUserId AND connected_user_id = $userId"
        );
        
        return true;
    }

    public static function unblockUser(int $userId, int $targetUserId): bool
    {
        Database::iud(
            "DELETE FROM user_connections 
             WHERE user_id = $userId AND connected_user_id = $targetUserId AND status = 'blocked'"
        );
        return true;
    }
}


class SupportGroupModel
{
    public static function getUserGroups(int $userId): array
    {
        $sql = "
            SELECT 
                sg.group_id,
                sg.name,
                sg.description,
                sg.category,
                sg.meeting_schedule,
                sg.meeting_link,
                sg.max_members,
                sgm.role,
                sgm.joined_at,
                (SELECT COUNT(*) FROM support_group_members sgm2 
                 WHERE sgm2.group_id = sg.group_id) AS member_count,
                 (SELECT COUNT(*) FROM support_group_messages sgm3 
                  WHERE sgm3.group_id = sg.group_id 
                  AND sgm3.created_at > COALESCE(
                      (SELECT MAX(created_at) FROM support_group_messages 
                       WHERE group_id = sg.group_id 
                       AND user_id = $userId), '1970-01-01'
                  )
                  AND sgm3.user_id != $userId) AS unread_count
            FROM support_groups sg
            JOIN support_group_members sgm ON sgm.group_id = sg.group_id
            WHERE sgm.user_id = $userId
            AND sg.is_active = 1
            ORDER BY sg.name ASC
        ";
        
        $rs = Database::search($sql);
        $groups = [];
        while ($row = $rs->fetch_assoc()) {
            $groups[] = [
                'groupId' => (int)$row['group_id'],
                'name' => $row['name'],
                'description' => $row['description'] ?? '',
                'category' => $row['category'] ?? '',
                'meetingSchedule' => $row['meeting_schedule'] ?? '',
                'meetingLink' => $row['meeting_link'] ?? '',
                'maxMembers' => $row['max_members'] ? (int)$row['max_members'] : null,
                'role' => $row['role'],
                'joinedAt' => $row['joined_at'],
                'memberCount' => (int)$row['member_count'],
                'unreadCount' => (int)$row['unread_count'],
            ];
        }
        return $groups;
    }

    public static function getAvailableGroups(int $userId): array
    {
        $sql = "
            SELECT 
                sg.group_id,
                sg.name,
                sg.description,
                sg.category,
                sg.meeting_schedule,
                sg.max_members,
                (SELECT COUNT(*) FROM support_group_members sgm2 
                 WHERE sgm2.group_id = sg.group_id) AS member_count,
                CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM support_group_members sgm3 
                        WHERE sgm3.group_id = sg.group_id 
                        AND sgm3.user_id = $userId
                    ) THEN 1 
                    ELSE 0 
                END AS is_member
            FROM support_groups sg
            WHERE sg.is_active = 1
            ORDER BY sg.category ASC, sg.name ASC
        ";
        
        $rs = Database::search($sql);
        $groups = [];
        while ($row = $rs->fetch_assoc()) {
            $isFull = $row['max_members'] && (int)$row['member_count'] >= (int)$row['max_members'];
            $groups[] = [
                'groupId' => (int)$row['group_id'],
                'name' => $row['name'],
                'description' => $row['description'] ?? '',
                'category' => $row['category'] ?? '',
                'meetingSchedule' => $row['meeting_schedule'] ?? '',
                'maxMembers' => $row['max_members'] ? (int)$row['max_members'] : null,
                'memberCount' => (int)$row['member_count'],
                'isMember' => (bool)$row['is_member'],
                'isFull' => $isFull,
            ];
        }
        return $groups;
    }

    public static function joinGroup(int $userId, int $groupId): bool
    {
        $checkGroup = Database::search(
            "SELECT group_id, max_members FROM support_groups 
             WHERE group_id = $groupId AND is_active = 1
             LIMIT 1"
        );
        
        if (!$checkGroup || $checkGroup->num_rows === 0) {
            return false;
        }
        
        $group = $checkGroup->fetch_assoc();
        
        if ($group['max_members']) {
            $memberCount = Database::search(
                "SELECT COUNT(*) as cnt FROM support_group_members WHERE group_id = $groupId"
            );
            $countRow = $memberCount->fetch_assoc();
            if ((int)$countRow['cnt'] >= (int)$group['max_members']) {
                return false;
            }
        }
        
        $checkMember = Database::search(
            "SELECT membership_id FROM support_group_members 
             WHERE group_id = $groupId AND user_id = $userId
             LIMIT 1"
        );
        
        if ($checkMember && $checkMember->num_rows > 0) {
            return true;
        }
        
        Database::iud(
            "INSERT INTO support_group_members (group_id, user_id, role, joined_at)
             VALUES ($groupId, $userId, 'member', NOW())"
        );
        
        return true;
    }

    public static function leaveGroup(int $userId, int $groupId): bool
    {
        $check = Database::search(
            "SELECT role FROM support_group_members 
             WHERE group_id = $groupId AND user_id = $userId
             AND role != 'leader'
             LIMIT 1"
        );
        
        if (!$check || $check->num_rows === 0) {
            return false;
        }
        
        Database::iud(
            "DELETE FROM support_group_members 
             WHERE group_id = $groupId AND user_id = $userId"
        );
        
        return true;
    }

    public static function getGroupMessages(int $groupId, int $userId, int $limit = 50, int $afterId = 0): array
    {
        $check = Database::search(
            "SELECT membership_id FROM support_group_members
             WHERE group_id = $groupId AND user_id = $userId
             LIMIT 1"
        );

        if (!$check || $check->num_rows === 0) {
            return [];
        }

        $afterFilter = $afterId > 0 ? "AND m.message_id > $afterId" : '';

        $sql = "
            SELECT
                m.message_id,
                m.user_id,
                m.content,
                m.is_pinned,
                m.is_deleted,
                m.created_at,
                COALESCE(u.display_name, CONCAT(u.first_name, ' ', u.last_name), u.username) AS sender_name,
                u.username,
                u.profile_picture,
                sgm.role AS member_role
            FROM support_group_messages m
            JOIN users u ON u.user_id = m.user_id
            LEFT JOIN support_group_members sgm ON sgm.group_id = m.group_id AND sgm.user_id = m.user_id
            WHERE m.group_id = $groupId
            AND m.is_deleted = 0
            $afterFilter
            ORDER BY m.created_at ASC
            LIMIT $limit
        ";
        
        $rs = Database::search($sql);
        $messages = [];
        while ($row = $rs->fetch_assoc()) {
            $messages[] = [
                'messageId' => (int)$row['message_id'],
                'userId' => (int)$row['user_id'],
                'content' => $row['content'],
                'isPinned' => (bool)$row['is_pinned'],
                'createdAt' => $row['created_at'],
                'senderName' => $row['sender_name'] ?? 'User',
                'username' => $row['username'] ?? 'user',
                'profilePicture' => $row['profile_picture'] ?? '',
                'memberRole' => $row['member_role'] ?? 'member',
                'isOwnMessage' => (int)$row['user_id'] === $userId,
            ];
        }
        return $messages;
    }

    public static function sendMessage(int $userId, int $groupId, string $content): ?array
    {
        $content = trim($content);
        if ($content === '') return null;
        
        $check = Database::search(
            "SELECT membership_id FROM support_group_members 
             WHERE group_id = $groupId AND user_id = $userId
             LIMIT 1"
        );
        
        if (!$check || $check->num_rows === 0) {
            return null;
        }
        
        $safeContent = addslashes($content);
        Database::iud(
            "INSERT INTO support_group_messages (group_id, user_id, content, created_at)
             VALUES ($groupId, $userId, '$safeContent', NOW())"
        );
        
        $messageId = Database::$connection->insert_id;
        
        return [
            'messageId' => (int)$messageId,
            'groupId' => $groupId,
            'userId' => $userId,
            'content' => $content,
            'createdAt' => date('Y-m-d H:i:s'),
        ];
    }

    public static function getGroupDetails(int $groupId, int $userId): ?array
    {
        $sql = "
            SELECT 
                sg.group_id,
                sg.name,
                sg.description,
                sg.category,
                sg.meeting_schedule,
                sg.meeting_link,
                sg.max_members,
                sg.is_active,
                sg.created_at,
                (SELECT COUNT(*) FROM support_group_members sgm2 
                 WHERE sgm2.group_id = sg.group_id) AS member_count,
                CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM support_group_members sgm3 
                        WHERE sgm3.group_id = sg.group_id 
                        AND sgm3.user_id = $userId
                    ) THEN 1 
                    ELSE 0 
                END AS is_member,
                (SELECT role FROM support_group_members 
                 WHERE group_id = sg.group_id AND user_id = $userId) AS user_role
            FROM support_groups sg
            WHERE sg.group_id = $groupId
        ";
        
        $rs = Database::search($sql);
        if ($rs && $row = $rs->fetch_assoc()) {
            return [
                'groupId' => (int)$row['group_id'],
                'name' => $row['name'],
                'description' => $row['description'] ?? '',
                'category' => $row['category'] ?? '',
                'meetingSchedule' => $row['meeting_schedule'] ?? '',
                'meetingLink' => $row['meeting_link'] ?? '',
                'maxMembers' => $row['max_members'] ? (int)$row['max_members'] : null,
                'isActive' => (bool)$row['is_active'],
                'createdAt' => $row['created_at'],
                'memberCount' => (int)$row['member_count'],
                'isMember' => (bool)$row['is_member'],
                'userRole' => $row['user_role'] ?? null,
            ];
        }
        return null;
    }

    public static function getGroupMembers(int $groupId, int $limit = 20): array
    {
        $sql = "
            SELECT 
                u.user_id,
                COALESCE(u.display_name, CONCAT(u.first_name, ' ', u.last_name), u.username) AS display_name,
                u.username,
                u.profile_picture,
                sgm.role,
                sgm.joined_at
            FROM support_group_members sgm
            JOIN users u ON u.user_id = sgm.user_id
            WHERE sgm.group_id = $groupId
            ORDER BY 
                CASE sgm.role 
                    WHEN 'leader' THEN 1 
                    WHEN 'moderator' THEN 2 
                    ELSE 3 
                END,
                sgm.joined_at ASC
            LIMIT $limit
        ";
        
        $rs = Database::search($sql);
        $members = [];
        while ($row = $rs->fetch_assoc()) {
            $members[] = [
                'userId' => (int)$row['user_id'],
                'displayName' => $row['display_name'] ?? 'User',
                'username' => $row['username'] ?? 'user',
                'profilePicture' => $row['profile_picture'] ?? '',
                'role' => $row['role'],
                'joinedAt' => $row['joined_at'],
            ];
        }
        return $members;
    }

    public static function getUnreadGroupMessageCount(int $userId): int
    {
        $sql = "
            SELECT SUM(unread.cnt) as total_unread
            FROM (
                SELECT 
                    sg.group_id,
                    (SELECT COUNT(*) FROM support_group_messages m
                     WHERE m.group_id = sg.group_id
                     AND m.user_id != $userId
                     AND m.created_at > COALESCE(
                         (SELECT MAX(created_at) FROM support_group_messages 
                          WHERE group_id = sg.group_id 
                          AND user_id = $userId), '1970-01-01'
                     )) AS cnt
                FROM support_groups sg
                JOIN support_group_members sgm ON sgm.group_id = sg.group_id
                WHERE sgm.user_id = $userId AND sg.is_active = 1
            ) unread
        ";
        
        $check = Database::search($sql);
        if ($check && $row = $check->fetch_assoc()) {
            return (int)($row['total_unread'] ?? 0);
        }
        return 0;
    }
}
