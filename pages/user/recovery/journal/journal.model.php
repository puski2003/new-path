<?php

class JournalModel
{
    private static $achievementDefs = [
        '7_days'    => ['type'=>'sober', 'days'=>7,    'title'=>'7 Days Sober',     'badge'=>'7D',  'icon'=>'calendar', 'milestone'=>true],
        '30_days'   => ['type'=>'sober', 'days'=>30,   'title'=>'30 Days Sober',    'badge'=>'30D', 'icon'=>'calendar', 'milestone'=>true],
        '90_days'   => ['type'=>'sober', 'days'=>90,   'title'=>'90 Days Sober',    'badge'=>'90D', 'icon'=>'calendar', 'milestone'=>true],
        '180_days'  => ['type'=>'sober', 'days'=>180,  'title'=>'180 Days Sober',  'badge'=>'180D','icon'=>'calendar', 'milestone'=>true],
        '365_days' => ['type'=>'sober', 'days'=>365,  'title'=>'1 Year Sober',  'badge'=>'1Y', 'icon'=>'calendar', 'milestone'=>true],
    ];

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

    public static function getEntries(int $userId, int $page = 1, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;

        $countRs = Database::search("SELECT COUNT(*) AS total FROM journal_entries WHERE user_id = $userId");
        $total = (int)($countRs->fetch_assoc()['total'] ?? 0);

        $rs = Database::search("
            SELECT je.entry_id, je.title, je.content, je.mood, je.is_highlight, je.created_at,
                   jc.name AS category_name, jc.color AS category_color
            FROM journal_entries je
            LEFT JOIN journal_categories jc ON jc.category_id = je.category_id
            WHERE je.user_id = $userId
            ORDER BY je.created_at DESC
            LIMIT $limit OFFSET $offset
        ");

        $entries = [];
        if ($rs) {
            while ($row = $rs->fetch_assoc()) {
                $entries[] = $row;
            }
        }

        return [
            'entries' => $entries,
            'total' => $total,
            'totalPages' => max(1, (int)ceil($total / $limit)),
            'page' => $page,
        ];
    }

    public static function getCategories(int $userId): array
    {
        $rs = Database::search(
            "SELECT category_id, name, color FROM journal_categories
             WHERE user_id = $userId OR is_default = 1
             ORDER BY is_default DESC, name ASC"
        );

        $categories = [];
        if ($rs) {
            while ($row = $rs->fetch_assoc()) {
                $categories[] = $row;
            }
        }
        return $categories;
    }

    public static function getEntry(int $entryId, int $userId): ?array
    {
        if ($entryId <= 0) return null;

        $rs = Database::search(
            "SELECT * FROM journal_entries WHERE entry_id = $entryId AND user_id = $userId LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return null;

        return $rs->fetch_assoc();
    }

    public static function saveEntry(int $userId, array $data): bool
    {
        $entryId = (int)($data['entry_id'] ?? 0);
        $title = trim($data['title'] ?? '');
        $content = trim($data['content'] ?? '');
        $categoryId = (int)($data['category_id'] ?? 0);
        $mood = trim($data['mood'] ?? '');
        $isHighlight = isset($data['is_highlight']) ? 1 : 0;

        if (strlen($content) < 1) return false;

        Database::setUpConnection();
        $conn = Database::$connection;
        $safeTitle = $conn->real_escape_string($title);
        $safeContent = $conn->real_escape_string($content);
        $safeMood = $conn->real_escape_string($mood);
        $catVal = $categoryId > 0 ? $categoryId : 'NULL';

        if ($entryId > 0) {
            Database::iud("UPDATE journal_entries
                SET title='$safeTitle', content='$safeContent', category_id=$catVal,
                    mood='$safeMood', is_highlight=$isHighlight
                WHERE entry_id=$entryId AND user_id=$userId");
        } else {
            Database::iud("INSERT INTO journal_entries (user_id, title, content, category_id, mood, is_highlight)
                VALUES ($userId, '$safeTitle', '$safeContent', $catVal, '$safeMood', $isHighlight)");
        }

        self::checkAndAwardAchievements($userId);
        return true;
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