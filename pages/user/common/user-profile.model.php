<?php

class UserProfileModel
{
    private static function escape(string $value): string
    {
        Database::setUpConnection();
        return Database::$connection->real_escape_string($value);
    }

    public static function getProfile(int $userId, ?int $viewerId = null): ?array
    {
        $sql = "
            SELECT 
                u.user_id,
                u.username,
                u.first_name,
                u.last_name,
                u.display_name,
                u.profile_picture,
                u.bio,
                u.created_at,
                (SELECT COUNT(*) FROM user_connections 
                 WHERE connected_user_id = u.user_id AND status = 'accepted') AS followers_count,
                (SELECT COUNT(*) FROM user_connections 
                 WHERE user_id = u.user_id AND status = 'accepted') AS following_count
            FROM users u
            WHERE u.user_id = $userId
            AND u.is_active = 1
        ";
        
        $rs = Database::search($sql);
        if ($rs && $row = $rs->fetch_assoc()) {
            $isFollowing = false;
            
            if ($viewerId !== null && $viewerId !== $userId) {
                $status = DirectMessageModel::getConnectionStatus($viewerId, $userId);
                $isFollowing = $status === 'accepted';
            }
            
            $displayName = $row['display_name'] 
                ?? trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) 
                ?: 'User';

            $communityPostsCountRs = Database::search(
                "SELECT COUNT(*) AS total_posts
                 FROM community_posts
                 WHERE user_id = $userId
                   AND is_active = 1"
            );
            $communityPostsCount = $communityPostsCountRs ? (int)($communityPostsCountRs->fetch_assoc()['total_posts'] ?? 0) : 0;

            $communityPostsRs = Database::search(
                "SELECT post_id, title, content, image_url, likes_count, comments_count, shares_count, created_at
                 FROM community_posts
                 WHERE user_id = $userId
                   AND is_active = 1
                 ORDER BY created_at DESC
                 LIMIT 3"
            );

            $communityPosts = [];
            while ($communityPostsRs && ($postRow = $communityPostsRs->fetch_assoc())) {
                $communityPosts[] = [
                    'postId' => (int)$postRow['post_id'],
                    'title' => $postRow['title'] ?? '',
                    'content' => $postRow['content'] ?? '',
                    'imageUrl' => $postRow['image_url'] ?? '',
                    'likesCount' => (int)($postRow['likes_count'] ?? 0),
                    'commentsCount' => (int)($postRow['comments_count'] ?? 0),
                    'sharesCount' => (int)($postRow['shares_count'] ?? 0),
                    'createdAt' => $postRow['created_at'] ?? null,
                ];
            }
            
            return [
                'userId' => (int)$row['user_id'],
                'username' => $row['username'] ?? '',
                'firstName' => $row['first_name'] ?? '',
                'lastName' => $row['last_name'] ?? '',
                'displayName' => $displayName,
                'profilePicture' => $row['profile_picture'] ?? '',
                'bio' => $row['bio'] ?? '',
                'followersCount' => (int)$row['followers_count'],
                'followingCount' => (int)$row['following_count'],
                'createdAt' => $row['created_at'],
                'communityPostsCount' => $communityPostsCount,
                'communityPosts' => $communityPosts,
                'isFollowing' => $isFollowing,
                'isOwnProfile' => $viewerId === $userId,
            ];
        }
        return null;
    }

    public static function getUsers(int $viewerId, array $params = []): array
    {
        $search = trim((string)($params['q'] ?? ''));
        
        $blockedBy = DirectMessageModel::getBlockedUsers($viewerId);
        $blockedByIds = array_merge($blockedBy, [$viewerId]);
        $excludeList = implode(',', array_map('intval', $blockedByIds));
        
        $where = "u.is_active = 1 AND u.user_id NOT IN ($excludeList) AND COALESCE(up.is_anonymous, 1) = 0";

        if ($search !== '') {
            $safeSearch = self::escape($search);
            $where .= " AND (
                u.username LIKE '%$safeSearch%'
                OR u.first_name LIKE '%$safeSearch%'
                OR u.last_name LIKE '%$safeSearch%'
                OR u.display_name LIKE '%$safeSearch%'
            )";
        }

        $sql = "
            SELECT
                u.user_id,
                u.username,
                u.first_name,
                u.last_name,
                u.display_name,
                u.profile_picture,
                (SELECT COUNT(*) FROM user_connections
                 WHERE connected_user_id = u.user_id AND status = 'accepted') AS followers_count
            FROM users u
            LEFT JOIN user_profiles up ON up.user_id = u.user_id
            WHERE $where
            ORDER BY u.created_at DESC
            LIMIT 50
        ";
        
        $rs = Database::search($sql);
        $users = [];
        
        while ($row = $rs->fetch_assoc()) {
            $connectionStatus = DirectMessageModel::getConnectionStatus($viewerId, (int)$row['user_id']);
            $isFollowing = $connectionStatus === 'accepted';
            
            $displayName = $row['display_name'] 
                ?? trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) 
                ?: 'User';
            
            $users[] = [
                'userId' => (int)$row['user_id'],
                'username' => $row['username'] ?? '',
                'displayName' => $displayName,
                'profilePicture' => $row['profile_picture'] ?? '',
                'followersCount' => (int)$row['followers_count'],
                'isFollowing' => $isFollowing,
            ];
        }
        
        return $users;
    }

    public static function getFollowers(int $userId): array
    {
        $sql = "
            SELECT 
                u.user_id,
                u.username,
                u.display_name,
                u.profile_picture
            FROM user_connections c
            JOIN users u ON u.user_id = c.user_id
            WHERE c.connected_user_id = $userId
            AND c.status = 'accepted'
            ORDER BY c.updated_at DESC
            LIMIT 100
        ";
        
        $rs = Database::search($sql);
        $followers = [];
        
        while ($row = $rs->fetch_assoc()) {
            $followers[] = [
                'userId' => (int)$row['user_id'],
                'username' => $row['username'] ?? '',
                'displayName' => $row['display_name'] ?: 'User',
                'profilePicture' => $row['profile_picture'] ?? '',
            ];
        }
        
        return $followers;
    }

    public static function getFollowing(int $userId): array
    {
        $sql = "
            SELECT 
                u.user_id,
                u.username,
                u.display_name,
                u.profile_picture
            FROM user_connections c
            JOIN users u ON u.user_id = c.connected_user_id
            WHERE c.user_id = $userId
            AND c.status = 'accepted'
            ORDER BY c.updated_at DESC
            LIMIT 100
        ";
        
        $rs = Database::search($sql);
        $following = [];
        
        while ($row = $rs->fetch_assoc()) {
            $following[] = [
                'userId' => (int)$row['user_id'],
                'username' => $row['username'] ?? '',
                'displayName' => $row['display_name'] ?: 'User',
                'profilePicture' => $row['profile_picture'] ?? '',
            ];
        }
        
        return $following;
    }

    public static function updateProfile(int $userId, array $data): bool
    {
        $displayName = self::escape(trim((string)($data['display_name'] ?? '')));
        $bio = self::escape(trim((string)($data['bio'] ?? '')));
        
        Database::iud(
            "UPDATE users SET display_name = '$displayName', bio = '$bio' WHERE user_id = $userId"
        );
        
        return true;
    }

    public static function getConnectionStatus(int $viewerId, int $targetId): ?string
    {
        return DirectMessageModel::getConnectionStatus($viewerId, $targetId);
    }
}
