<?php

class LogUrgeModel
{
    public static function getProgressStats(int $userId): array
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
        $intensity = (int)($data['intensity'] ?? 0);
        $category = trim($data['trigger_category'] ?? '');
        $strategy = trim($data['coping_strategy'] ?? '');
        $outcome = trim($data['outcome'] ?? '');
        $notes = trim($data['notes'] ?? '');

        Database::setUpConnection();
        $conn = Database::$connection;
        $safeCategory = $conn->real_escape_string($category);
        $safeStrategy = $conn->real_escape_string(Encryption::encrypt($strategy));
        $safeOutcome  = $conn->real_escape_string($outcome);
        $safeNotes    = $conn->real_escape_string(Encryption::encrypt($notes));

        Database::iud("INSERT INTO urge_logs
            (user_id, intensity, trigger_category, coping_strategy_used, outcome, notes)
            VALUES ($userId, $intensity, '$safeCategory', '$safeStrategy', '$safeOutcome', '$safeNotes')");

        return true;
    }

    public static function validate(array $data): ?string
    {
        $intensity = (int)($data['intensity'] ?? 0);
        $category = trim($data['trigger_category'] ?? '');
        $outcome = trim($data['outcome'] ?? '');

        if ($intensity < 1 || $intensity > 10) return 'Please select an intensity level (1–10).';
        if (empty($category)) return 'Please select a trigger category.';
        if (empty($outcome)) return 'Please select an outcome.';
        return null;
    }
}