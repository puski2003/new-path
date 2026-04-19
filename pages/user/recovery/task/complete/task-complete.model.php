<?php

class TaskCompleteModel
{
    private static $achievementDefs = [
        '7_days'    => ['type'=>'sober', 'days'=>7,    'title'=>'7 Days Sober',     'badge'=>'7D',  'icon'=>'calendar', 'milestone'=>true],
        '30_days'   => ['type'=>'sober', 'days'=>30,   'title'=>'30 Days Sober',    'badge'=>'30D', 'icon'=>'calendar', 'milestone'=>true],
        '90_days'   => ['type'=>'sober', 'days'=>90,   'title'=>'90 Days Sober',    'badge'=>'90D', 'icon'=>'calendar', 'milestone'=>true],
        '180_days'  => ['type'=>'sober', 'days'=>180,  'title'=>'180 Days Sober',  'badge'=>'180D','icon'=>'calendar', 'milestone'=>true],
        '365_days' => ['type'=>'sober', 'days'=>365,  'title'=>'1 Year Sober',  'badge'=>'1Y', 'icon'=>'calendar', 'milestone'=>true],
    ];

    public static function complete(int $taskId, int $userId): bool
    {
        if ($taskId <= 0 || $userId <= 0) return false;

        $taskRs = Database::search(
            "SELECT rt.phase, rt.plan_id, rt.status
             FROM recovery_tasks rt
             INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             WHERE rt.task_id = $taskId AND rp.user_id = $userId
             LIMIT 1"
        );
        if (!$taskRs || $taskRs->num_rows === 0) return false;
        $taskRow = $taskRs->fetch_assoc();

        if ($taskRow['status'] === 'completed') return true;

        $currentPhase = (int)$taskRow['phase'];
        $planId = (int)$taskRow['plan_id'];

        if ($currentPhase > 1) {
            $blockRs = Database::search(
                "SELECT COUNT(*) AS blocked
                 FROM recovery_tasks
                 WHERE plan_id = $planId
                   AND phase < $currentPhase
                   AND status <> 'completed'"
            );
            $blockRow = $blockRs->fetch_assoc();
            if ((int)($blockRow['blocked'] ?? 0) > 0) {
                return false;
            }
        }

        Database::iud(
            "UPDATE recovery_tasks
             SET status = 'completed', completed_at = NOW(), updated_at = NOW()
             WHERE task_id = $taskId"
        );

        self::recalculatePlanProgress($planId);
        return true;
    }

    public static function checkPlanCompleted(int $taskId): ?array
    {
        $planRs = Database::search(
            "SELECT rp.plan_id, rp.status
             FROM recovery_tasks rt
             INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             WHERE rt.task_id = $taskId
             LIMIT 1"
        );
        if (!$planRs) return null;
        $row = $planRs->fetch_assoc();
        return $row['status'] === 'completed' ? $row : null;
    }

    public static function checkAndAwardAchievements(int $userId): void
    {
        if ($userId <= 0) return;

        $earnedRs = Database::search(
            "SELECT achievement_key FROM user_achievements WHERE user_id = $userId"
        );
        $earned = [];
        while ($row = $earnedRs->fetch_assoc()) {
            $earned[$row['achievement_key']] = true;
        }

        $stats = self::getProgressStats($userId);
        $daysSober = (int)$stats['daysSober'];

        foreach (self::$achievementDefs as $key => $def) {
            if (isset($earned[$key])) continue;
            if ($def['type'] === 'sober' && $daysSober >= $def['days']) {
                self::awardAchievement($userId, $key);
            }
        }

        if (!isset($earned['first_checkin'])) {
            $rs = Database::search("SELECT 1 FROM daily_checkins WHERE user_id = $userId LIMIT 1");
            if ($rs && $rs->num_rows > 0) self::awardAchievement($userId, 'first_checkin');
        }

        if (!isset($earned['first_journal'])) {
            $rs = Database::search("SELECT 1 FROM journal_entries WHERE user_id = $userId LIMIT 1");
            if ($rs && $rs->num_rows > 0) self::awardAchievement($userId, 'first_journal');
        }
    }

    private static function getProgressStats(int $userId): array
    {
        $rs = Database::search(
            "SELECT sobriety_start_date, days_sober, total_days_tracked, total_sessions_completed
             FROM user_progress
             WHERE user_id = $userId
             LIMIT 1"
        );

        $row = $rs ? $rs->fetch_assoc() : null;

        $startDate = $row['sobriety_start_date'] ?? null;
        $daysSober = $startDate ? (int)((time() - strtotime($startDate)) / 86400) + 1 : 0;
        $daysSober = max(0, $daysSober);

        return [
            'daysSober' => $daysSober,
            'totalDaysTracked' => (int)($row['total_days_tracked'] ?? 0),
            'trackingStarted' => $startDate !== null,
        ];
    }

    private static function awardAchievement(int $userId, string $key): void
    {
        Database::iud(
            "INSERT IGNORE INTO user_achievements (user_id, achievement_key, awarded_at)
             VALUES ($userId, '" . addslashes($key) . "', NOW())"
        );
    }

    private static function recalculatePlanProgress(int $planId): void
    {
        if ($planId <= 0) return;

        $stats = Database::search(
            "SELECT COUNT(*) AS total_count,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_count
             FROM recovery_tasks
             WHERE plan_id = $planId"
        );
        $row = $stats ? $stats->fetch_assoc() : null;
        $total = (int)($row['total_count'] ?? 0);
        $completed = (int)($row['completed_count'] ?? 0);
        $progress = $total > 0 ? (int)round(($completed / $total) * 100) : 0;

        $newStatus = ($total > 0 && $completed >= $total) ? 'completed' : 'active';

        Database::iud(
            "UPDATE recovery_plans
             SET progress_percentage = $progress,
                 status = '$newStatus',
                 actual_completion_date = " . ($newStatus === 'completed' ? 'CURDATE()' : 'actual_completion_date') . ",
                 updated_at = NOW()
             WHERE plan_id = $planId"
        );
    }
}