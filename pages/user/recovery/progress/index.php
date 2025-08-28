<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/../recovery.model.php';

$userId            = (int)$user['id'];
$stats             = RecoveryModel::getProgressStats($userId);
$taskStats         = RecoveryModel::getUserTaskStats($userId);

$daysSober         = (int)$stats['daysSober'];
$daysTracked       = (int)$stats['totalDaysTracked'];
$urgesLogged       = (int)$stats['urgesLogged'];
$sessionsCompleted = (int)$stats['sessionsCompleted'];
$trackingStarted   = (bool)$stats['trackingStarted'];

// Next milestone
$milestones    = [1, 7, 14, 30, 60, 90, 180, 365];
$nextMilestone = 365;
$prevMilestone = 0;
foreach ($milestones as $m) {
    if ($daysSober < $m) { $nextMilestone = $m; break; }
    $prevMilestone = $m;
}
$milestoneProgress = ($nextMilestone > $prevMilestone)
    ? min(100, (int)round(($daysSober - $prevMilestone) / ($nextMilestone - $prevMilestone) * 100))
    : 100;

// Achievements
$achievements = RecoveryModel::getUserAchievements($userId);

// Sobriety chart — last 8 log entries
$chartLabels = []; $chartValues = [];
$rsChart = Database::search(
    "SELECT DATE_FORMAT(date,'%b %d') AS d, days_sober
     FROM user_progress WHERE user_id = $userId
     ORDER BY date DESC LIMIT 8"
);
if ($rsChart) {
    $rows = [];
    while ($r = $rsChart->fetch_assoc()) $rows[] = $r;
    $rows = array_reverse($rows);
    foreach ($rows as $r) { $chartLabels[] = $r['d']; $chartValues[] = (int)$r['days_sober']; }
}
if (empty($chartLabels) && $daysSober > 0) {
    for ($i = max(1, $daysSober - 5); $i <= $daysSober; $i++) {
        $chartLabels[] = "Day $i"; $chartValues[] = $i;
    }
}

// Urge sparkline (last 8 days with urge counts)
$urgeLabels = []; $urgeValues = [];
$rsUrge = Database::search(
    "SELECT DATE_FORMAT(MIN(logged_at),'%b %d') AS d, COUNT(*) AS cnt
     FROM urge_logs WHERE user_id = $userId
     GROUP BY DATE(logged_at) ORDER BY DATE(logged_at) DESC LIMIT 8"
);
if ($rsUrge) {
    $rows = [];
    while ($r = $rsUrge->fetch_assoc()) $rows[] = $r;
    $rows = array_reverse($rows);
    foreach ($rows as $r) { $urgeLabels[] = $r['d']; $urgeValues[] = (int)$r['cnt']; }
}
if (empty($urgeLabels)) { $urgeLabels = ['–']; $urgeValues = [0]; }

// Session bar data (last 6 sessions)
$sessionBarLabels = []; $sessionBarValues = [];
$rssess = Database::search(
    "SELECT DATE_FORMAT(MIN(session_datetime),'%b %d') AS d
     FROM sessions WHERE user_id = $userId
     GROUP BY DATE(session_datetime) ORDER BY DATE(session_datetime) DESC LIMIT 6"
);
if ($rssess) {
    $rows = [];
    while ($r = $rssess->fetch_assoc()) $rows[] = $r;
    $rows = array_reverse($rows);
    foreach ($rows as $r) { $sessionBarLabels[] = $r['d']; $sessionBarValues[] = 1; }
}
if (empty($sessionBarLabels)) { $sessionBarLabels = ['No data']; $sessionBarValues = [0]; }

// Sessions history table
$sessionsHistory = [];
$rsHist = Database::search(
    "SELECT s.session_datetime, s.status,
            COALESCE(u.display_name, CONCAT(u.first_name,' ',u.last_name)) AS counselor_name
     FROM sessions s
     LEFT JOIN counselors c ON c.counselor_id = s.counselor_id
     LEFT JOIN users u ON u.user_id = c.user_id
     WHERE s.user_id = $userId
     ORDER BY s.session_datetime DESC LIMIT 6"
);
if ($rsHist) {
    while ($r = $rsHist->fetch_assoc()) {
        $sessionsHistory[] = [
            'date'      => date('Y-m-d', strtotime($r['session_datetime'])),
            'checkin'   => date('g:i A', strtotime($r['session_datetime'])),
            'event'     => htmlspecialchars($r['counselor_name'] ?? 'Session'),
            'status'    => $r['status'] ?? 'scheduled',
        ];
    }
}

// Trigger distribution (task types as proxy)
$totalTasks  = $taskStats['completed'] + $taskStats['pending'];
$taskRate    = ($totalTasks > 0) ? (int)round($taskStats['completed'] / $totalTasks * 100) : 0;
$recoveryRate = ($daysTracked > 0) ? min(100, (int)round($daysSober / $daysTracked * 100)) : 0;
$sessionRate  = min(100, $sessionsCompleted * 10);

// Sobriety change (vs. 7 days ago)
$rsPrev = Database::search(
    "SELECT days_sober FROM user_progress
     WHERE user_id = $userId AND date <= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
     ORDER BY date DESC LIMIT 1"
);
$prevDays  = ($rsPrev && ($p = $rsPrev->fetch_assoc())) ? (int)$p['days_sober'] : 0;
$soberChange = $daysSober - $prevDays;

$pageTitle = 'Progress Tracker';
$pageStyle = ['user/progress-tracker'];
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/../../common/user.html.head.php'; ?>
<body>
<main class="main-container">
    <?php $activePage = 'recovery'; require_once __DIR__ . '/../../common/user.sidebar.php'; ?>

    <section class="main-content">
        <img src="/assets/img/main-content-head.svg" alt="" class="main-header-bg-image" />

        <!-- ── Header ─────────────────────────────────────────────────── -->
        <div class="main-content-header">
            <div class="main-content-header-text">
                <h2>Recovery Progress</h2>
                <p>Track your progress and stay motivated.</p>
            </div>

            <div class="card-container">
                <div class="card days-sober-card">
                    <div class="flex items-center gap-xs">
                        <span class="days-label">DAYS SOBER</span>
                        <i data-lucide="heart" style="width:13px;height:13px;color:var(--color-accent);"></i>
                    </div>
                    <span class="days-number"><?= $daysSober ?></span>
                </div>

                <div class="card days-sober-card">
                    <span class="days-label">MILESTONE PROGRESS</span>
                    <?php if (!$trackingStarted): ?>
                        <span class="days-number" style="font-size:var(--font-size-base);color:var(--color-text-muted);">Not started</span>
                        <span style="font-size:var(--font-size-xs);color:var(--color-text-muted);margin-top:2px;">Start sobriety tracking to begin</span>
                    <?php else: ?>
                        <span class="days-number" style="font-size:var(--font-size-xl);"><?= $milestoneProgress ?>%</span>
                        <div style="width:100%;height:5px;background:var(--color-progress-track);border-radius:var(--radius-full);overflow:hidden;margin-top:4px;">
                            <div style="width:<?= $milestoneProgress ?>%;height:100%;background:var(--color-primary);border-radius:var(--radius-full);transition:width .4s ease;"></div>
                        </div>
                        <span style="font-size:var(--font-size-xs);color:var(--color-text-muted);margin-top:4px;">
                            Next: <?= $nextMilestone ?> day<?= $nextMilestone > 1 ? 's' : '' ?> sober
                            &nbsp;(<?= $nextMilestone - $daysSober ?>d away)
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <img src="/assets/img/recovery-head.svg" alt=""
                 style="width:140px;position:absolute;right:0;bottom:-10px;" />
        </div>

        <!-- ── Body ───────────────────────────────────────────────────── -->
        <div class="main-content-body">
            <div class="progress-tracker-container">

                <div class="back-navigation">
                    <button class="back-btn" onclick="history.back()" title="Back">
                        <i data-lucide="chevron-left" style="width:18px;height:18px;"></i>
                    </button>
                </div>

                <h3 style="text-align:center;font-size:var(--font-size-lg);font-weight:var(--font-weight-semibold);margin-bottom:var(--spacing-xl);">
                    Progress Tracker
                </h3>

                <div class="tracker-content">

                    <!-- ── ACHIEVEMENTS ──────────────────────────────────── -->
                    <div class="section achievements-section">
                        <h4 class="section-title">
                            <i data-lucide="trophy" class="section-title-icon"></i>
                            Achievements
                        </h4>

                        <div class="achievements-grid">
                            <?php foreach ($achievements as $ach):
                                $earned = $ach['earned'];
                            ?>
                            <div class="achievement-item <?= $earned ? 'earned' : 'locked' ?> flex flex-col items-center"
                                 style="text-align:center;">

                                <div class="achievement-badge <?= ($ach['milestone'] && $earned) ? 'milestone' : '' ?>">
                                    <?php if ($earned): ?>
                                        <i data-lucide="<?= $ach['icon'] ?>" style="width:26px;height:26px;"></i>
                                    <?php else: ?>
                                        <span class="badge-text"><?= $ach['badge'] ?></span>
                                    <?php endif; ?>
                                </div>

                                <span class="font-semibold text-sm" style="margin-top:var(--spacing-xs);">
                                    <?= htmlspecialchars($ach['title']) ?>
                                </span>
                                <?php if (!$earned && isset($ach['days'])): ?>
                                <span style="font-size:var(--font-size-xs);color:var(--color-text-muted);">
                                    <?= max(0, $ach['days'] - $daysSober) ?>d left
                                </span>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- ── DATA VISUALIZATIONS ───────────────────────────── -->
                    <div class="section data-visualizations">
                        <h4 class="section-title">
                            <i data-lucide="bar-chart-2" class="section-title-icon"></i>
                            Data Visualizations
                        </h4>

                        <div class="stats-grid">

                            <!-- Sobriety Progress sparkline -->
                            <div class="card stat-card">
                                <div class="stat-header">
                                    <span class="stat-label">Sobriety Progress</span>
                                </div>
                                <div class="stat-change <?= $soberChange >= 0 ? 'positive' : 'negative' ?>">
                                    <?= $soberChange >= 0 ? '+' : '' ?><?= $soberChange ?>
                                </div>
                                <div class="stat-sublabel">
                                    vs 7 days ago &nbsp;
                                    <span style="color:<?= $soberChange >= 0 ? 'var(--color-primary)' : 'var(--color-error)' ?>">
                                        <?= $soberChange >= 0 ? '+' : '' ?><?= $soberChange ?>d
                                    </span>
                                </div>
                                <div class="chart-container" style="margin:var(--spacing-sm) 0 0;padding:var(--spacing-sm);max-height:100px;">
                                    <canvas id="soberSparkline" style="height:80px!important;"></canvas>
                                </div>
                            </div>

                            <!-- Urge Trend sparkline -->
                            <div class="card stat-card">
                                <div class="stat-header">
                                    <span class="stat-label">Urge Trend</span>
                                </div>
                                <div class="stat-change <?= $urgesLogged === 0 ? 'positive' : ($urgesLogged < 5 ? 'positive' : 'negative') ?>">
                                    <?= $urgesLogged ?>
                                </div>
                                <div class="stat-sublabel">
                                    total urges &nbsp;
                                    <span style="color:var(--color-text-muted);">
                                        <?= $urgesLogged === 0 ? 'none logged' : ($urgesLogged < 5 ? 'low' : 'monitor closely') ?>
                                    </span>
                                </div>
                                <div class="chart-container" style="margin:var(--spacing-sm) 0 0;padding:var(--spacing-sm);max-height:100px;">
                                    <canvas id="urgeSparkline" style="height:80px!important;"></canvas>
                                </div>
                            </div>

                            <!-- Session Count bar chart -->
                            <div class="card stat-card">
                                <div class="stat-header">
                                    <span class="stat-label">Session Count</span>
                                </div>
                                <div class="stat-change positive">+<?= $sessionsCompleted ?></div>
                                <div class="stat-sublabel">counseling sessions done</div>
                                <div class="chart-container" style="margin:var(--spacing-sm) 0 0;padding:var(--spacing-sm);max-height:100px;">
                                    <canvas id="sessionBars" style="height:80px!important;"></canvas>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ── TRIGGER DISTRIBUTION ──────────────────────────── -->
                    <div class="section">
                        <h4 class="section-title">
                            <i data-lucide="activity" class="section-title-icon"></i>
                            Trigger Distribution
                        </h4>

                        <div class="flex justify-between items-center" style="margin-bottom:var(--spacing-lg);">
                            <span class="stat-change positive" style="font-size:var(--font-size-xl);"><?= $recoveryRate ?>%</span>
                            <span class="stat-sublabel">This period &nbsp;
                                <span style="color:var(--color-primary);">+<?= $recoveryRate ?>%</span>
                            </span>
                        </div>

                        <div class="trigger-bars">
                            <!-- Sobriety Rate -->
                            <div class="trigger-bar">
                                <span class="trigger-name">Sobriety Rate</span>
                                <div class="bar-container">
                                    <div style="width:<?= $recoveryRate ?>%;height:100%;background:var(--color-primary);border-radius:var(--radius-sm);transition:width .5s ease;"></div>
                                </div>
                                <span class="text-sm text-muted"><?= $recoveryRate ?>% of <?= $daysTracked ?> days tracked</span>
                            </div>

                            <!-- Task Completion -->
                            <div class="trigger-bar">
                                <span class="trigger-name">Task Completion</span>
                                <div class="bar-container">
                                    <div style="width:<?= $taskRate ?>%;height:100%;background:var(--color-secondary);border-radius:var(--radius-sm);transition:width .5s ease;"></div>
                                </div>
                                <span class="text-sm text-muted"><?= $taskStats['completed'] ?> / <?= $totalTasks ?> tasks (<?= $taskRate ?>%)</span>
                            </div>

                            <!-- Counseling Engagement -->
                            <div class="trigger-bar">
                                <span class="trigger-name">Counseling Engagement</span>
                                <div class="bar-container">
                                    <div style="width:<?= $sessionRate ?>%;height:100%;background:var(--color-accent);border-radius:var(--radius-sm);transition:width .5s ease;"></div>
                                </div>
                                <span class="text-sm text-muted"><?= $sessionsCompleted ?> sessions completed</span>
                            </div>
                        </div>
                    </div>

                    <!-- ── SESSIONS & COMMIT HISTORY ─────────────────────── -->
                    <div class="section sessions-history">
                        <div class="flex justify-between items-center" style="margin-bottom:var(--spacing-lg);">
                            <h4 class="section-title" style="margin-bottom:0;">
                                <i data-lucide="clock" class="section-title-icon"></i>
                                Sessions &amp; Commit History
                            </h4>
                            <a href="/user/sessions" class="btn btn-secondary">View All</a>
                        </div>

                        <?php if (empty($sessionsHistory)): ?>
                        <div style="text-align:center;padding:var(--spacing-2xl) 0;">
                            <i data-lucide="calendar-x" style="width:36px;height:36px;color:var(--color-text-muted);display:block;margin:0 auto var(--spacing-md);"></i>
                            <p class="text-sm text-muted">No sessions recorded yet.</p>
                            <a href="/user/counselors" class="btn btn-primary" style="display:inline-flex;margin-top:var(--spacing-md);">
                                Book a Session
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="history-table">
                            <div class="table-header" style="grid-template-columns:1fr 1fr 1fr;">
                                <span>Date</span>
                                <span>Check-in</span>
                                <span>Event</span>
                            </div>
                            <?php foreach ($sessionsHistory as $s): ?>
                            <div class="table-row" style="grid-template-columns:1fr 1fr 1fr;">
                                <span class="row-date"><?= $s['date'] ?></span>
                                <span class="row-checkin <?= htmlspecialchars($s['status']) ?>"><?= $s['checkin'] ?></span>
                                <span class="row-status <?= htmlspecialchars($s['status']) ?>"><?= $s['event'] ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                </div><!-- /.tracker-content -->
            </div><!-- /.progress-tracker-container -->
        </div><!-- /.main-content-body -->
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
lucide.createIcons();

const sparklineDefaults = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: { enabled: false } },
    elements: { point: { radius: 0 } },
    scales: {
        x: { display: false },
        y: { display: false, beginAtZero: true }
    }
};

// Sobriety sparkline
new Chart(document.getElementById('soberSparkline'), {
    type: 'line',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
            data: <?= json_encode($chartValues) ?>,
            borderColor: '#3DE4B9',
            backgroundColor: 'rgba(61,228,185,0.15)',
            borderWidth: 2,
            tension: 0.4,
            fill: true,
        }]
    },
    options: sparklineDefaults
});

// Urge sparkline
new Chart(document.getElementById('urgeSparkline'), {
    type: 'line',
    data: {
        labels: <?= json_encode($urgeLabels) ?>,
        datasets: [{
            data: <?= json_encode($urgeValues) ?>,
            borderColor: '#f59e0b',
            backgroundColor: 'rgba(245,158,11,0.12)',
            borderWidth: 2,
            tension: 0.4,
            fill: true,
        }]
    },
    options: sparklineDefaults
});

// Session bar chart
new Chart(document.getElementById('sessionBars'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($sessionBarLabels) ?>,
        datasets: [{
            data: <?= json_encode($sessionBarValues) ?>,
            backgroundColor: 'rgba(61,228,185,0.6)',
            borderColor: '#3DE4B9',
            borderWidth: 1,
            borderRadius: 4,
        }]
    },
    options: {
        ...sparklineDefaults,
        scales: {
            x: { display: false },
            y: { display: false, beginAtZero: true, max: 2 }
        }
    }
});
</script>
</body>
</html>
