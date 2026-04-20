<?php

class RejectModel
{
    public static function reject(int $planId, int $userId): bool
    {
        if ($planId <= 0 || $userId <= 0) return false;

        Database::iud(
            "UPDATE recovery_plans
             SET assigned_status = 'rejected',
                 status = 'draft',
                 updated_at = NOW()
             WHERE plan_id = $planId
               AND user_id = $userId
               AND assigned_status = 'pending'"
        );

        return true;
    }
}