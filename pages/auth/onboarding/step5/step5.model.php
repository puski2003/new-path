<?php

/**
 * Step 5 Model: Active Plan Retrieval
 */
class Step5Model
{
    /**
     * Retrieves the user's active recovery plan to display on the success screen.
     */
    public static function getActivePlan(int $userId): ?array
    {
        $safeUserId = (int) $userId;

        $rs = Database::search(
            "SELECT title, plan_type, start_date 
             FROM recovery_plans 
             WHERE user_id = $safeUserId AND status = 'active'
             ORDER BY created_at DESC 
             LIMIT 1"
        );

        $plan = $rs->fetch_assoc();
        return $plan ?: null;
    }
}
