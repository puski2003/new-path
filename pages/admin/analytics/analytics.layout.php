<?php
$pageTitle = 'Analytics & Reports';
require_once __DIR__ . '/../common/admin.html.head.php';
?>
<style>
/* ── Analytics page ─────────────────────────────────────── */
.an-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}
.an-kpi {
    background: #fff;
    border-radius: 12px;
    padding: 20px 18px;
    box-shadow: 0 1px 4px rgba(0,0,0,.07);
    display: flex;
    flex-direction: column;
    gap: 6px;
    border-top: 3px solid transparent;
    transition: box-shadow .2s;
}
.an-kpi:hover { box-shadow: 0 4px 16px rgba(99,102,241,.14); }
.an-kpi--indigo  { border-color: #6366f1; }
.an-kpi--emerald { border-color: #10b981; }
.an-kpi--amber   { border-color: #f59e0b; }
.an-kpi--rose    { border-color: #f43f5e; }
.an-kpi--blue    { border-color: #3b82f6; }
.an-kpi--violet  { border-color: #8b5cf6; }
.an-kpi--teal    { border-color: #14b8a6; }
.an-kpi--orange  { border-color: #f97316; }
.an-kpi__icon {
    width: 36px; height: 36px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 4px;
}
.an-kpi__icon--indigo  { background:#eef2ff; color:#6366f1; }
.an-kpi__icon--emerald { background:#ecfdf5; color:#10b981; }
.an-kpi__icon--amber   { background:#fffbeb; color:#f59e0b; }
.an-kpi__icon--rose    { background:#fff1f2; color:#f43f5e; }
.an-kpi__icon--blue    { background:#eff6ff; color:#3b82f6; }
.an-kpi__icon--violet  { background:#f5f3ff; color:#8b5cf6; }
.an-kpi__icon--teal    { background:#f0fdfa; color:#14b8a6; }
.an-kpi__icon--orange  { background:#fff7ed; color:#f97316; }
.an-kpi__label  { font-size:12px; color:#64748b; font-weight:500; }
.an-kpi__value  { font-size:28px; font-weight:700; color:#1e293b; line-height:1; }
.an-kpi__sub    { font-size:11px; color:#94a3b8; }

/* ── Chart grid ─────────────────────────────────────────── */
.an-chart-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 16px;
}
.an-chart-grid--3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}
.an-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 4px rgba(0,0,0,.07);
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.an-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}
.an-card__title {
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
}
.an-card__badge {
    font-size: 11px;
    padding: 3px 10px;
    border-radius: 99px;
    background: #f1f5f9;
    color: #64748b;
}
.an-chart-wrap {
    position: relative;
    width: 100%;
}

/* ── Counselor table ─────────────────────────────────────── */
.an-cns-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.an-cns-table th {
    text-align: left;
    font-weight: 600;
    color: #64748b;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .5px;
    padding: 8px 10px;
    border-bottom: 1px solid #f1f5f9;
}
.an-cns-table td {
    padding: 10px 10px;
    border-bottom: 1px solid #f8fafc;
    color: #334155;
    vertical-align: middle;
}
.an-cns-table tr:last-child td { border-bottom: none; }
.an-cns-name { font-weight: 600; color: #1e293b; }
.an-cns-spec { font-size: 11px; color:#64748b; margin-top:2px; }
.an-stars { color: #f59e0b; font-size: 12px; }
.an-badge {
    display: inline-flex; align-items:center;
    padding: 3px 9px;
    border-radius: 99px;
    font-size: 11px;
    font-weight: 600;
}
.an-badge--indigo { background:#eef2ff; color:#6366f1; }
.an-badge--emerald { background:#ecfdf5; color:#10b981; }

/* ── Filter bar ──────────────────────────────────────────── */
.an-filter-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    background: #fff;
    border-radius: 12px;
    padding: 14px 20px;
    box-shadow: 0 1px 4px rgba(0,0,0,.07);
}
.an-filter-bar h1 {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}
.an-filter-actions { display:flex; gap:8px; align-items:center; }

@media (max-width: 900px) {
    .an-chart-grid { grid-template-columns: 1fr; }
    .an-chart-grid--3 { grid-template-columns: 1fr; }
    .an-kpi-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>

<main class="admin-main-container">
    <?php require_once __DIR__ . '/../common/admin.sidebar.php'; ?>

    <section class="admin-main-content">

        <!-- Page Header / Filter Bar -->
        <div class="an-filter-bar">
            <h1>Analytics &amp; Reports</h1>
            <div class="an-filter-actions">
                <form method="GET" style="display:flex;gap:8px;align-items:center;">
                    <select name="timePeriod" class="admin-dropdown" onchange="this.form.submit()">
                        <option value="lastWeek"  <?= $timePeriod === 'lastWeek'  ? 'selected' : '' ?>>Last Week</option>
                        <option value="lastMonth" <?= $timePeriod === 'lastMonth' ? 'selected' : '' ?>>Last Month</option>
                        <option value="lastYear"  <?= $timePeriod === 'lastYear'  ? 'selected' : '' ?>>Last Year</option>
                    </select>
                </form>
                <button class="admin-button admin-button--secondary">
                    <i data-lucide="download" style="width:14px;height:14px;"></i> Export
                </button>
            </div>
        </div>

        <!-- KPI Grid -->
        <div class="an-kpi-grid">
            <div class="an-kpi an-kpi--indigo">
                <div class="an-kpi__icon an-kpi__icon--indigo"><i data-lucide="users" style="width:18px;height:18px;"></i></div>
                <span class="an-kpi__label">Active Users</span>
                <span class="an-kpi__value"><?= number_format($summary['totalUsers']) ?></span>
                <span class="an-kpi__sub">Registered platform users</span>
            </div>
            <div class="an-kpi an-kpi--emerald">
                <div class="an-kpi__icon an-kpi__icon--emerald"><i data-lucide="calendar-check" style="width:18px;height:18px;"></i></div>
                <span class="an-kpi__label">Total Sessions</span>
                <span class="an-kpi__value"><?= number_format($summary['totalSessions']) ?></span>
                <span class="an-kpi__sub"><?= $summary['completedSessions'] ?> completed</span>
            </div>
            <div class="an-kpi an-kpi--amber">
                <div class="an-kpi__icon an-kpi__icon--amber"><i data-lucide="clipboard-list" style="width:18px;height:18px;"></i></div>
                <span class="an-kpi__label">Recovery Plans</span>
                <span class="an-kpi__value"><?= number_format($summary['totalPlans']) ?></span>
                <span class="an-kpi__sub">Avg <?= $summary['avgPlanProgress'] ?>% progress</span>
            </div>
            <div class="an-kpi an-kpi--violet">
                <div class="an-kpi__icon an-kpi__icon--violet"><i data-lucide="user-check" style="width:18px;height:18px;"></i></div>
                <span class="an-kpi__label">Counselors</span>
                <span class="an-kpi__value"><?= $summary['verifiedCounselors'] ?></span>
                <span class="an-kpi__sub">Verified &amp; active</span>
            </div>
            <div class="an-kpi an-kpi--rose">
                <div class="an-kpi__icon an-kpi__icon--rose"><i data-lucide="message-circle" style="width:18px;height:18px;"></i></div>
                <span class="an-kpi__label">Community Posts</span>
                <span class="an-kpi__value"><?= $summary['activePosts'] ?></span>
                <span class="an-kpi__sub">Active community content</span>
            </div>
            <div class="an-kpi an-kpi--teal">
                <div class="an-kpi__icon an-kpi__icon--teal"><i data-lucide="activity" style="width:18px;height:18px;"></i></div>
                <span class="an-kpi__label">Check-ins (30d)</span>
                <span class="an-kpi__value"><?= $summary['checkinsLast30'] ?></span>
                <span class="an-kpi__sub">Daily sobriety check-ins</span>
            </div>
            <div class="an-kpi an-kpi--blue">
                <div class="an-kpi__icon an-kpi__icon--blue"><i data-lucide="briefcase" style="width:18px;height:18px;"></i></div>
                <span class="an-kpi__label">Active Job Posts</span>
                <span class="an-kpi__value"><?= $summary['activeJobs'] ?></span>
                <span class="an-kpi__sub">Post-recovery employment</span>
            </div>
            <div class="an-kpi an-kpi--orange">
                <div class="an-kpi__icon an-kpi__icon--orange"><i data-lucide="trending-up" style="width:18px;height:18px;"></i></div>
                <span class="an-kpi__label">Completion Rate</span>
                <span class="an-kpi__value"><?= $summary['totalSessions'] > 0 ? round($summary['completedSessions'] / $summary['totalSessions'] * 100) : 0 ?>%</span>
                <span class="an-kpi__sub">Sessions completed</span>
            </div>
        </div>

        <!-- Row 1: Engagement Bar + Plan Adoption Doughnut -->
        <div class="an-chart-grid">
            <div class="an-card">
                <div class="an-card__header">
                    <span class="an-card__title">User Engagement — Last 6 Months</span>
                    <span class="an-card__badge">Registrations &amp; Sessions</span>
                </div>
                <div class="an-chart-wrap" style="height:280px;">
                    <canvas id="engagementChart"></canvas>
                </div>
            </div>
            <div class="an-card">
                <div class="an-card__header">
                    <span class="an-card__title">Recovery Plan Adoption</span>
                    <span class="an-card__badge">Users</span>
                </div>
                <div class="an-chart-wrap" style="height:280px;">
                    <canvas id="planAdoptionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Row 2: Session Status Pie + Mood Bar + Plan Status Doughnut -->
        <div class="an-chart-grid--3">
            <div class="an-card">
                <div class="an-card__header">
                    <span class="an-card__title">Session Status</span>
                    <span class="an-card__badge">All time</span>
                </div>
                <div class="an-chart-wrap" style="height:220px;">
                    <canvas id="sessionStatusChart"></canvas>
                </div>
            </div>
            <div class="an-card">
                <div class="an-card__header">
                    <span class="an-card__title">Mood Distribution</span>
                    <span class="an-card__badge">Check-ins</span>
                </div>
                <div class="an-chart-wrap" style="height:220px;">
                    <canvas id="moodChart"></canvas>
                </div>
            </div>
            <div class="an-card">
                <div class="an-card__header">
                    <span class="an-card__title">Plan Status Breakdown</span>
                    <span class="an-card__badge">Recovery plans</span>
                </div>
                <div class="an-chart-wrap" style="height:220px;">
                    <canvas id="planStatusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Row 3: Top Counselors Table -->
        <div class="an-card">
            <div class="an-card__header">
                <span class="an-card__title">Top Counselors by Sessions</span>
                <span class="an-card__badge">Verified counselors</span>
            </div>
            <table class="an-cns-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Counselor</th>
                        <th>Specialty</th>
                        <th>Rating</th>
                        <th>Reviews</th>
                        <th>Sessions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topCounselors as $i => $c): ?>
                    <tr>
                        <td><span class="an-badge an-badge--indigo"><?= $i + 1 ?></span></td>
                        <td>
                            <div class="an-cns-name"><?= htmlspecialchars($c['name']) ?></div>
                        </td>
                        <td><span class="an-cns-spec"><?= htmlspecialchars($c['specialty']) ?></span></td>
                        <td>
                            <span class="an-stars">★</span>
                            <strong><?= $c['rating'] ?></strong>
                        </td>
                        <td><?= $c['reviews'] ?></td>
                        <td><span class="an-badge an-badge--emerald"><?= $c['sessionCount'] ?> sessions</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </section>
</main>

<script>
(function () {
    Chart.defaults.font.family = "'Montserrat', sans-serif";
    Chart.defaults.color = '#64748b';

    const INDIGO   = 'rgba(99,102,241,';
    const EMERALD  = 'rgba(16,185,129,';
    const AMBER    = 'rgba(245,158,11,';
    const ROSE     = 'rgba(244,63,94,';
    const VIOLET   = 'rgba(139,92,246,';

    /* 1. Engagement Bar Chart */
    new Chart(document.getElementById('engagementChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($engagementChart['labels']) ?>,
            datasets: [
                {
                    label: 'New Registrations',
                    data: <?= json_encode($engagementChart['newUsers']) ?>,
                    backgroundColor: INDIGO + '0.8)',
                    borderRadius: 6,
                    borderSkipped: false,
                },
                {
                    label: 'Sessions Booked',
                    data: <?= json_encode($engagementChart['sessData']) ?>,
                    backgroundColor: EMERALD + '0.8)',
                    borderRadius: 6,
                    borderSkipped: false,
                },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } },
        },
    });

    /* 2. Plan Adoption Doughnut */
    new Chart(document.getElementById('planAdoptionChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($planAdoptionChart['labels']) ?>,
            datasets: [{ data: <?= json_encode($planAdoptionChart['data']) ?>, backgroundColor: [INDIGO+'0.85)', 'rgba(226,232,240,0.7)'], borderWidth: 2 }],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            cutout: '68%',
        },
    });

    /* 3. Session Status Pie */
    new Chart(document.getElementById('sessionStatusChart'), {
        type: 'pie',
        data: {
            labels: <?= json_encode($sessionStatusChart['labels']) ?>,
            datasets: [{ data: <?= json_encode($sessionStatusChart['data']) ?>, backgroundColor: <?= json_encode($sessionStatusChart['colors']) ?>, borderWidth: 2 }],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 10 } } },
        },
    });

    /* 4. Mood Distribution Bar */
    const moodColors = ['#10b981','#6366f1','#f59e0b','#94a3b8','#f43f5e','#8b5cf6'];
    new Chart(document.getElementById('moodChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($moodChart['labels']) ?>,
            datasets: [{
                label: 'Check-ins',
                data: <?= json_encode($moodChart['data']) ?>,
                backgroundColor: moodColors,
                borderRadius: 6,
                borderSkipped: false,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }, y: { grid: { display: false } } },
        },
    });

    /* 5. Plan Status Doughnut */
    new Chart(document.getElementById('planStatusChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($planStatusChart['labels']) ?>,
            datasets: [{ data: <?= json_encode($planStatusChart['data']) ?>, backgroundColor: <?= json_encode($planStatusChart['colors']) ?>, borderWidth: 2 }],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 10 } } },
            cutout: '60%',
        },
    });
})();
</script>

<?php require_once __DIR__ . '/../common/admin.footer.php'; ?>
</body>
</html>
