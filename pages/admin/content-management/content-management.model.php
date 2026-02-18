<?php

class ContentManagementModel
{
    private static function esc(string $value): string
    {
        Database::setUpConnection();
        return Database::$connection->real_escape_string($value);
    }

    private static function getAdminIdByUserId(int $adminUserId): int
    {
        $safeUserId = max(0, $adminUserId);
        if ($safeUserId <= 0) {
            return 0;
        }

        $rs = Database::search("SELECT admin_id FROM admin WHERE user_id = $safeUserId LIMIT 1");
        return (int) ($rs && ($row = $rs->fetch_assoc()) ? ($row['admin_id'] ?? 0) : 0);
    }

    /**
     * Paginated list of post reports.
     */
    public static function getReports(array $filters, int $limit = 20, int $offset = 0): array
    {
        $type   = trim((string)($filters['type']   ?? 'all'));
        $reason = trim((string)($filters['reason'] ?? 'all'));
        $status = trim((string)($filters['status'] ?? 'all'));

        $where = "WHERE 1=1";

        if ($type !== '' && $type !== 'all') {
            if ($type === 'post')    $where .= " AND pr.post_id IS NOT NULL AND pr.comment_id IS NULL";
            elseif ($type === 'comment') $where .= " AND pr.comment_id IS NOT NULL";
        }
        if ($reason !== '' && $reason !== 'all') {
            $sr = self::esc($reason);
            $where .= " AND pr.reason = '$sr'";
        }
        if ($status !== '' && $status !== 'all') {
            $ss = self::esc($status);
            $where .= " AND pr.status = '$ss'";
        }

        $countRs = Database::search(
            "SELECT COUNT(*) AS total
             FROM post_reports pr
             LEFT JOIN community_posts p ON p.post_id = pr.post_id
             $where"
        );
        $total = (int)(($countRs ? $countRs->fetch_assoc() : [])['total'] ?? 0);

        $rs = Database::search(
            "SELECT
                pr.report_id,
                pr.post_id,
                pr.comment_id,
                pr.reason,
                pr.description,
                pr.status,
                pr.action_taken,
                pr.created_at,
                CASE
                    WHEN pr.post_id IS NOT NULL AND pr.comment_id IS NULL THEN 'Post'
                    WHEN pr.comment_id IS NOT NULL THEN 'Comment'
                    ELSE 'Other'
                END AS content_type,
                COALESCE(p.title, SUBSTRING(p.content, 1, 80)) AS content_preview,
                p.is_active AS post_active,
                p.is_anonymous,
                COALESCE(au.display_name, CONCAT(au.first_name,' ',au.last_name), au.username, 'User') AS author_name,
                au.user_id AS author_user_id,
                COALESCE(ru.display_name, CONCAT(ru.first_name,' ',ru.last_name), ru.username, 'User') AS reporter_name,
                ru.email AS reporter_email
             FROM post_reports pr
             LEFT JOIN community_posts p ON p.post_id = pr.post_id
             LEFT JOIN users au ON au.user_id = p.user_id
             JOIN  users ru ON ru.user_id = pr.reporter_id
             $where
             ORDER BY pr.created_at DESC
             LIMIT $limit OFFSET $offset"
        );

        $rows = [];
        if ($rs) {
            while ($row = $rs->fetch_assoc()) {
                $rows[] = $row;
            }
        }

        return ['rows' => $rows, 'total' => $total];
    }

    /**
     * Full detail for a single report — used by the review modal.
     */
    public static function getReport(int $reportId): ?array
    {
        $rs = Database::search(
            "SELECT
                pr.report_id,
                pr.post_id,
                pr.comment_id,
                pr.reason,
                pr.description,
                pr.status,
                pr.action_taken,
                pr.created_at,
                CASE
                    WHEN pr.post_id IS NOT NULL AND pr.comment_id IS NULL THEN 'Post'
                    WHEN pr.comment_id IS NOT NULL THEN 'Comment'
                    ELSE 'Other'
                END AS content_type,
                p.title,
                p.content AS full_content,
                p.image_url,
                p.post_type,
                p.is_anonymous,
                p.is_active AS post_active,
                COALESCE(au.display_name, CONCAT(au.first_name,' ',au.last_name), au.username, 'User') AS author_name,
                au.user_id AS author_user_id,
                COALESCE(ru.display_name, CONCAT(ru.first_name,' ',ru.last_name), ru.username, 'User') AS reporter_name,
                ru.email AS reporter_email
             FROM post_reports pr
             LEFT JOIN community_posts p ON p.post_id = pr.post_id
             LEFT JOIN users au ON au.user_id = p.user_id
             JOIN  users ru ON ru.user_id = pr.reporter_id
             WHERE pr.report_id = $reportId
             LIMIT 1"
        );
        $row = $rs ? $rs->fetch_assoc() : null;
        return $row ?: null;
    }

    /**
     * Remove the reported post: soft-delete it, resolve all pending reports for it,
     * and notify the post author.
     */
    public static function removePost(int $reportId, int $adminUserId, string $note): bool
    {
        $adminId = self::getAdminIdByUserId($adminUserId);
        if ($adminId <= 0) return false;

        $rs = Database::search(
            "SELECT post_id, reporter_id, status FROM post_reports WHERE report_id = $reportId LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;

        $row = $rs->fetch_assoc();
        if (!in_array($row['status'], ['pending', 'reviewed'], true)) return false;

        $postId = (int)$row['post_id'];
        if ($postId <= 0) return false;

        $trimmedNote = trim($note);
        $safeNote = self::esc($trimmedNote);
        $actionTaken = $safeNote !== '' ? "'Post removed: $safeNote'" : "'Post removed by moderator'";

        // Soft-delete the post
        Database::iud(
            "UPDATE community_posts SET is_active = 0, updated_at = NOW() WHERE post_id = $postId"
        );

        // Resolve all pending/reviewed reports for this post in one query
        Database::iud(
            "UPDATE post_reports
             SET status = 'resolved', reviewed_by = $adminId,
                 reviewed_at = NOW(), action_taken = $actionTaken
             WHERE post_id = $postId AND status IN ('pending','reviewed')"
        );

        // Notify the post author
        $postRs = Database::search("SELECT user_id FROM community_posts WHERE post_id = $postId LIMIT 1");
        if ($postRs && ($postRow = $postRs->fetch_assoc())) {
            $authorId = (int)$postRow['user_id'];
            $t = self::esc('Post Removed');
            $m = $trimmedNote !== ''
                ? self::esc("Your post was removed by a moderator: $trimmedNote")
                : self::esc('Your post has been removed for violating community guidelines.');
            $l = self::esc('/user/community');
            Database::iud(
                "INSERT INTO notifications (user_id, type, title, message, link)
                 VALUES ($authorId, 'post_removed', '$t', '$m', '$l')"
            );
        }

        return true;
    }

    /**
     * Dismiss a report without taking action on the post.
     */
    public static function dismissReport(int $reportId, int $adminUserId, string $note): bool
    {
        $adminId = self::getAdminIdByUserId($adminUserId);
        if ($adminId <= 0) return false;

        $rs = Database::search(
            "SELECT status FROM post_reports WHERE report_id = $reportId LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;

        $row = $rs->fetch_assoc();
        if (!in_array($row['status'], ['pending', 'reviewed'], true)) return false;

        $safeNote   = self::esc(trim($note));
        $actionSql  = $safeNote !== '' ? "'$safeNote'" : 'NULL';

        Database::iud(
            "UPDATE post_reports
             SET status = 'dismissed', reviewed_by = $adminId,
                 reviewed_at = NOW(), action_taken = $actionSql
             WHERE report_id = $reportId"
        );

        return true;
    }

    /**
     * Summary stats for the header cards.
     */
    public static function getStats(): array
    {
        $today   = date('Y-m-d');
        $weekAgo = date('Y-m-d', strtotime('-7 days'));

        $pendingRs    = Database::search("SELECT COUNT(*) AS c FROM post_reports WHERE status = 'pending'");
        $pending      = (int)(($pendingRs ? $pendingRs->fetch_assoc() : [])['c'] ?? 0);

        $todayRs      = Database::search("SELECT COUNT(*) AS c FROM post_reports WHERE DATE(created_at) = '$today'");
        $todayCount   = (int)(($todayRs ? $todayRs->fetch_assoc() : [])['c'] ?? 0);

        $weekRs       = Database::search(
            "SELECT COUNT(*) AS c FROM post_reports
             WHERE status IN ('resolved','dismissed') AND reviewed_at >= '$weekAgo'"
        );
        $weekCount    = (int)(($weekRs ? $weekRs->fetch_assoc() : [])['c'] ?? 0);

        return [
            'today'          => $todayCount,
            'pending'        => $pending,
            'actionsThisWeek'=> $weekCount,
        ];
    }
}
