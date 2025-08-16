<?php

/**
 * Step 3 Model: Assessment Info
 */
class Step3Model
{
    /**
     * Store assessment score and increment step
     */
    public static function saveAssessmentScore(int $userId, int $score): bool
{
    $safeUserId = (int) $userId;
    $safeScore  = (int) $score;

    Database::iud(
        "UPDATE user_profiles
         SET risk_score = $safeScore, updated_at = NOW()
         WHERE user_id = $safeUserId"
    );

    Database::iud(
        "UPDATE users SET current_onboarding_step = 4 WHERE user_id = $safeUserId"
    );

    return true;
}
}
