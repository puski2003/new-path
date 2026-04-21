<?php
$pageTitle = 'Dashboard Overview';
$pageStyle = ['admin/dashboard'];
require_once __DIR__ . '/../common/admin.html.head.php';
?>
    <main class="admin-layout">

        <?php require_once __DIR__ . '/../common/admin.sidebar.php'; ?>

        <section class="admin-content">
            <div class="page-header">
                <h1>Dashboard Overview</h1>
            </div>

            <div class="summary-cards">
                <div class="summary-card summary-card--growth">
                    <div class="summary-card__icon">
                         <i data-lucide="user-plus"></i>
                    </div>
                    <div class="summary-card__body">
                        <p class="summary-card__label">Total Active Users</p>
                        <p class="summary-card__value"><?= number_format($data['totalUsers']) ?></p>
                    </div>
                </div>

                <div class="summary-card summary-card--pending">
                    <div class="summary-card__icon">
                        <i data-lucide="clock"></i>
                    </div>
                    <div class="summary-card__body">
                        <p class="summary-card__label">Pending Applications</p>
                        <p class="summary-card__value"><?= $data['pendingApplications'] ?></p>
                        <p class="summary-card__sub">Counselor applications</p>
                    </div>
                </div>

                <div class="summary-card summary-card--upcoming">
                    <div class="summary-card__icon">
                        <i data-lucide="calendar"></i>
                    </div>
                    <div class="summary-card__body">
                        <p class="summary-card__label">Upcoming Sessions</p>
                        <p class="summary-card__value"><?= $data['upcomingSessions'] ?></p>
                        <p class="summary-card__sub">Next 24 hours</p>
                    </div>
                </div>

                <div class="summary-card">
                    
                    <div class="summary-card__body">
                        <p class="summary-card__label">Revenue Today</p>
                        <p class="summary-card__value">LKR <?= number_format($data['revenueToday'], 2) ?></p>
                    </div>
                </div>
            </div>

            <div class="data-row">
                <div class="data-card data-card--wide" style="height: 340px;">
                    <h2>User Growth Over Time</h2>
                    <canvas id="dashUserGrowthChart" style="width:100%;height:260px;"></canvas>
                </div>
                <div class="data-card" style="height: 340px;">
                    <h2>Recovery Plan Adoption</h2>
                    <canvas id="dashPlanAdoptionChart" style="width:100%;height:260px;"></canvas>
                </div>
            </div>

            <div class="data-row">
                <div class="data-card data-card--wide" style="height: 340px;">
                    <h2>Monthly Revenue Trend</h2>
                    <canvas id="dashRevenueChart" style="width:100%;height:260px;"></canvas>
                </div>

                <div class="data-card">
                    <h2>Quick Actions</h2>
                    <a href="/admin/user-management" class="btn btn--ghost btn--full">
                        <i data-lucide="circle-user"></i> User Management
                    </a>
                    <a href="/admin/content-management" class="btn btn--ghost btn--full">
                         <i data-lucide="file-text"></i> Content Management
                    </a>
                    <a href="/admin/finances" class="btn btn--ghost btn--full">
                        <i data-lucide="indian-rupee"></i> Financials
                    </a>
                    <a href="/admin/analytics" class="btn btn--ghost btn--full">
                        <i data-lucide="bar-chart-2"></i> Analytics
                    </a>
                </div>
            </div>

        </section>
    </main>

<script>
(function () {
    const labels      = <?= json_encode($chartData['labels']) ?>;
    const userGrowth  = <?= json_encode($chartData['userGrowth']) ?>;
    const planAdopt   = <?= json_encode($chartData['planAdoption']) ?>;

    // Line chart — User Growth
    const growthCtx = document.getElementById('dashUserGrowthChart');
    if (growthCtx) {
        new Chart(growthCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'New Users',
                    data: userGrowth,
                    borderColor: 'rgba(99,102,241,1)',
                    backgroundColor: 'rgba(99,102,241,0.12)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(99,102,241,1)',
                    pointRadius: 5,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        });
    }

    // Doughnut chart — Plan Adoption
    const planCtx = document.getElementById('dashPlanAdoptionChart');
    if (planCtx) {
        new Chart(planCtx, {
            type: 'doughnut',
            data: {
                labels: ['With Plan', 'Without Plan'],
                datasets: [{
                    data: planAdopt,
                    backgroundColor: ['rgba(99,102,241,0.85)', 'rgba(209,213,219,0.6)'],
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                cutout: '65%',
            },
        });
    }

    // Line chart — Monthly Revenue Trend
    const revenueLabels = <?= json_encode($revenueChart['labels']) ?>;
    const revenueData   = <?= json_encode($revenueChart['revenue']) ?>;
    const revCtx = document.getElementById('dashRevenueChart');
    if (revCtx) {
        new Chart(revCtx, {
            type: 'line',
            data: {
                labels: revenueLabels,
                datasets: [{
                    label: 'Revenue (LKR)',
                    data: revenueData,
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
                responsive: true,
                maintainAspectRatio: false,
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
    }
})();
</script>

    <?php require_once __DIR__ . '/../common/admin.footer.php'; ?>
</body>

</html>
