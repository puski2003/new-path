<?php

/**
 * Step 3 Model: Assessment Info
 */
class Step3Model
{
    /**
     * Store assessment answers (JSON), calculate normalized score, and increment step
     */
    public static function saveAssessmentAnswers(int $userId, ?string $answersJson, array $questionWeights = []): bool
    {
        $safeUserId = (int) $userId;
        $safeAnswers = $answersJson !== null
            ? Database::$connection->real_escape_string($answersJson)
            : 'NULL';

        $normalizedScore = null;

        if ($answersJson !== null && !empty($questionWeights)) {
            $answers = json_decode($answersJson, true);
            $ass = 0.0;
            $weightedMax = 0.0;

            foreach ($answers as $questionId => $answer) {
                $weight = $questionWeights[$questionId] ?? 0;
                if ($weight > 0) {
                    $adjustedScore = ((int)$answer) - 1;
                    $ass += $adjustedScore * $weight;
                    $weightedMax += 4 * $weight;
                }
            }

            if ($weightedMax > 0) {
                $normalizedScore = round(($ass / $weightedMax) * 100, 2);
            }

            $scoreEscaped = Database::$connection->real_escape_string((string)$normalizedScore);

            Database::iud(
                "INSERT INTO onboarding_evaluation (user_id, answers, normalized_score)
                 VALUES ($safeUserId, '$safeAnswers', $scoreEscaped)
                 ON DUPLICATE KEY UPDATE
                    answers = '$safeAnswers',
                    normalized_score = $scoreEscaped"
            );

            Database::iud(
                "UPDATE user_profiles SET risk_score = $scoreEscaped, updated_at = NOW() WHERE user_id = $safeUserId"
            );
        } elseif ($answersJson === null) {
            Database::iud(
                "INSERT INTO onboarding_evaluation (user_id, answers)
                 VALUES ($safeUserId, NULL)
                 ON DUPLICATE KEY UPDATE
                    answers = NULL"
            );
        }

        Database::iud(
            "UPDATE users SET current_onboarding_step = 4 WHERE user_id = $safeUserId"
        );

        return true;
    }

    /**
     * Store assessment score (legacy - kept for compatibility)
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
