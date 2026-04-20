<?php

class GoalUpdateModel
{
    public static function update(int $goalId, int $userId, string $title, string $goalType, int $targetDays, string $description): bool
    {
        if ($goalId <= 0 || $userId <= 0 || trim($title) === '' || $targetDays <= 0) return false;

        Database::setUpConnection();
        $conn = Database::$connection;
        $safeTitle = $conn->real_escape_string($title);
        $safeDesc = $conn->real_escape_string($description);
        $safeType = in_array($goalType, ['short_term', 'long_term']) ? $goalType : 'short_term';

        Database::iud(
            "UPDATE recovery_goals rg
             INNER JOIN recovery_plans rp ON rp.plan_id = rg.plan_id
             SET rg.title = '$safeTitle',
                 rg.description = '$safeDesc',
                 rg.goal_type = '$safeType',
                 rg.target_days = $targetDays,
                 rg.updated_at = NOW()
             WHERE rg.goal_id = $goalId
               AND rp.user_id = $userId"
        );
        return true;
    }
}