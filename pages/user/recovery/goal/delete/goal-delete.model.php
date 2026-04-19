<?php

class GoalDeleteModel
{
    public static function delete(int $goalId, int $userId): bool
    {
        if ($goalId <= 0 || $userId <= 0) return false;

        Database::iud(
            "DELETE rg FROM recovery_goals rg
             INNER JOIN recovery_plans rp ON rp.plan_id = rg.plan_id
             WHERE rg.goal_id = $goalId
               AND rp.user_id = $userId"
        );
        return true;
    }
}