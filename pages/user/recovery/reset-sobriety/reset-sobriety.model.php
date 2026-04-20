<?php

class ResetSobrietyModel
{
    public static function reset(int $userId, string $reason = ''): bool
    {
        if ($userId <= 0) return false;

        $currentDays = 0;
        $rsProfile = Database::search(
            "SELECT DATEDIFF(CURDATE(), sobriety_start_date) AS days
             FROM user_profiles
             WHERE user_id = $userId AND sobriety_start_date IS NOT NULL
             LIMIT 1"
        );
        if ($row = $rsProfile->fetch_assoc()) {
            $currentDays = max(0, (int)$row['days']);
        }

        Database::setUpConnection();
        $safeNotes = Database::$connection->real_escape_string(Encryption::encrypt($reason));
        Database::iud(
            "INSERT INTO relapse_history
                (user_id, relapse_date, days_sober_before, trigger_notes, counselor_notified)
             VALUES ($userId, CURDATE(), $currentDays, '$safeNotes', 0)"
        );

        Database::iud(
            "UPDATE user_profiles
             SET sobriety_start_date = CURDATE(), updated_at = NOW()
             WHERE user_id = $userId"
        );

        Database::iud(
            "INSERT INTO user_progress (user_id, date, days_sober, is_sober_today, notes)
             VALUES ($userId, CURDATE(), 0, 1, 'Reset sobriety counter')
             ON DUPLICATE KEY UPDATE
               is_sober_today = VALUES(is_sober_today),
               notes = VALUES(notes),
               updated_at = NOW()"
        );

        return true;
    }
}