<?php

class ProgressModel
{
    private static array $achievementDefs = [
        'sober_1d'       => ['type'=>'sober','days'=>1,   'title'=>'1 Day Sober',        'badge'=>'1D',  'icon'=>'sun',            'milestone'=>false],
        'sober_7d'       => ['type'=>'sober','days'=>7,   'title'=>'7 Days Sober',       'badge'=>'7D',  'icon'=>'calendar',       'milestone'=>false],
        'sober_14d'      => ['type'=>'sober','days'=>14,  'title'=>'2 Weeks Sober',      'badge'=>'2W',  'icon'=>'calendar-check', 'milestone'=>false],
        'sober_30d'      => ['type'=>'sober','days'=>30,  'title'=>'First Month',        'badge'=>'1M',  'icon'=>'medal',          'milestone'=>true],
        'sober_60d'      => ['type'=>'sober','days'=>60,  'title'=>'Two Months',         'badge'=>'2M',  'icon'=>'award',          'milestone'=>false],
        'sober_90d'      => ['type'=>'sober','days'=>90,  'title'=>'3 Months Sober',     'badge'=>'3M',  'icon'=>'trophy',         'milestone'=>true],
        'sober_180d'     => ['type'=>'sober','days'=>180, 'title'=>'Half a Year',        'badge'=>'6M',  'icon'=>'star',           'milestone'=>true],
        'sober_365d'     => ['type'=>'sober','days'=>365, 'title'=>'One Full Year',      'badge'=>'1Y',  'icon'=>'crown',          'milestone'=>true],
        'first_checkin'  => ['type'=>'activity',          'title'=>'First Check-in',     'badge'=>'CI',  'icon'=>'clipboard-list', 'milestone'=>false],
        'first_journal'  => ['type'=>'activity',          'title'=>'First Journal Entry','badge'=>'JE',  'icon'=>'book-open',      'milestone'=>false],
        'plan_completed' => ['type'=>'activity',          'title'=>'Plan Completed',     'badge'=>'PC',  'icon'=>'check-circle',   'milestone'=>true],
    ];

    public function getProgressStats(int $userId): array
    {
        $stats = [
            'daysSober'        => 0,
            'totalDaysTracked' => 0,
            'urgesLogged'      => 0,
            'sessionsCompleted'=> 0,
            'trackingStarted'  => false,
        ];

        // Always compute days_sober live from sobriety_start_date — this is the
        // source of truth. user_progress rows can be stale (days_sober = 0 from
        // when tracking first started) and must not override the real count.
        $rsProfile = Database::search(
            "SELECT DATEDIFF(CURDATE(), sobriety_start_date) AS days
             FROM user_profiles
             WHERE user_id = $userId
               AND sobriety_start_date IS NOT NULL
             LIMIT 1"
        );
        if ($p = $rsProfile->fetch_assoc()) {
            $stats['daysSober'] = max(0, (int)($p['days'] ?? 0));
        }

        $rsTracked = Database::search(
            "SELECT COUNT(DISTINCT date) AS tracked
             FROM user_progress
             WHERE user_id = $userId"
        );
        if ($t = $rsTracked->fetch_assoc()) {
            $stats['totalDaysTracked'] = max($stats['daysSober'], (int)($t['tracked'] ?? 0));
        } else {
            $stats['totalDaysTracked'] = $stats['daysSober'];
        }

        $rsUrges = Database::search("SELECT COUNT(*) AS urge_count FROM urge_logs WHERE user_id = $userId");
        if ($u = $rsUrges->fetch_assoc()) {
            $stats['urgesLogged'] = (int)($u['urge_count'] ?? 0);
        }

        $rsSessions = Database::search(
            "SELECT COUNT(*) AS session_count
             FROM sessions
             WHERE user_id = $userId
               AND status = 'completed'"
        );
        if ($s = $rsSessions->fetch_assoc()) {
            $stats['sessionsCompleted'] = (int)($s['session_count'] ?? 0);
        }

        $rsStarted = Database::search(
            "SELECT sobriety_start_date FROM user_profiles WHERE user_id = $userId LIMIT 1"
        );
        if ($row = $rsStarted->fetch_assoc()) {
            $stats['trackingStarted'] = $row['sobriety_start_date'] !== null;
        }

        return $stats;
    }

    public function getUserTaskStats(int $userId): array
    {
        $rs = Database::search(
            "SELECT
                SUM(CASE WHEN rt.status = 'completed' THEN 1 ELSE 0 END) AS completed_count,
                SUM(CASE WHEN rt.status <> 'completed' THEN 1 ELSE 0 END) AS pending_count
             FROM recovery_tasks rt
             INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             WHERE rp.user_id = $userId
               AND rp.status = 'active'"
        );

        $row = $rs->fetch_assoc();
        return [
            'completed' => isset($row['completed_count']) ? (int)$row['completed_count'] : 0,
            'pending'   => isset($row['pending_count'])   ? (int)$row['pending_count']   : 0,
        ];
    }

    public function getUserAchievements(int $userId): array
    {
        $earnedRs = Database::search(
            "SELECT achievement_key, awarded_at FROM user_achievements WHERE user_id = $userId"
        );
        $earned = [];
        while ($row = $earnedRs->fetch_assoc()) {
            $earned[$row['achievement_key']] = $row['awarded_at'];
        }

        $result = [];
        foreach (self::$achievementDefs as $key => $def) {
            $result[] = array_merge($def, [
                'key'       => $key,
                'earned'    => isset($earned[$key]),
                'awardedAt' => $earned[$key] ?? null,
            ]);
        }
        return $result;
    }

    // $daysSober is passed in so this method does not need a second DB round-trip
    public function getSobrietyChartData(int $userId, int $daysSober): array
    {
        $labels = [];
        $values = [];

        $rs = Database::search(
            "SELECT DATE_FORMAT(date,'%b %d') AS d, days_sober
             FROM user_progress WHERE user_id = $userId
             ORDER BY date DESC LIMIT 8"
        );
        if ($rs) {
            $rows = [];
            while ($r = $rs->fetch_assoc()) $rows[] = $r;
            $rows = array_reverse($rows);
            foreach ($rows as $r) { $labels[] = $r['d']; $values[] = (int)$r['days_sober']; }
        }

        if (empty($labels) && $daysSober > 0) {
            for ($i = max(1, $daysSober - 5); $i <= $daysSober; $i++) {
                $labels[] = "Day $i";
                $values[] = $i;
            }
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getUrgeSparklineData(int $userId): array
    {
        $labels = [];
        $values = [];

        $rs = Database::search(
            "SELECT DATE_FORMAT(MIN(logged_at),'%b %d') AS d, COUNT(*) AS cnt
             FROM urge_logs WHERE user_id = $userId
             GROUP BY DATE(logged_at) ORDER BY DATE(logged_at) DESC LIMIT 8"
        );
        if ($rs) {
            $rows = [];
            while ($r = $rs->fetch_assoc()) $rows[] = $r;
            $rows = array_reverse($rows);
            foreach ($rows as $r) { $labels[] = $r['d']; $values[] = (int)$r['cnt']; }
        }

        if (empty($labels)) { $labels = ['–']; $values = [0]; }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getSessionBarData(int $userId): array
    {
        $labels = [];
        $values = [];

        $rs = Database::search(
            "SELECT DATE_FORMAT(MIN(session_datetime),'%b %d') AS d
             FROM sessions WHERE user_id = $userId
             GROUP BY DATE(session_datetime) ORDER BY DATE(session_datetime) DESC LIMIT 6"
        );
        if ($rs) {
            $rows = [];
            while ($r = $rs->fetch_assoc()) $rows[] = $r;
            $rows = array_reverse($rows);
            foreach ($rows as $r) { $labels[] = $r['d']; $values[] = 1; }
        }

        if (empty($labels)) { $labels = ['No data']; $values = [0]; }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getSessionsHistory(int $userId, int $limit = 6): array
    {
        $history = [];

        $rs = Database::search(
            "SELECT s.session_datetime, s.status,
                    COALESCE(u.display_name, CONCAT(u.first_name,' ',u.last_name)) AS counselor_name
             FROM sessions s
             LEFT JOIN counselors c ON c.counselor_id = s.counselor_id
             LEFT JOIN users u ON u.user_id = c.user_id
             WHERE s.user_id = $userId
             ORDER BY s.session_datetime DESC LIMIT $limit"
        );
        if ($rs) {
            while ($r = $rs->fetch_assoc()) {
                $history[] = [
                    'date'    => date('Y-m-d', strtotime($r['session_datetime'])),
                    'checkin' => date('g:i A', strtotime($r['session_datetime'])),
                    'event'   => htmlspecialchars($r['counselor_name'] ?? 'Session'),
                    'status'  => $r['status'] ?? 'scheduled',
                ];
            }
        }

        return $history;
    }

    public function getPreviousDaysSober(int $userId): int
    {
        $rs = Database::search(
            "SELECT days_sober FROM user_progress
             WHERE user_id = $userId AND date <= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
             ORDER BY date DESC LIMIT 1"
        );
        return ($rs && ($row = $rs->fetch_assoc())) ? (int)$row['days_sober'] : 0;
    }
}
