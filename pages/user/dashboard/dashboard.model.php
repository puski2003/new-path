<?php

/**
 * User Dashboard Model — DB queries only.
 * Returns plain arrays — no HTML, no logic.
 */
class UserDashboardModel
{
    /**
     * Get the number of days sober for a user.
     * sobriety_start_date lives in user_profiles, not users.
     */
    public static function getDaysSober(int $userId): int
    {
        // Always compute live from sobriety_start_date — stale user_progress rows
        // (days_sober = 0 from initialisation) must not override the real count.
        $rs = Database::search(
            "SELECT DATEDIFF(CURDATE(), sobriety_start_date) AS days
             FROM user_profiles
             WHERE user_id = $userId
               AND sobriety_start_date IS NOT NULL"
        );
        $row = $rs->fetch_assoc();
        return $row ? max(0, (int)$row['days']) : 0;
    }

    /**
     * Get the next upcoming session for a user.
     * sessions.counselor_id → counselors.counselor_id → users.user_id
     */
    public static function getNextSession(int $userId): ?array
    {
        $rs = Database::search(
            "SELECT s.session_id, s.session_datetime, s.meeting_link,
                    COALESCE(u.display_name, CONCAT(u.first_name, ' ', u.last_name)) AS counselor_name
             FROM sessions s
             JOIN counselors c ON c.counselor_id = s.counselor_id
             JOIN users u ON u.user_id = c.user_id
             WHERE s.user_id = $userId
               AND s.session_datetime >= NOW()
               AND s.status IN ('scheduled', 'confirmed')
             ORDER BY s.session_datetime ASC
             LIMIT 1"
        );
        $row = $rs->fetch_assoc();
        if (!$row) return null;

        return [
            'counselorName' => $row['counselor_name'],
            'formattedTime' => date('M j, g:i A', strtotime($row['session_datetime'])),
            'meetingLink'   => $row['meeting_link'] ?? '#',
        ];
    }

    /**
     * Get community highlight posts (latest 3 active posts).
     */
    public static function getCommunityHighlights(int $limit = 3): array
    {
        $rs = Database::search(
            "SELECT p.post_id, p.title, p.is_anonymous,
                    COALESCE(u.display_name, CONCAT(u.first_name, ' ', u.last_name)) AS display_name,
                    u.profile_picture
             FROM community_posts p
             JOIN users u ON u.user_id = p.user_id
             WHERE p.is_active = 1
             ORDER BY p.created_at DESC
             LIMIT $limit"
        );

        $posts = [];
        while ($row = $rs->fetch_assoc()) {
            $posts[] = [
                'title'             => $row['title'],
                'anonymous'         => (bool) $row['is_anonymous'],
                'displayName'       => $row['display_name'],
                'profilePictureUrl' => $row['profile_picture'],
            ];
        }
        return $posts;
    }

    /**
     * Get daily tasks for a user from their active recovery plan.
     * Uses recovery_tasks from an active recovery_plan.
     */
    public static function getDailyTasks(int $userId, int $limit = 5): array
    {
        $rs = Database::search(
            "SELECT rt.task_id, rt.title, rt.status, rt.priority, rt.task_type
             FROM recovery_tasks rt
             JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             WHERE rp.user_id = $userId
               AND rp.status = 'active'
               AND (rp.assigned_status IS NULL OR rp.assigned_status = 'accepted')
               AND rt.phase = (
                   SELECT MIN(rt2.phase)
                   FROM recovery_tasks rt2
                   WHERE rt2.plan_id = rp.plan_id
                     AND rt2.status <> 'completed'
               )
             ORDER BY rt.status ASC, rt.priority DESC, rt.sort_order ASC
             LIMIT $limit"
        );

        $tasks = [];
        while ($row = $rs->fetch_assoc()) {
            $tasks[] = [
                'id'        => (int) $row['task_id'],
                'title'     => $row['title'],
                'completed' => $row['status'] === 'completed',
                'urgent'    => $row['priority'] === 'high',
                'taskType'  => str_replace('_', ' ', $row['task_type'] ?? 'task'),
            ];
        }
        return $tasks;
    }

    /**
     * Get recovery plan progress percentage.
     * Uses progress_percentage from the active recovery_plan, or computes from tasks.
     */
    public static function getProgressPercentage(int $userId): int
    {
        $rs = Database::search(
            "SELECT rp.progress_percentage
             FROM recovery_plans rp
             WHERE rp.user_id = $userId
               AND rp.status = 'active'
             ORDER BY rp.updated_at DESC
             LIMIT 1"
        );
        $row = $rs->fetch_assoc();
        return $row ? (int) $row['progress_percentage'] : 0;
    }

    /**
     * Get milestone progress (sobriety progress toward next milestone).
     */
    public static function getMilestoneProgress(int $daysSober): array
    {
        $milestones = [7, 30, 90, 180, 365];
        $nextMilestone = 7;

        foreach ($milestones as $m) {
            if ($daysSober < $m) {
                $nextMilestone = $m;
                break;
            }
        }

        // If past all milestones
        if ($daysSober >= 365) {
            return [
                'progress'      => 100,
                'nextMilestone' => 365,
            ];
        }

        // Find previous milestone
        $prevMilestone = 0;
        foreach ($milestones as $m) {
            if ($m >= $nextMilestone) break;
            $prevMilestone = $m;
        }

        $progress = (int) round(($daysSober / $nextMilestone) * 100);

        return [
            'progress'      => min(100, max(0, $progress)),
            'nextMilestone' => $nextMilestone,
        ];
    }

    /**
     * Calculate achievements based on days sober.
     */
    public static function getAchievements(int $daysSober): array
    {
        return [
            'oneYear'     => $daysSober >= 365,
            'sixMonths'   => $daysSober >= 180,
            'threeMonths' => $daysSober >= 90,
            'firstMonth'  => $daysSober >= 30,
            'sevenDays'   => $daysSober >= 7,
        ];
    }
}
