<?php
// receives: $data, $user — no DB calls, no logic
$daysSober         = $data['daysSober'];
$daysTracked       = $data['daysTracked'];
$urgesLogged       = $data['urgesLogged'];
$sessionsCompleted = $data['sessionsCompleted'];
$trackingStarted   = $data['trackingStarted'];
$nextMilestone     = $data['nextMilestone'];
$milestoneProgress = $data['milestoneProgress'];
$achievements      = $data['achievements'];
$soberChart        = $data['soberChart'];
$urgeChart         = $data['urgeChart'];
$sessionChart      = $data['sessionChart'];
$sessionsHistory   = $data['sessionsHistory'];
$taskStats         = $data['taskStats'];
$totalTasks        = $data['totalTasks'];
$taskRate          = $data['taskRate'];
$recoveryRate      = $data['recoveryRate'];
$sessionRate       = $data['sessionRate'];
$soberChange       = $data['soberChange'];
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
                        <form method="POST" action="/user/recovery/start-sobriety">
                            <button type="submit" style="margin-top:6px;padding:6px 14px;background:var(--color-primary);color:#fff;border:none;border-radius:var(--radius-md);cursor:pointer;font-size:var(--font-size-xs);">
                                Start Tracking
                            </button>
                        </form>
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

new Chart(document.getElementById('soberSparkline'), {
    type: 'line',
    data: {
        labels: <?= json_encode($soberChart['labels']) ?>,
        datasets: [{
            data: <?= json_encode($soberChart['values']) ?>,
            borderColor: '#3DE4B9',
            backgroundColor: 'rgba(61,228,185,0.15)',
            borderWidth: 2,
            tension: 0.4,
            fill: true,
        }]
    },
    options: sparklineDefaults
});

new Chart(document.getElementById('urgeSparkline'), {
    type: 'line',
    data: {
        labels: <?= json_encode($urgeChart['labels']) ?>,
        datasets: [{
            data: <?= json_encode($urgeChart['values']) ?>,
            borderColor: '#f59e0b',
            backgroundColor: 'rgba(245,158,11,0.12)',
            borderWidth: 2,
            tension: 0.4,
            fill: true,
        }]
    },
    options: sparklineDefaults
});

new Chart(document.getElementById('sessionBars'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($sessionChart['labels']) ?>,
        datasets: [{
            data: <?= json_encode($sessionChart['values']) ?>,
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
