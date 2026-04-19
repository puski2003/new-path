<?php

class GoalLogProgressModel
{
    public static function logProgress(int $goalId, int $userId, int $days = 1): bool
    {
        if ($goalId <= 0 || $userId <= 0 || $days <= 0) return false;

        $rs = Database::search(
            "SELECT rg.current_progress, rg.target_days, rg.status
             FROM recovery_goals rg
             INNER JOIN recovery_plans rp ON rp.plan_id = rg.plan_id
             WHERE rg.goal_id = $goalId AND rp.user_id = $userId
             LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;
        $row = $rs->fetch_assoc();
        if ($row['status'] === 'achieved') return true;

        $newProgress = min((int)$row['current_progress'] + $days, (int)$row['target_days']);
        $achieved = $newProgress >= (int)$row['target_days'];
        $status = $achieved ? 'achieved' : 'in_progress';
        $achievedAt = $achieved ? ', achieved_at = NOW()' : '';

        Database::iud(
            "UPDATE recovery_goals
             SET current_progress = $newProgress,
                 status = '$status'
                 $achievedAt,
                 updated_at = NOW()
             WHERE goal_id = $goalId"
        );
        return true;
    }
}