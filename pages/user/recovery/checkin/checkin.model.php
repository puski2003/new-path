<?php

class CheckinModel
{
    private static $achievementDefs = [
        '7_days'    => ['type'=>'sober', 'days'=>7,    'title'=>'7 Days Sober',     'badge'=>'7D',  'icon'=>'calendar', 'milestone'=>true],
        '30_days'   => ['type'=>'sober', 'days'=>30,   'title'=>'30 Days Sober',    'badge'=>'30D', 'icon'=>'calendar', 'milestone'=>true],
        '90_days'   => ['type'=>'sober', 'days'=>90,   'title'=>'90 Days Sober',    'badge'=>'90D', 'icon'=>'calendar', 'milestone'=>true],
        '180_days'  => ['type'=>'sober', 'days'=>180,  'title'=>'180 Days Sober',  'badge'=>'180D','icon'=>'calendar', 'milestone'=>true],
        '365_days' => ['type'=>'sober', 'days'=>365,  'title'=>'1 Year Sober',  'badge'=>'1Y', 'icon'=>'calendar', 'milestone'=>true],
    ];

    public static function getTodayCheckin(int $userId): ?array
    {
        $today = date('Y-m-d');
        $rs = Database::search(
            "SELECT checkin_id, mood_rating, mood_label, energy_level, sleep_quality, stress_level, notes, is_sober
             FROM daily_checkins WHERE user_id = $userId AND checkin_date = '$today' LIMIT 1"
        );
        return ($rs && $rs->num_rows > 0) ? $rs->fetch_assoc() : null;
    }

    public static function getStats(int $userId): array
    {
        $rs = Database::search(
            "SELECT sobriety_start_date, days_sober, total_days_tracked, total_sessions_completed
             FROM user_progress
             WHERE user_id = $userId
             LIMIT 1"
        );

        $row = $rs ? $rs->fetch_assoc() : null;

        $startDate = $row['sobriety_start_date'] ?? null;
        $daysSober = $startDate ? (int)((time() - strtotime($startDate)) / 86400) + 1 : 0;
        $daysSober = max(0, $daysSober);

        $urgesLogged = 0;
        $urgeRs = Database::search("SELECT COUNT(*) AS cnt FROM urge_logs WHERE user_id = $userId AND outcome = 'resisted'");
        if ($urgeRs) {
            $urgesLogged = (int)($urgeRs->fetch_assoc()['cnt'] ?? 0);
        }

        return [
            'daysSober' => $daysSober,
            'totalDaysTracked' => (int)($row['total_days_tracked'] ?? 0),
            'urgesLogged' => $urgesLogged,
            'sessionsCompleted' => (int)($row['total_sessions_completed'] ?? 0),
            'trackingStarted' => $startDate !== null,
        ];
    }

    public static function save(int $userId, array $data): bool
    {
        $mood = (int)($data['mood_rating'] ?? 0);
        $energy = (int)($data['energy_level'] ?? 0);
        $sleep = (int)($data['sleep_quality'] ?? 0);
        $stress = (int)($data['stress_level'] ?? 0);
        $isSober = isset($data['is_sober']) ? 1 : 0;
        $notes = trim($data['notes'] ?? '');

        $moodLabels = [1 => 'Terrible', 2 => 'Bad', 3 => 'Okay', 4 => 'Good', 5 => 'Great'];
        $moodLabel = $moodLabels[$mood] ?? '';

        $today = date('Y-m-d');

        Database::setUpConnection();
        $conn = Database::$connection;
        $safeNotes = $conn->real_escape_string($notes);
        $safeMoodLabel = $conn->real_escape_string($moodLabel);

        $existing = self::getTodayCheckin($userId);
        if ($existing) {
            Database::iud("UPDATE daily_checkins
                SET mood_rating=$mood, mood_label='$safeMoodLabel',
                    energy_level=$energy, sleep_quality=$sleep,
                    stress_level=$stress, is_sober=$isSober, notes='$safeNotes'
                WHERE checkin_id={$existing['checkin_id']}");
        } else {
            Database::iud("INSERT INTO daily_checkins
                (user_id, checkin_date, mood_rating, mood_label, energy_level, sleep_quality, stress_level, is_sober, notes)
                VALUES ($userId, '$today', $mood, '$safeMoodLabel', $energy, $sleep, $stress, $isSober, '$safeNotes')");
        }

        self::checkAndAwardAchievements($userId);
        return true;
    }

    public static function validate(array $data): ?string
    {
        $mood = (int)($data['mood_rating'] ?? 0);
        $energy = (int)($data['energy_level'] ?? 0);
        $sleep = (int)($data['sleep_quality'] ?? 0);
        $stress = (int)($data['stress_level'] ?? 0);

        if ($mood < 1 || $mood > 5) return 'Please select a mood rating.';
        if ($energy < 1 || $energy > 5) return 'Please select your energy level.';
        if ($sleep < 1 || $sleep > 5) return 'Please rate your sleep quality.';
        if ($stress < 1 || $stress > 5) return 'Please rate your stress level.';
        return null;
    }

    private static function checkAndAwardAchievements(int $userId): void
    {
        if ($userId <= 0) return;

        $earnedRs = Database::search(
            "SELECT achievement_key FROM user_achievements WHERE user_id = $userId"
        );
        $earned = [];
        while ($row = $earnedRs->fetch_assoc()) {
            $earned[$row['achievement_key']] = true;
        }

        $stats = self::getStats($userId);
        $daysSober = (int)$stats['daysSober'];

        foreach (self::$achievementDefs as $key => $def) {
            if (isset($earned[$key])) continue;
            if ($def['type'] === 'sober' && $daysSober >= $def['days']) {
                self::awardAchievement($userId, $key);
            }
        }

        if (!isset($earned['first_checkin'])) {
            $rs = Database::search("SELECT 1 FROM daily_checkins WHERE user_id = $userId LIMIT 1");
            if ($rs && $rs->num_rows > 0) self::awardAchievement($userId, 'first_checkin');
        }

        if (!isset($earned['first_journal'])) {
            $rs = Database::search("SELECT 1 FROM journal_entries WHERE user_id = $userId LIMIT 1");
            if ($rs && $rs->num_rows > 0) self::awardAchievement($userId, 'first_journal');
        }
    }

    private static function awardAchievement(int $userId, string $key): void
    {
        Database::iud(
            "INSERT IGNORE INTO user_achievements (user_id, achievement_key, awarded_at)
             VALUES ($userId, '" . addslashes($key) . "', NOW())"
        );
    }
}