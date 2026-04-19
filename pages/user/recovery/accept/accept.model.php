<?php

class AcceptModel
{
    public static function accept(int $planId, int $userId): bool
    {
        if ($planId <= 0 || $userId <= 0) return false;

        Database::iud(
            "UPDATE recovery_plans
             SET status = 'paused', updated_at = NOW()
             WHERE user_id = $userId
               AND status = 'active'
               AND is_template = 0
               AND plan_id <> $planId"
        );

        Database::iud(
            "UPDATE recovery_plans
             SET assigned_status = 'accepted',
                 status = 'active',
                 start_date = COALESCE(start_date, CURDATE()),
                 updated_at = NOW()
             WHERE plan_id = $planId
               AND user_id = $userId
               AND assigned_status = 'pending'"
        );

        return true;
    }
}