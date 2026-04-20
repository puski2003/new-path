<?php

class ReviewModel
{
    public static function submitReview(int $userId, int $sessionId, int $rating, string $reviewText): bool
    {
        if ($sessionId <= 0 || $rating < 1 || $rating > 5) return false;

        $rs = Database::search(
            "SELECT session_id, counselor_id, rating FROM sessions
             WHERE session_id = $sessionId
               AND user_id   = $userId
               AND (status = 'completed' OR (status IN ('scheduled','confirmed') AND session_datetime < NOW()))
             LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;

        $session = $rs->fetch_assoc();
        if ($session['rating'] !== null) return false; // already reviewed

        Database::setUpConnection();
        $safeReview = Database::$connection->real_escape_string(trim($reviewText));
        $reviewSql  = $safeReview !== '' ? "'$safeReview'" : 'NULL';

        Database::iud(
            "UPDATE sessions
             SET rating     = $rating,
                 review     = $reviewSql,
                 updated_at = NOW()
             WHERE session_id = $sessionId
               AND user_id   = $userId
               AND rating    IS NULL"
        );

        // Recalculate counselor's aggregate rating
        $counselorId = (int)$session['counselor_id'];
        Database::iud(
            "UPDATE counselors c
             SET c.total_reviews = (
                     SELECT COUNT(*) FROM sessions
                     WHERE counselor_id = $counselorId
                       AND rating IS NOT NULL AND status = 'completed'
                 ),
                 c.rating = (
                     SELECT AVG(rating) FROM sessions
                     WHERE counselor_id = $counselorId
                       AND rating IS NOT NULL AND status = 'completed'
                 )
             WHERE c.counselor_id = $counselorId"
        );

        return true;
    }
}
