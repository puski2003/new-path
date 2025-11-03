<?php
$pageTitle = 'System & Security';
require_once __DIR__ . '/../common/admin.html.head.php';
?>
<main class="admin-main-container">
    <?php require_once __DIR__ . '/../common/admin.sidebar.php'; ?>
    <section class="admin-main-content">
        <h1>System &amp; Security</h1>
        <div class="admin-sub-container-2">
            <div class="admin-sub-container-1" style="justify-content: space-between; align-items: center;"><h2>Audit Log Timeline</h2><button type="button" class="admin-button admin-button--primary">Export Log</button></div>
            <form method="GET" class="admin-sub-container-1"><input type="text" name="action" placeholder="Action" value="<?= htmlspecialchars($filters['action'] === 'all' ? '' : $filters['action']) ?>"><input type="date" name="startDate" value="<?= htmlspecialchars($filters['startDate']) ?>"><input type="date" name="endDate" value="<?= htmlspecialchars($filters['endDate']) ?>"><button class="admin-button admin-button--secondary">Filter</button></form>
            <table class="admin-table"><thead class="admin-table-header"><tr class="admin-table-row"><th class="admin-table-th">Date/Time</th><th class="admin-table-th">Action</th><th class="admin-table-th">Admin Name</th><th class="admin-table-th">Affected Resource</th><th class="admin-table-th">Status</th></tr></thead><tbody class="admin-table-body"><?php foreach ($auditLogs as $index => $log): ?><tr class="admin-table-row <?= $index % 2 === 0 ? 'admin-table-row--even' : 'admin-table-row--odd' ?>"><td class="admin-table-td"><?= htmlspecialchars($log['dateTime']) ?></td><td class="admin-table-td"><?= htmlspecialchars($log['action']) ?></td><td class="admin-table-td"><?= htmlspecialchars($log['adminName']) ?></td><td class="admin-table-td"><?= htmlspecialchars($log['affectedResource']) ?></td><td class="admin-table-td"><?= htmlspecialchars($log['status']) ?></td></tr><?php endforeach; ?></tbody></table>
        </div>
        <div class="admin-sub-container-2"><h2>Compliance &amp; Policies</h2><div class="policy-cards-grid"><div class="admin-data-card"><h3>Privacy Notice</h3><p>Last updated Jan 15, 2026</p></div><div class="admin-data-card"><h3>Terms of Use</h3><p>Last updated Jan 10, 2026</p></div><div class="admin-data-card"><h3>Notification Templates</h3><p>Enabled</p></div></div></div>
        <div class="admin-sub-container-2">
            <h2>Suspicious Activity Dashboard</h2>
            <div class="admin-sub-container-1" style="align-items: stretch;">
                <div class="admin-data-card" style="height: 340px; flex: 2;">
                    <h3>Suspicious Login Attempts by Date</h3>
                    <canvas id="loginAttemptsChart" style="width:100%;height:260px;"></canvas>
                </div>
                <div class="admin-data-card">
                    <h3>Security Alerts</h3>
                    <p>Critical: Multiple failed login attempts from a single IP.</p>
                    <p>Warning: Unusual device activity detected.</p>
                    <p>Info: Payment anomaly detected.</p>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
(function () {
    const labels = <?= json_encode($loginAttemptsChart['labels']) ?>;
    const counts = <?= json_encode($loginAttemptsChart['counts']) ?>;

    const ctx = document.getElementById('loginAttemptsChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Failed Login Attempts',
                data: counts,
                backgroundColor: 'rgba(239,68,68,0.7)',
                borderColor: 'rgba(239,68,68,1)',
                borderWidth: 1.5,
                borderRadius: 5,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        },
    });
})();
</script>

<?php require_once __DIR__ . '/../common/admin.footer.php'; ?>
</body>
</html>
