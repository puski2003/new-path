<?php

class StartSobrietyModel
{
    public static function start(int $userId): bool
    {
        if ($userId <= 0) return false;

        Database::iud(
            "UPDATE user_profiles
             SET sobriety_start_date = CURDATE(), updated_at = NOW()
             WHERE user_id = $userId
               AND sobriety_start_date IS NULL"
        );

        Database::iud(
            "INSERT INTO user_progress (user_id, date, days_sober, is_sober_today, notes)
             VALUES ($userId, CURDATE(), 0, 1, 'Started sobriety tracking')
             ON DUPLICATE KEY UPDATE
               is_sober_today = VALUES(is_sober_today),
               notes = VALUES(notes),
               updated_at = NOW()"
        );

        return true;
    }
}