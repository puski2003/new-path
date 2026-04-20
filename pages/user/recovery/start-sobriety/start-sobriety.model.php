<?php

class StartSobrietyModel
{
    public static function start(int $userId, ?string $date = null): bool
    {
        if ($userId <= 0) return false;

        $safeDate = 'CURDATE()';
        if ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && strtotime($date) <= time()) {
            $safeDate = "'" . $date . "'";
        }

        Database::iud(
            "UPDATE user_profiles
             SET sobriety_start_date = $safeDate, updated_at = NOW()
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