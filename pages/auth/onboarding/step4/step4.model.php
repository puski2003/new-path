<?php

/**
 * Step 4 Model: Plan Selection
 */
class Step4Model
{
    /**
     * Fetch the normalized score for a user (defaults to 0% if not yet scored).
     */
    public static function getRiskScore(int $userId): float
    {
        Database::setUpConnection();
        $uid = (int) $userId;
        $rs  = Database::search("SELECT risk_score FROM user_profiles WHERE user_id = $uid LIMIT 1");
        $row = $rs->fetch_assoc();
        return (float) ($row['risk_score'] ?? 0);
    }
}
