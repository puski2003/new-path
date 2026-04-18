<?php

/**
 * Step 2 Model: Substance Use Info + Risk Scoring
 */
class Step2Model
{
    /**
     * Save all substance/motivation fields and calculate + store risk score.
     */
    public static function saveSubstanceInfo(
        int    $userId,
        string $primarySubstance,
        string $frequency,
        string $lastUsed,
        int    $quitAttempts,
        string $motivation
    ): bool {
        Database::setUpConnection();

        $uid   = (int) $userId;
        $sub   = Database::$connection->real_escape_string($primarySubstance);
        $freq  = Database::$connection->real_escape_string($frequency);
        $last  = Database::$connection->real_escape_string($lastUsed);
        $motiv = Database::$connection->real_escape_string($motivation);
        $quit  = (int) $quitAttempts;
        $score = self::calcRiskScore($primarySubstance, $frequency, $lastUsed, $quitAttempts, $motivation);

        Database::iud(
            "INSERT INTO user_profiles
               (user_id, recovery_type, substance_frequency, last_used_timeframe, quit_attempts, motivation_level, risk_score)
             VALUES
               ($uid, '$sub', '$freq', '$last', $quit, '$motiv', $score)
             ON DUPLICATE KEY UPDATE
               recovery_type        = '$sub',
               substance_frequency  = '$freq',
               last_used_timeframe  = '$last',
               quit_attempts        = $quit,
               motivation_level     = '$motiv',
               risk_score           = $score"
        );

        Database::iud("UPDATE users SET current_onboarding_step = 3 WHERE user_id = $uid");

        return true;
    }

    /**
     * Calculate the 5-field weighted risk score (range 5–15).
     *
     * LOW      5–7
     * MODERATE 8–11
     * HIGH     12–15
     */
    private static function calcRiskScore(
        string $substance,
        string $frequency,
        string $lastUsed,
        int    $quitAttempts,
        string $motivation
    ): int {
        // Substance (1–3)
        $s = match($substance) {
            'Alcohol', 'Marijuana'              => 1,
            'Prescription', 'Stimulants'        => 2,
            'Opioids'                           => 3,
            default                             => 1,  // None / Other
        };

        // Frequency (1–3)
        $f = match($frequency) {
            'Occasionally', 'Monthly', 'None'   => 1,
            'Weekly'                            => 2,
            'Daily'                             => 3,
            default                             => 1,
        };

        // Last used (1–3)
        $l = match($lastUsed) {
            'More than a month ago', 'Never'    => 1,
            'Past month'                        => 2,
            'Past week', 'Today'                => 3,
            default                             => 1,
        };

        // Quit attempts (1–3)
        $q = $quitAttempts <= 1 ? 1 : ($quitAttempts <= 3 ? 2 : 3);

        // Motivation (1–3)
        $m = match($motivation) {
            'exploring'  => 1,
            'motivated'  => 2,
            'desperate'  => 3,
            default      => 1,
        };

        return $s + $f + $l + $q + $m;
    }

    /**
     * Save evaluation data (addiction types) to onboarding_evaluation table.
     */
    public static function saveEvaluation(int $userId, array $addictions = []): bool {
        Database::setUpConnection();

        $uid = (int) $userId;
        $addictionsJson = json_encode($addictions);
        $addictionsEscaped = Database::$connection->real_escape_string($addictionsJson);

        Database::iud(
            "INSERT INTO onboarding_evaluation (user_id, addictions)
             VALUES ($uid, '$addictionsEscaped')
             ON DUPLICATE KEY UPDATE
                addictions = '$addictionsEscaped'"
        );

        return true;
    }
}
