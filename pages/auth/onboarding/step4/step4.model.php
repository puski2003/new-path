<?php

/**
 * Step 4 Model: Plan Selection
 */
class Step4Model
{
    /**
     * Fetch the stored risk score for a user (defaults to 5 = LOW if not yet scored).
     */
    public static function getRiskScore(int $userId): int
    {
        Database::setUpConnection();
        $uid = (int) $userId;
        $rs  = Database::search("SELECT risk_score FROM user_profiles WHERE user_id = $uid LIMIT 1");
        $row = $rs->fetch_assoc();
        return (int) ($row['risk_score'] ?? 5);
    }
}
