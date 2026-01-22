<?php
/* ── status badge helper (uses existing admin-table-badge classes) ── */
function payoutStatusBadge(string $status): string {
    $map = [
        'completed'  => 'admin-table-badge--success',
        'pending'    => 'admin-table-badge--warning',
        'processing' => 'admin-table-badge--info',
        'failed'     => 'admin-table-badge--error',
    ];
    $cls   = $map[$status] ?? 'admin-table-badge--default';
    $label = $status === 'completed' ? 'Paid' : ucfirst($status);
    return '<span class="admin-table-badge ' . $cls . '">' . htmlspecialchars($label) . '</span>';
}

$pageTitle   = 'Counselor Payouts';
$pageScripts = ['/assets/js/admin/counselor-payouts.js'];
require_once __DIR__ . '/../common/admin.html.head.php';
?>
<main class="admin-main-container">
    <?php require_once __DIR__ . '/../common/admin.sidebar.php'; ?>

    <section class="admin-main-content">
        <h1>Counselor Payouts</h1>

        <!-- Summary cards -->
        <div class="admin-sub-container-1">
            <div class="admin-summary-card">
                <div class="admin-summary-card-content">
                    <p class="admin-summary-card-title">Pending Payout</p>
                    <p class="admin-summary-card-info">LKR <?= number_format($summary['pendingAmount'], 2) ?></p>
                </div>
            </div>
            <div class="admin-summary-card">
                <div class="admin-summary-card-content">
                    <p class="admin-summary-card-title">Pending Count</p>
                    <p class="admin-summary-card-info"><?= $summary['pendingCount'] ?></p>
                </div>
            </div>
            <div class="admin-summary-card">
                <div class="admin-summary-card-content">
                    <p class="admin-summary-card-title">Total Paid Out</p>
                    <p class="admin-summary-card-info">LKR <?= number_format($summary['paidAmount'], 2) ?></p>
                </div>
            </div>
            <div class="admin-summary-card">
                <div class="admin-summary-card-content">
                    <p class="admin-summary-card-title">Payouts Completed</p>
                    <p class="admin-summary-card-info"><?= $summary['paidCount'] ?></p>
                </div>
            </div>
        </div>

        <!-- Filters + table -->
        <div class="admin-sub-container-2">
            <form method="GET" class="admin-sub-container-1" style="justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <h2>Payout Records</h2>
                <div class="admin-sub-container-1" style="gap: 8px; flex-wrap: wrap;">
                    <select name="status" class="admin-dropdown">
                        <option value="all"        <?= ($filters['status'] ?? 'all') === 'all'        ? 'selected' : '' ?>>All Status</option>
                        <option value="pending"    <?= ($filters['status'] ?? '') === 'pending'    ? 'selected' : '' ?>>Pending</option>
                        <option value="processing" <?= ($filters['status'] ?? '') === 'processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="completed"  <?= ($filters['status'] ?? '') === 'completed'  ? 'selected' : '' ?>>Paid</option>
                        <option value="failed"     <?= ($filters['status'] ?? '') === 'failed'     ? 'selected' : '' ?>>Failed</option>
                    </select>
                    <input type="text" name="search"
                           value="<?= htmlspecialchars($search) ?>"
                           placeholder="Search by counselor name / email"
                           class="admin-search-bar">
                    <button class="admin-button admin-button--secondary" type="submit">Filter</button>
                </div>
            </form>

            <table class="admin-table">
                <thead class="admin-table-header">
                    <tr class="admin-table-row">
                        <th class="admin-table-th">Counselor</th>
                        <th class="admin-table-th">Week Period</th>
                        <th class="admin-table-th">Sessions</th>
                        <th class="admin-table-th">Gross</th>
                        <th class="admin-table-th">Commission</th>
                        <th class="admin-table-th">Net Payout</th>
                        <th class="admin-table-th">Status</th>
                        <th class="admin-table-th">Paid On</th>
                        <th class="admin-table-th">Action</th>
                    </tr>
                </thead>
                <tbody class="admin-table-body">
                    <?php if (!empty($payouts)): ?>
                        <?php foreach ($payouts as $index => $po): ?>
                            <tr class="admin-table-row <?= $index % 2 === 0 ? 'admin-table-row--even' : 'admin-table-row--odd' ?>"
                                id="payout-row-<?= $po['counselorId'] ?>">
                                <td class="admin-table-td">
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <img src="<?= htmlspecialchars($po['counselorAvatar']) ?>"
                                             alt="" style="width:28px;height:28px;border-radius:50%;object-fit:cover;">
                                        <div>
                                            <div style="font-weight:600;"><?= htmlspecialchars($po['counselorName']) ?></div>
                                            <div style="font-size:0.75rem;color:var(--color-text-secondary);"><?= htmlspecialchars($po['counselorEmail']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="admin-table-td">
                                    <?= htmlspecialchars($po['periodStart']) ?> –<br>
                                    <?= htmlspecialchars($po['periodEnd']) ?>
                                </td>
                                <td class="admin-table-td"><?= $po['sessionsCount'] ?></td>
                                <td class="admin-table-td"><?= htmlspecialchars($po['currency']) ?> <?= htmlspecialchars($po['grossAmount']) ?></td>
                                <td class="admin-table-td">
                                    <?= htmlspecialchars($po['currency']) ?> <?= htmlspecialchars($po['commission']) ?>
                                    <?php if ((float) $po['commissionRate'] > 0): ?>
                                        <span style="font-size:0.72rem;color:var(--color-text-secondary);">(<?= htmlspecialchars($po['commissionRate']) ?>%)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="admin-table-td" style="font-weight:600;">
                                    <?= htmlspecialchars($po['currency']) ?> <?= htmlspecialchars($po['netAmount']) ?>
                                </td>
                                <td class="admin-table-td" id="payout-status-<?= $po['counselorId'] ?>">
                                    <?= payoutStatusBadge($po['status']) ?>
                                </td>
                                <td class="admin-table-td" id="payout-paid-at-<?= $po['counselorId'] ?>">
                                    <?= $po['paidAt'] ? htmlspecialchars($po['paidAt']) : '<span style="color:var(--color-text-secondary);font-style:italic;">—</span>' ?>
                                </td>
                                <td class="admin-table-td">
                                    <?php if ($po['status'] !== 'completed'): ?>
                                        <button class="admin-button admin-button--primary"
                                                onclick="markPayoutPaid(<?= $po['counselorId'] ?>)"
                                                id="payout-btn-<?= $po['counselorId'] ?>"
                                                style="font-size:0.78rem;padding:5px 14px;">
                                            Mark as Paid
                                        </button>
                                    <?php else: ?>
                                        <span style="color:var(--color-text-secondary);font-size:0.78rem;">Paid</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr class="admin-table-row">
                            <td class="admin-table-td" colspan="9" style="text-align:center;color:var(--color-text-secondary);padding:32px;">
                                No payout records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php
            $basePath = '/admin/counselor-payouts';
            $query    = array_merge($filters, ['search' => $search]);
            require __DIR__ . '/../common/admin.pagination.php';
            ?>
        </div>
    </section>
</main>

<!-- Confirm paid modal -->
<div id="payoutConfirmOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;padding:32px;max-width:400px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,0.18);">
        <h3 style="margin-bottom:12px;">Confirm Payment</h3>
        <p id="payoutConfirmText" style="color:var(--color-text-secondary);margin-bottom:24px;font-size:0.95rem;"></p>
        <div style="display:flex;gap:12px;justify-content:flex-end;">
            <button class="admin-button admin-button--secondary" onclick="closePayoutConfirm()">Cancel</button>
            <button class="admin-button admin-button--primary" id="payoutConfirmBtn" onclick="confirmMarkPaid()">Confirm</button>
        </div>
        <p id="payoutConfirmError" style="color:#dc2626;font-size:0.85rem;margin-top:12px;display:none;"></p>
    </div>
</div>

<?php require_once __DIR__ . '/../common/admin.footer.php'; ?>
</body>
</html>
