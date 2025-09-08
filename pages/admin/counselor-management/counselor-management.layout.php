<?php
$pageTitle = 'Counselor Management';
$pageStyle = ['admin/counselor-management'];
require_once __DIR__ . '/../common/admin.html.head.php';
?>
<main class="admin-main-container">
    <?php require_once __DIR__ . '/../common/admin.sidebar.php'; ?>
    <section class="admin-main-content">
        <h1>Counselor Management</h1>

        <div class="admin-sub-container-1" style="justify-content: flex-end; margin-bottom: -0.5rem;">
            <a href="/admin/counselor-payouts" class="admin-button admin-button--secondary">
                <i data-lucide="banknote" stroke-width="1" style="width:1rem;height:1rem;"></i>
                <span class="button-text">Counselor Payouts</span>
            </a>
        </div>

        <div class="admin-sub-container-1">
            <div class="admin-summary-card"><div class="admin-summary-card-content"><p class="admin-summary-card-title">Avg Sessions per Counselor</p><p class="admin-summary-card-info"><?= $stats['avgSessionsPerCounselor'] > 0 ? number_format($stats['avgSessionsPerCounselor'], 1) : 'N/A' ?></p><p class="admin-summary-card-subinfo">Avg completed sessions</p></div></div>
            <div class="admin-summary-card"><div class="admin-summary-card-content"><p class="admin-summary-card-title">Total Sessions</p><p class="admin-summary-card-info"><?= $stats['totalSessions'] ?></p><p class="admin-summary-card-subinfo">Sessions completed</p></div></div>
            <div class="admin-summary-card"><div class="admin-summary-card-content"><p class="admin-summary-card-title">Active Counselors</p><p class="admin-summary-card-info"><?= $stats['activeCounselorsCount'] ?></p><p class="admin-summary-card-subinfo">Currently active</p></div></div>
            <div class="admin-summary-card"><div class="admin-summary-card-content"><p class="admin-summary-card-title">Pending Applications</p><p class="admin-summary-card-info"><?= $stats['pendingApplicationsCount'] ?></p><p class="admin-summary-card-subinfo">Awaiting review</p></div></div>
        </div>

        <div class="admin-sub-container-2">
            <div class="admin-sub-container-1" style="justify-content: space-between; align-items: center;">
                <h2>Counselor Applications Queue</h2>
                <div class="admin-sub-container-1">
                    <?php foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label): ?>
                        <a href="/admin/counselor-management?appStatus=<?= $value ?>" class="admin-button <?= $filters['appStatus'] === $value ? 'admin-button--primary' : 'admin-button--secondary' ?>"><span class="button-text"><?= $label ?></span></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="admin-table-container">
                <table class="admin-table">
                    <thead class="admin-table-header"><tr class="admin-table-row"><th class="admin-table-th">Name</th><th class="admin-table-th">Email</th><th class="admin-table-th">Specialization</th><th class="admin-table-th">Experience</th><th class="admin-table-th">Application Date</th><th class="admin-table-th">Status</th><th class="admin-table-th">Actions</th></tr></thead>
                    <tbody class="admin-table-body">
                    <?php if ($applications === []): ?><tr class="admin-table-row"><td class="admin-table-td" colspan="7">No applications found.</td></tr><?php endif; ?>
                    <?php foreach ($applications as $index => $app): ?>
                        <tr class="admin-table-row <?= $index % 2 === 0 ? 'admin-table-row--even' : 'admin-table-row--odd' ?>">
                            <td class="admin-table-td"><strong><?= htmlspecialchars($app['fullName']) ?></strong><?php if ($app['title'] !== ''): ?><br><small><?= htmlspecialchars($app['title']) ?></small><?php endif; ?></td>
                            <td class="admin-table-td"><?= htmlspecialchars($app['email']) ?></td>
                            <td class="admin-table-td"><?= htmlspecialchars($app['specialty']) ?></td>
                            <td class="admin-table-td"><?= $app['experienceYears'] !== null ? $app['experienceYears'] . ' years' : '-' ?></td>
                            <td class="admin-table-td"><?= htmlspecialchars($app['applicationDate']) ?></td>
                            <td class="admin-table-td"><?= htmlspecialchars(ucfirst($app['status'])) ?></td>
                            <td class="admin-table-td admin-table-td--action">
                                <div class="admin-table-actions">
                                    <a href="/admin/counselor-management/application-view?id=<?= (int) $app['applicationId'] ?>" class="admin-button admin-button--ghost">View</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination-container">
                <?php
                    $pagination = $applicationsPagination;
                    $basePath = '/admin/counselor-management';
                    $query = $filters;
                    require __DIR__ . '/../common/admin.pagination.php';
                ?>
            </div>
        </div>

        <div class="admin-sub-container-2">
            <div class="admin-sub-container-1" style="justify-content: space-between; align-items: center;">
                <h2>Counselor Directory</h2>
                <form method="GET" action="/admin/counselor-management" class="content-management-filters">
                    <input type="hidden" name="appStatus" value="<?= htmlspecialchars($filters['appStatus']) ?>">
                    <div class="content-management-filters__dropdowns">
                        <select name="specialization" class="admin-dropdown" onchange="this.form.submit()">
                            <?php foreach (['all' => 'All Specializations', 'Addiction Counseling' => 'Addiction Counseling', 'Mental Health' => 'Mental Health', 'Trauma Therapy' => 'Trauma Therapy'] as $value => $label): ?>
                                <option value="<?= htmlspecialchars($value) ?>" <?= $filters['specialization'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="counselorStatus" class="admin-dropdown" onchange="this.form.submit()">
                            <?php foreach (['all' => 'All Status', 'active' => 'Active', 'inactive' => 'Inactive'] as $value => $label): ?>
                                <option value="<?= htmlspecialchars($value) ?>" <?= $filters['counselorStatus'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead class="admin-table-header"><tr class="admin-table-row"><th class="admin-table-th">Counselor</th><th class="admin-table-th">Specialization</th><th class="admin-table-th">Languages</th><th class="admin-table-th">Rating</th><th class="admin-table-th">Reviews</th><th class="admin-table-th">Status</th><th class="admin-table-th">Actions</th></tr></thead>
                    <tbody class="admin-table-body">
                    <?php if ($counselors === []): ?><tr class="admin-table-row"><td class="admin-table-td" colspan="7">No counselors found.</td></tr><?php endif; ?>
                    <?php foreach ($counselors as $index => $counselor): ?>
                        <tr class="admin-table-row <?= $index % 2 === 0 ? 'admin-table-row--even' : 'admin-table-row--odd' ?>">
                            <td class="admin-table-td"><strong><?= htmlspecialchars($counselor['fullName']) ?></strong><br><small><?= htmlspecialchars($counselor['email']) ?></small></td>
                            <td class="admin-table-td"><?= htmlspecialchars($counselor['specialty']) ?></td>
                            <td class="admin-table-td"><?= htmlspecialchars($counselor['languagesSpoken'] ?: '-') ?></td>
                            <td class="admin-table-td"><?= $counselor['rating'] > 0 ? number_format($counselor['rating'], 1) : '-' ?></td>
                            <td class="admin-table-td"><?= $counselor['totalReviews'] ?></td>
                            <td class="admin-table-td"><?= $counselor['active'] ? 'Active' : 'Inactive' ?></td>
                            <td class="admin-table-td admin-table-td--action">
                                <a href="/admin/counselor-management/counselor-view?id=<?= (int) $counselor['counselorId'] ?>" class="admin-button admin-button--ghost">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="pagination-container">
                <?php
                    $pagination = $counselorsPagination;
                    $basePath = '/admin/counselor-management';
                    $query = $filters;
                    require __DIR__ . '/../common/admin.pagination.php';
                ?>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../common/admin.footer.php'; ?>
</body>
</html>
