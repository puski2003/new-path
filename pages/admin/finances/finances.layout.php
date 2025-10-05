<?php
$pageTitle = 'Finances';
require_once __DIR__ . '/../common/admin.html.head.php';

// Status badge helper
function finBadge(string $status): string {
    $map = [
        'completed'  => ['#dcfce7','#16a34a'],
        'pending'    => ['#fef9c3','#b45309'],
        'processing' => ['#dbeafe','#2563eb'],
        'failed'     => ['#fee2e2','#dc2626'],
        'refunded'   => ['#f3e8ff','#7c3aed'],
        'disputed'   => ['#fff7ed','#d97706'],
        'under_review'=> ['#e0f2fe','#0369a1'],
        'approved'   => ['#dcfce7','#15803d'],
        'rejected'   => ['#fee2e2','#dc2626'],
        'resolved'   => ['#f0fdf4','#166534'],
    ];
    [$bg, $fg] = $map[$status] ?? ['#f1f5f9','#475569'];
    $label = ucwords(str_replace('_', ' ', $status));
    return "<span style=\"display:inline-flex;align-items:center;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600;background:{$bg};color:{$fg};\">{$label}</span>";
}
?>
<style>
/* ── Finances page ───────────────────────────────────────── */
.fin-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}
.fin-kpi {
    background: #fff;
    border-radius: 12px;
    padding: 20px 18px;
    box-shadow: 0 1px 4px rgba(0,0,0,.07);
    display: flex;
    flex-direction: column;
    gap: 6px;
    border-top: 3px solid transparent;
}
.fin-kpi--green  { border-color: #10b981; }
.fin-kpi--indigo { border-color: #6366f1; }
.fin-kpi--amber  { border-color: #f59e0b; }
.fin-kpi--rose   { border-color: #f43f5e; }
.fin-kpi--blue   { border-color: #3b82f6; }
.fin-kpi--violet { border-color: #8b5cf6; }
.fin-kpi--teal   { border-color: #14b8a6; }
.fin-kpi--orange { border-color: #f97316; }
.fin-kpi__icon {
    width: 36px; height: 36px; border-radius: 8px;
    display:flex;align-items:center;justify-content:center; margin-bottom:4px;
}
.fin-kpi__icon--green  { background:#ecfdf5; color:#10b981; }
.fin-kpi__icon--indigo { background:#eef2ff; color:#6366f1; }
.fin-kpi__icon--amber  { background:#fffbeb; color:#f59e0b; }
.fin-kpi__icon--rose   { background:#fff1f2; color:#f43f5e; }
.fin-kpi__icon--blue   { background:#eff6ff; color:#3b82f6; }
.fin-kpi__icon--violet { background:#f5f3ff; color:#8b5cf6; }
.fin-kpi__icon--teal   { background:#f0fdfa; color:#14b8a6; }
.fin-kpi__icon--orange { background:#fff7ed; color:#f97316; }
.fin-kpi__label { font-size:12px; color:#64748b; font-weight:500; }
.fin-kpi__value { font-size:22px; font-weight:700; color:#1e293b; line-height:1.2; }
.fin-kpi__sub   { font-size:11px; color:#94a3b8; }

/* Charts grid */
.fin-chart-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 16px;
}
.fin-chart-grid--3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}
.fin-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 4px rgba(0,0,0,.07);
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.fin-card__header {
    display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;
}
.fin-card__title { font-size:15px; font-weight:600; color:#1e293b; }
.fin-card__badge { font-size:11px; padding:3px 10px; border-radius:99px; background:#f1f5f9; color:#64748b; }
.fin-chart-wrap { position:relative; width:100%; }

/* Section headers */
.fin-section-title {
    display:flex; justify-content:space-between; align-items:center;
    flex-wrap:wrap; gap:12px;
    background:#fff; border-radius:12px; padding:14px 20px;
    box-shadow:0 1px 4px rgba(0,0,0,.07);
}
.fin-section-title h2 { font-size:16px; font-weight:600; color:#1e293b; margin:0; }
.fin-section-title__actions { display:flex; gap:8px; align-items:center; }

/* Tables */
.fin-table-wrap { background:#fff; border-radius:12px; padding:0 0 4px; box-shadow:0 1px 4px rgba(0,0,0,.07); overflow:hidden; }
.fin-table { width:100%; border-collapse:collapse; font-size:13px; }
.fin-table th {
    text-align:left; font-weight:600; color:#64748b;
    font-size:11px; text-transform:uppercase; letter-spacing:.5px;
    padding:12px 16px; border-bottom:1px solid #f1f5f9; background:#fafafa;
}
.fin-table td {
    padding:11px 16px; border-bottom:1px solid #f8fafc; color:#334155; vertical-align:middle;
}
.fin-table tr:last-child td { border-bottom:none; }
.fin-table tr:hover td { background:#fafbff; }
.fin-txn-id { font-family:monospace; font-size:11px; color:#94a3b8; }
.fin-amount  { font-weight:600; color:#1e293b; }

/* Filter row */
.fin-filter-row { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }

/* Page header */
.fin-page-header {
    display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;
    background:#fff; border-radius:12px; padding:16px 20px; box-shadow:0 1px 4px rgba(0,0,0,.07);
}
.fin-page-header h1 { font-size:20px; font-weight:700; color:#1e293b; margin:0; }

/* Tabs */
.fin-tabs { display:flex; gap:0; border-bottom:2px solid #f1f5f9; }
.fin-tab {
    padding:10px 18px; font-size:13px; font-weight:600;
    color:#64748b; cursor:pointer; border:none; background:none;
    border-bottom:2px solid transparent; margin-bottom:-2px; transition:all .2s;
}
.fin-tab.active { color:#6366f1; border-bottom-color:#6366f1; }
.fin-tab-panel  { display:none; }
.fin-tab-panel.active { display:block; }

@media (max-width:900px) {
    .fin-chart-grid, .fin-chart-grid--3 { grid-template-columns:1fr; }
    .fin-kpi-grid { grid-template-columns:repeat(2,1fr); }
}
</style>

<main class="admin-main-container">
    <?php require_once __DIR__ . '/../common/admin.sidebar.php'; ?>

    <section class="admin-main-content">

        <!-- Page Header -->
        <div class="fin-page-header">
            <h1>Finances</h1>
            <div style="display:flex;gap:8px;">
                <button class="admin-button admin-button--secondary">
                    <i data-lucide="download" style="width:14px;height:14px;"></i> Export CSV
                </button>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="fin-kpi-grid">
            <div class="fin-kpi fin-kpi--green">
                <div class="fin-kpi__icon fin-kpi__icon--green"><i data-lucide="trending-up" style="width:18px;height:18px;"></i></div>
                <span class="fin-kpi__label">Total Revenue</span>
                <span class="fin-kpi__value">LKR <?= number_format($summary['totalRevenue'], 2) ?></span>
                <span class="fin-kpi__sub">All time completed</span>
            </div>
            <div class="fin-kpi fin-kpi--indigo">
                <div class="fin-kpi__icon fin-kpi__icon--indigo"><i data-lucide="bar-chart-3" style="width:18px;height:18px;"></i></div>
                <span class="fin-kpi__label">This Month</span>
                <span class="fin-kpi__value">LKR <?= number_format($summary['revenueThisMonth'], 2) ?></span>
                <span class="fin-kpi__sub">Current month revenue</span>
            </div>
            <div class="fin-kpi fin-kpi--teal">
                <div class="fin-kpi__icon fin-kpi__icon--teal"><i data-lucide="calendar" style="width:18px;height:18px;"></i></div>
                <span class="fin-kpi__label">Today</span>
                <span class="fin-kpi__value">LKR <?= number_format($summary['revenueToday'], 2) ?></span>
                <span class="fin-kpi__sub">Revenue today</span>
            </div>
            <div class="fin-kpi fin-kpi--amber">
                <div class="fin-kpi__icon fin-kpi__icon--amber"><i data-lucide="receipt" style="width:18px;height:18px;"></i></div>
                <span class="fin-kpi__label">Avg Payment</span>
                <span class="fin-kpi__value">LKR <?= number_format($summary['avgPayment'], 2) ?></span>
                <span class="fin-kpi__sub">Per completed transaction</span>
            </div>
            <div class="fin-kpi fin-kpi--blue">
                <div class="fin-kpi__icon fin-kpi__icon--blue"><i data-lucide="credit-card" style="width:18px;height:18px;"></i></div>
                <span class="fin-kpi__label">Sessions Paid</span>
                <span class="fin-kpi__value"><?= number_format($summary['sessionsPaid']) ?></span>
                <span class="fin-kpi__sub">Completed transactions</span>
            </div>
            <div class="fin-kpi fin-kpi--rose">
                <div class="fin-kpi__icon fin-kpi__icon--rose"><i data-lucide="alert-circle" style="width:18px;height:18px;"></i></div>
                <span class="fin-kpi__label">Pending Refunds</span>
                <span class="fin-kpi__value"><?= $summary['pendingRefunds'] ?></span>
                <span class="fin-kpi__sub">Awaiting resolution</span>
            </div>
            <div class="fin-kpi fin-kpi--orange">
                <div class="fin-kpi__icon fin-kpi__icon--orange"><i data-lucide="x-circle" style="width:18px;height:18px;"></i></div>
                <span class="fin-kpi__label">Failed Transactions</span>
                <span class="fin-kpi__value"><?= $summary['failedTxns'] ?></span>
                <span class="fin-kpi__sub">Require attention</span>
            </div>
            <div class="fin-kpi fin-kpi--violet">
                <div class="fin-kpi__icon fin-kpi__icon--violet"><i data-lucide="send" style="width:18px;height:18px;"></i></div>
                <span class="fin-kpi__label">Total Payouts</span>
                <span class="fin-kpi__value">LKR <?= number_format($summary['totalPayouts'], 2) ?></span>
                <span class="fin-kpi__sub">Paid to counselors</span>
            </div>
        </div>

        <!-- Charts Row 1: Revenue Trend + Transaction Status -->
        <div class="fin-chart-grid">
            <div class="fin-card">
                <div class="fin-card__header">
                    <span class="fin-card__title">Monthly Revenue Trend</span>
                    <span class="fin-card__badge">Last 6 months</span>
                </div>
                <div class="fin-chart-wrap" style="height:260px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            <div class="fin-card">
                <div class="fin-card__header">
                    <span class="fin-card__title">Transaction Status</span>
                    <span class="fin-card__badge">All time</span>
                </div>
                <div class="fin-chart-wrap" style="height:260px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Charts Row 2: Payment Types + Counselor Revenue -->
        <div class="fin-chart-grid--3">
            <div class="fin-card">
                <div class="fin-card__header">
                    <span class="fin-card__title">Payment Types</span>
                    <span class="fin-card__badge">By volume</span>
                </div>
                <div class="fin-chart-wrap" style="height:220px;">
                    <canvas id="paymentTypeChart"></canvas>
                </div>
            </div>
            <div class="fin-card" style="grid-column: span 2;">
                <div class="fin-card__header">
                    <span class="fin-card__title">Revenue by Counselor</span>
                    <span class="fin-card__badge">LKR</span>
                </div>
                <div class="fin-chart-wrap" style="height:220px;">
                    <canvas id="counselorRevChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tabbed Section: Transactions / Disputes / Payouts -->
        <div class="fin-card" style="gap:0;">
            <div style="padding: 4px 4px 0;">
                <div class="fin-tabs">
                    <button class="fin-tab active" data-tab="transactions">Transaction Logs</button>
                    <button class="fin-tab" data-tab="disputes">Refunds &amp; Disputes</button>
                    <button class="fin-tab" data-tab="payouts">Counselor Payouts</button>
                </div>
            </div>

            <!-- Transactions Tab -->
            <div id="tab-transactions" class="fin-tab-panel active">
                <div style="padding:16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                    <span style="font-size:14px;font-weight:600;color:#1e293b;">All Transactions</span>
                    <form method="GET" class="fin-filter-row">
                        <input type="hidden" name="disputeStatus" value="<?= htmlspecialchars($filters['disputeStatus']) ?>">
                        <input type="text" name="search" placeholder="Search by ID, user or email…"
                               value="<?= htmlspecialchars($search) ?>"
                               style="border:1px solid #e2e8f0;border-radius:8px;padding:7px 12px;font-size:13px;min-width:220px;">
                        <button class="admin-button admin-button--secondary">Search</button>
                    </form>
                </div>
                <div style="overflow-x:auto;">
                    <table class="fin-table">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>User</th>
                                <th>Counselor</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $tx): ?>
                            <tr>
                                <td><span class="fin-txn-id"><?= htmlspecialchars($tx['transactionId']) ?></span></td>
                                <td><?= htmlspecialchars($tx['userName']) ?></td>
                                <td><?= htmlspecialchars($tx['counselorName']) ?></td>
                                <td><?= htmlspecialchars($tx['date']) ?></td>
                                <td><?= htmlspecialchars($tx['paymentType']) ?></td>
                                <td class="fin-amount">LKR <?= htmlspecialchars($tx['amount']) ?></td>
                                <td><?= finBadge($tx['status']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div style="padding:12px 16px;">
                    <?php
                    $pagination = $transactionsPagination;
                    $basePath   = '/admin/finances';
                    $query      = array_merge($filters, ['search' => $search]);
                    require __DIR__ . '/../common/admin.pagination.php';
                    ?>
                </div>
            </div>

            <!-- Disputes Tab -->
            <div id="tab-disputes" class="fin-tab-panel">
                <div style="padding:16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                    <span style="font-size:14px;font-weight:600;color:#1e293b;">Refunds &amp; Disputes</span>
                    <form method="GET" class="fin-filter-row">
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                        <select name="disputeStatus" class="admin-dropdown" style="font-size:13px;">
                            <option value="all">All Status</option>
                            <?php foreach (['pending','under_review','approved','rejected','resolved'] as $ds): ?>
                            <option value="<?= $ds ?>" <?= $filters['disputeStatus'] === $ds ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ',$ds)) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="disputeIssue" style="border:1px solid #e2e8f0;border-radius:8px;padding:7px 12px;font-size:13px;"
                               placeholder="Issue type…" value="<?= htmlspecialchars($filters['disputeIssue'] === 'allIssues' ? '' : $filters['disputeIssue']) ?>">
                        <button class="admin-button admin-button--secondary">Filter</button>
                    </form>
                </div>
                <div style="overflow-x:auto;">
                    <table class="fin-table">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>User</th>
                                <th>Counselor</th>
                                <th>Amount</th>
                                <th>Issue</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($disputes as $d): ?>
                            <tr>
                                <td><span class="fin-txn-id"><?= htmlspecialchars($d['transactionId']) ?></span></td>
                                <td><?= htmlspecialchars($d['userName']) ?></td>
                                <td><?= htmlspecialchars($d['counselorName']) ?></td>
                                <td class="fin-amount">LKR <?= htmlspecialchars($d['amount']) ?></td>
                                <td><?= htmlspecialchars($d['issue']) ?></td>
                                <td><?= finBadge($d['status']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div style="padding:12px 16px;">
                    <?php
                    $pagination = $disputesPagination;
                    $basePath   = '/admin/finances';
                    $query      = array_merge($filters, ['search' => $search]);
                    require __DIR__ . '/../common/admin.pagination.php';
                    ?>
                </div>
            </div>

            <!-- Payouts Tab -->
            <div id="tab-payouts" class="fin-tab-panel">
                <div style="padding:16px;">
                    <span style="font-size:14px;font-weight:600;color:#1e293b;">Counselor Payouts</span>
                </div>
                <div style="overflow-x:auto;">
                    <table class="fin-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Counselor</th>
                                <th>Period</th>
                                <th>Sessions</th>
                                <th>Amount</th>
                                <th>Paid At</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payouts)): ?>
                            <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:24px;">No payout records found.</td></tr>
                            <?php else: foreach ($payouts as $p): ?>
                            <tr>
                                <td><span class="fin-txn-id">#<?= $p['id'] ?></span></td>
                                <td><?= htmlspecialchars($p['counselorName']) ?></td>
                                <td style="font-size:12px;color:#64748b;"><?= htmlspecialchars($p['period']) ?></td>
                                <td><?= $p['sessions'] ?></td>
                                <td class="fin-amount">LKR <?= htmlspecialchars($p['amount']) ?></td>
                                <td style="font-size:12px;color:#64748b;"><?= htmlspecialchars($p['paidAt']) ?></td>
                                <td><?= finBadge($p['status']) ?></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </section>
</main>

<script>
(function () {
    Chart.defaults.font.family = "'Montserrat', sans-serif";
    Chart.defaults.color = '#64748b';

    /* 1. Revenue Trend – Area Line */
    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($revenueChart['labels']) ?>,
            datasets: [{
                label: 'Revenue (LKR)',
                data: <?= json_encode($revenueChart['revenue']) ?>,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,0.10)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#6366f1',
                pointRadius: 5,
                pointHoverRadius: 7,
            }],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => 'LKR ' + ctx.parsed.y.toLocaleString('en-LK', { minimumFractionDigits: 2 }),
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: { callback: v => 'LKR ' + (v / 1000).toFixed(0) + 'k' },
                },
                x: { grid: { display: false } },
            },
        },
    });

    /* 2. Transaction Status – Doughnut */
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($statusChart['labels']) ?>,
            datasets: [{ data: <?= json_encode($statusChart['data']) ?>, backgroundColor: <?= json_encode($statusChart['colors']) ?>, borderWidth: 2 }],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 12 } } },
            cutout: '62%',
        },
    });

    /* 3. Payment Types – Pie */
    new Chart(document.getElementById('paymentTypeChart'), {
        type: 'pie',
        data: {
            labels: <?= json_encode($paymentTypeChart['labels']) ?>,
            datasets: [{
                data: <?= json_encode($paymentTypeChart['counts']) ?>,
                backgroundColor: ['#6366f1','#10b981','#f59e0b','#f43f5e','#8b5cf6'],
                borderWidth: 2,
            }],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 10 } } },
        },
    });

    /* 4. Counselor Revenue – Horizontal Bar */
    new Chart(document.getElementById('counselorRevChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($counselorRevChart['labels']) ?>,
            datasets: [{
                label: 'Revenue (LKR)',
                data: <?= json_encode($counselorRevChart['data']) ?>,
                backgroundColor: 'rgba(99,102,241,0.8)',
                borderRadius: 6,
                borderSkipped: false,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => 'LKR ' + ctx.parsed.x.toLocaleString('en-LK', { minimumFractionDigits: 2 }) } },
            },
            scales: {
                x: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { callback: v => 'LKR ' + (v/1000).toFixed(0) + 'k' } },
                y: { grid: { display: false } },
            },
        },
    });

    /* Tab switcher */
    document.querySelectorAll('.fin-tab').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.fin-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.fin-tab-panel').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
        });
    });
})();
</script>

<?php require_once __DIR__ . '/../common/admin.footer.php'; ?>
</body>
</html>
