<?php

/**
 * NotificationService — shared DB logic for the notification bell.
 * Works for user, counselor, and admin roles (all use the same table).
 */
class NotificationService
{
    public static function list(int $userId, int $limit = 20): array
    {
        $safeLimit = max(1, min(100, $limit));
        $rs = Database::search(
            "SELECT notification_id, type, title, message, link, is_read, created_at
             FROM notifications
             WHERE user_id = $userId
             ORDER BY created_at DESC
             LIMIT $safeLimit"
        );
        $items  = [];
        $unread = 0;
        while ($rs && ($row = $rs->fetch_assoc())) {
            $isRead = (int)$row['is_read'] === 1;
            if (!$isRead) $unread++;
            $items[] = [
                'id'        => (int)$row['notification_id'],
                'type'      => $row['type'],
                'title'     => $row['title'],
                'message'   => $row['message'],
                'link'      => $row['link'] ?: null,
                'isRead'    => $isRead,
                'createdAt' => $row['created_at'],
                'timeAgo'   => self::timeAgo(strtotime((string)$row['created_at'])),
            ];
        }
        return ['items' => $items, 'unread' => $unread];
    }

    public static function markAllRead(int $userId): void
    {
        Database::iud(
            "UPDATE notifications SET is_read = 1
             WHERE user_id = $userId AND is_read = 0"
        );
    }

    public static function markOneRead(int $userId, int $notifId): void
    {
        if ($notifId <= 0) return;
        Database::iud(
            "UPDATE notifications SET is_read = 1
             WHERE notification_id = $notifId AND user_id = $userId"
        );
    }

    private static function timeAgo(int $ts): string
    {
        $diff = time() - $ts;
        if ($diff < 60)     return 'just now';
        if ($diff < 3600)   return floor($diff / 60) . 'm ago';
        if ($diff < 86400)  return floor($diff / 3600) . 'h ago';
        if ($diff < 604800) return floor($diff / 86400) . 'd ago';
        return date('M j', $ts);
    }
}
