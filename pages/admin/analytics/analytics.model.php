<?php

class AnalyticsModel
{
    /* ------------------------------------------------------------------ */
    /* Summary KPI cards                                                    */
    /* ------------------------------------------------------------------ */
    public static function getSummary(): array
    {
        $users   = Database::search("SELECT COUNT(*) AS total FROM users WHERE role = 'user' AND is_active = 1");
        $sessions = Database::search("SELECT COUNT(*) AS total FROM sessions");
        $completed = Database::search("SELECT COUNT(*) AS total FROM sessions WHERE status = 'completed'");
        $plans   = Database::search("SELECT COUNT(*) AS total, COALESCE(AVG(progress_percentage), 0) AS avg_progress FROM recovery_plans WHERE status != 'cancelled'");
        $jobs    = Database::search("SELECT COUNT(*) AS total FROM job_posts WHERE is_active = 1");
        $counselors = Database::search("SELECT COUNT(*) AS total FROM counselors WHERE is_verified = 1");
        $posts   = Database::search("SELECT COUNT(*) AS total FROM community_posts WHERE is_active = 1");
        $checkins = Database::search("SELECT COUNT(*) AS total FROM daily_checkins WHERE checkin_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");

        $u  = $users      ? ($users->fetch_assoc()      ?: []) : [];
        $s  = $sessions   ? ($sessions->fetch_assoc()   ?: []) : [];
        $cp = $completed  ? ($completed->fetch_assoc()  ?: []) : [];
        $p  = $plans      ? ($plans->fetch_assoc()      ?: []) : [];
        $j  = $jobs       ? ($jobs->fetch_assoc()       ?: []) : [];
        $c  = $counselors ? ($counselors->fetch_assoc() ?: []) : [];
        $po = $posts      ? ($posts->fetch_assoc()      ?: []) : [];
        $ch = $checkins   ? ($checkins->fetch_assoc()   ?: []) : [];

        return [
            'totalUsers'       => (int) ($u['total']  ?? 0),
            'totalSessions'    => (int) ($s['total']  ?? 0),
            'completedSessions'=> (int) ($cp['total'] ?? 0),
            'totalPlans'       => (int) ($p['total']  ?? 0),
            'avgPlanProgress'  => round((float) ($p['avg_progress'] ?? 0), 1),
            'activeJobs'       => (int) ($j['total']  ?? 0),
            'verifiedCounselors'=> (int) ($c['total'] ?? 0),
            'activePosts'      => (int) ($po['total'] ?? 0),
            'checkinsLast30'   => (int) ($ch['total'] ?? 0),
        ];
    }

    /* ------------------------------------------------------------------ */
    /* 6-month new-user registrations + sessions for bar chart             */
    /* ------------------------------------------------------------------ */
    public static function getEngagementChart(string $timePeriod = 'lastMonth'): array
    {
        $months    = 6;
        $labels    = [];
        $newUsers  = [];
        $sessData  = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $ts = strtotime("-{$i} months");
            $labels[] = date('M Y', $ts);
            $ym = date('Y-m', $ts);

            $rsU = Database::search("SELECT COUNT(*) AS cnt FROM users WHERE role='user' AND DATE_FORMAT(created_at,'%Y-%m')='{$ym}'");
            $newUsers[] = $rsU ? (int)($rsU->fetch_assoc()['cnt'] ?? 0) : 0;

            $rsS = Database::search("SELECT COUNT(*) AS cnt FROM sessions WHERE DATE_FORMAT(session_datetime,'%Y-%m')='{$ym}'");
            $sessData[] = $rsS ? (int)($rsS->fetch_assoc()['cnt'] ?? 0) : 0;
        }

        // Demo fallback
        if (array_sum($newUsers) === 0) { $newUsers = [12, 19, 14, 27, 22, 31]; }
        if (array_sum($sessData) === 0) { $sessData = [8,  15, 10, 22, 18, 26]; }

        return compact('labels', 'newUsers', 'sessData');
    }

    /* ------------------------------------------------------------------ */
    /* Recovery plan adoption doughnut                                      */
    /* ------------------------------------------------------------------ */
    public static function getPlanAdoptionChart(): array
    {
        $rsTotal    = Database::search("SELECT COUNT(*) AS cnt FROM users WHERE role='user' AND is_active=1");
        $rsWithPlan = Database::search("SELECT COUNT(DISTINCT user_id) AS cnt FROM recovery_plans");
        $total    = $rsTotal    ? (int)($rsTotal->fetch_assoc()['cnt']    ?? 0) : 0;
        $withPlan = $rsWithPlan ? (int)($rsWithPlan->fetch_assoc()['cnt'] ?? 0) : 0;
        $withoutPlan = max(0, $total - $withPlan);

        if ($withPlan === 0 && $withoutPlan === 0) { $withPlan = 68; $withoutPlan = 32; }
        return ['labels' => ['With Plan', 'Without Plan'], 'data' => [$withPlan, $withoutPlan]];
    }

    /* ------------------------------------------------------------------ */
    /* Session status breakdown for pie chart                               */
    /* ------------------------------------------------------------------ */
    public static function getSessionStatusChart(): array
    {
        $rs = Database::search(
            "SELECT status, COUNT(*) AS cnt FROM sessions GROUP BY status ORDER BY cnt DESC"
        );
        $labels = []; $data = []; $colors = [];
        $palette = ['completed'=>'#6366f1','scheduled'=>'#10b981','cancelled'=>'#f43f5e','confirmed'=>'#f59e0b','in_progress'=>'#3b82f6','no_show'=>'#94a3b8'];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $s = $row['status'];
            $labels[] = ucfirst(str_replace('_', ' ', $s));
            $data[]   = (int)$row['cnt'];
            $colors[] = $palette[$s] ?? '#cbd5e1';
        }
        if (empty($data)) {
            $labels = ['Completed','Scheduled','Cancelled']; $data = [14,12,4];
            $colors = ['#6366f1','#10b981','#f43f5e'];
        }
        return compact('labels', 'data', 'colors');
    }

    /* ------------------------------------------------------------------ */
    /* Mood distribution from daily check-ins                               */
    /* ------------------------------------------------------------------ */
    public static function getMoodChart(): array
    {
        $rs = Database::search(
            "SELECT mood_label, COUNT(*) AS cnt FROM daily_checkins WHERE mood_label IS NOT NULL GROUP BY mood_label ORDER BY cnt DESC LIMIT 6"
        );
        $labels = []; $data = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $labels[] = $row['mood_label'];
            $data[]   = (int)$row['cnt'];
        }
        if (empty($data)) {
            $labels = ['Great','Good','Okay','Neutral','Terrible'];
            $data   = [3, 4, 3, 1, 1];
        }
        return compact('labels', 'data');
    }

    /* ------------------------------------------------------------------ */
    /* Plan status breakdown                                                */
    /* ------------------------------------------------------------------ */
    public static function getPlanStatusChart(): array
    {
        $rs = Database::search(
            "SELECT status, COUNT(*) AS cnt FROM recovery_plans GROUP BY status ORDER BY cnt DESC"
        );
        $labels=[]; $data=[]; $colors=[];
        $palette=['active'=>'#6366f1','completed'=>'#10b981','paused'=>'#f59e0b','draft'=>'#94a3b8','cancelled'=>'#f43f5e'];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $labels[] = ucfirst($row['status']);
            $data[]   = (int)$row['cnt'];
            $colors[] = $palette[$row['status']] ?? '#cbd5e1';
        }
        if (empty($data)) {
            $labels=['Active','Completed','Paused','Draft']; $data=[4,2,1,1]; $colors=['#6366f1','#10b981','#f59e0b','#94a3b8'];
        }
        return compact('labels', 'data', 'colors');
    }

    /* ------------------------------------------------------------------ */
    /* Top counselors by sessions                                           */
    /* ------------------------------------------------------------------ */
    public static function getTopCounselors(): array
    {
        $rs = Database::search(
            "SELECT COALESCE(u.display_name, u.email) AS name,
                    c.specialty,
                    c.rating,
                    c.total_reviews,
                    COUNT(s.session_id) AS session_count
             FROM counselors c
             INNER JOIN users u ON u.user_id = c.user_id
             LEFT JOIN sessions s ON s.counselor_id = c.counselor_id
             WHERE c.is_verified = 1
             GROUP BY c.counselor_id, u.display_name, u.email, c.specialty, c.rating, c.total_reviews
             ORDER BY session_count DESC
             LIMIT 5"
        );
        $rows = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $rows[] = [
                'name'          => $row['name'],
                'specialty'     => $row['specialty'] ?? 'General',
                'rating'        => number_format((float)($row['rating'] ?? 0), 1),
                'reviews'       => (int)($row['total_reviews'] ?? 0),
                'sessionCount'  => (int)($row['session_count'] ?? 0),
            ];
        }
        if (empty($rows)) {
            $rows = [
                ['name'=>'Pasidu Rajapaksha','specialty'=>'Trauma & PTSD','rating'=>'3.0','reviews'=>1,'sessionCount'=>33],
                ['name'=>'samantha','specialty'=>'Mental Health','rating'=>'0.0','reviews'=>0,'sessionCount'=>0],
            ];
        }
        return $rows;
    }
}
