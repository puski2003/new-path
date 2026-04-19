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

    /**
     * Insert a notification row. Skips silently if an identical unread
     * notification of the same type was already sent within $dedupHours hours.
     */
    public static function send(
        int    $userId,
        string $type,
        string $title,
        string $message,
        string $link       = '',
        int    $dedupHours = 24
    ): void {
        Database::setUpConnection();
        $conn    = Database::$connection;
        $eType   = $conn->real_escape_string($type);
        $eTitle  = $conn->real_escape_string($title);
        $eMsg    = $conn->real_escape_string($message);
        $eLink   = $conn->real_escape_string($link);

        if ($dedupHours > 0) {
            $rs = Database::search(
                "SELECT 1 FROM notifications
                 WHERE user_id = $userId
                   AND type    = '$eType'
                   AND is_read = 0
                   AND created_at >= NOW() - INTERVAL $dedupHours HOUR
                 LIMIT 1"
            );
            if ($rs && $rs->num_rows > 0) return;
        }

        Database::iud(
            "INSERT INTO notifications (user_id, type, title, message, link)
             VALUES ($userId, '$eType', '$eTitle', '$eMsg', '$eLink')"
        );
    }

    // -------------------------------------------------------------------------
    // Missed-task reminders
    // Tune the two defaults below to change behaviour for the whole app:
    //   $windowDays    — how many past days to look at          (default 7)
    //   $missThreshold — days with missed tasks needed to fire  (default 3)
    // -------------------------------------------------------------------------

    /**
     * Check one user and send a task_reminder if they've missed enough days.
     * Called on dashboard load — fast (one query) and deduped to once per 24 h.
     */
    public static function remindIfMissedTasks(
        int $userId,
        int $windowDays    = 7,
        int $missThreshold = 3
    ): void {
        $rs = Database::search(
            "SELECT COUNT(DISTINCT rt.due_date) AS missed_days
             FROM recovery_tasks rt
             JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             WHERE rp.user_id  = $userId
               AND rp.status   = 'active'
               AND rt.due_date BETWEEN CURDATE() - INTERVAL $windowDays DAY
                                       AND CURDATE() - INTERVAL 1 DAY
               AND rt.status  != 'completed'"
        );
        if (!$rs) return;
        $row = $rs->fetch_assoc();
        if (!$row || (int)$row['missed_days'] < $missThreshold) return;

        $missed  = (int)$row['missed_days'];
        $message = "You've missed tasks on $missed of the last $windowDays days. "
                 . 'Small steps every day make a big difference — check your plan now.';

        self::send($userId, 'task_reminder', 'Stay on track with your recovery',
                   $message, '/user/recovery', 24);
    }

    /**
     * Check ALL active-plan users and fire reminders where needed.
     * Intended for a daily cron job (cron/task-reminder.php).
     */
    public static function remindAllMissedTasks(
        int $windowDays    = 7,
        int $missThreshold = 3
    ): void {
        $rs = Database::search(
            "SELECT rp.user_id,
                    COUNT(DISTINCT rt.due_date) AS missed_days
             FROM recovery_tasks rt
             JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             WHERE rp.status    = 'active'
               AND rt.due_date  BETWEEN CURDATE() - INTERVAL $windowDays DAY
                                        AND CURDATE() - INTERVAL 1 DAY
               AND rt.status   != 'completed'
             GROUP BY rp.user_id
             HAVING missed_days >= $missThreshold"
        );
        if (!$rs) return;

        while ($row = $rs->fetch_assoc()) {
            $userId  = (int)$row['user_id'];
            $missed  = (int)$row['missed_days'];
            $message = "You've missed tasks on $missed of the last $windowDays days. "
                     . 'Small steps every day make a big difference — check your plan now.';

            self::send($userId, 'task_reminder', 'Stay on track with your recovery',
                       $message, '/user/recovery', 24);
        }
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
