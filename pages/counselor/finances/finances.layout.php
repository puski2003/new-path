<?php
$activePage  = 'finances';
$pageScripts = ['/assets/js/counselor/finances/finances.js'];

/* ── Status badge helper ── */
function finStatusBadge(string $status): string {
    $map = [
        'completed'  => 'badge--success',
        'pending'    => 'badge--warning',
        'processing' => 'badge--info',
        'failed'     => 'badge--danger',
        'refunded'   => 'badge--neutral',
        'disputed'   => 'badge--danger',
    ];
    $cls = $map[$status] ?? 'badge--neutral';
    return '<span class="fin-badge ' . $cls . '">' . htmlspecialchars(ucfirst($status)) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<?php $pageTitle = 'Finances'; $pageStyle = ['counselor/finances']; require __DIR__ . '/../common/counselor.html.head.php'; ?>
<body>
<main class="main-container theme-counselor">
    <?php require __DIR__ . '/../common/counselor.sidebar.php'; ?>

    <section class="main-content">
        <?php
        $pageHeaderCards = '
            <div class="card days-sober-card">
                <div class=" .theme-counselor days-sober-content">
                    <p>INCOME</p>
                    <h1>Rs. ' . number_format($summary['totalEarned'], 2) . '</h1>
                </div>
                <p>' . $summary['totalSessions'] . ' sessions paid</p>
            </div>
            <div class="card days-sober-card">
                <div class=".theme-counselor days-sober-content">
                    <p>PENDING PAYOUT</p>
                        <h1>Rs. ' . number_format($summary['pendingPayout'], 2) . '</h1>
                </div>
                <p>Awaiting payment</p>
            </div>
        ';
        require __DIR__ . '/../common/counselor.page-header.php';
        ?>

        <div class="main-content-body">
            <div class="inner-body-content">
                <div class="body-column">

                    <!-- Tab bar -->
                    <div class="dashboard-card counselor-tab-card">
                        <div class="counselor-tab-row">
                            <span onclick="showSection('tab-payments')"
                                  class="toggle-button <?= $activeTab === 'payments' ? 'active-button' : '' ?>"
                                  id="btn-payments">Session Payments</span>
                            <span onclick="showSection('tab-payouts')"
                                  class="toggle-button <?= $activeTab === 'payouts' ? 'active-button' : '' ?>"
                                  id="btn-payouts">Weekly Payouts</span>
                        </div>
                    </div>

                    <!-- ── Session Payments tab ── -->
                    <section class="toggle-section <?= $activeTab === 'payments' ? 'active-section' : '' ?>" id="tab-payments">

                        <!-- Toolbar -->
                        <div class="dashboard-card counselor-toolbar-card">
                            <form method="GET" class="counselor-toolbar">
                                <input type="hidden" name="tab" value="payments">
                                <div class="search-bar fin-search-bar">
                                    <button class="search-button" type="submit">
                                        <i data-lucide="search" class="search-icon" stroke-width="1"></i>
                                    </button>
                                    <input type="text" name="search" class="search-input"
                                           placeholder="Search by client or transaction ID"
                                           value="<?= htmlspecialchars($search) ?>">
                                </div>
                            </form>
                           <div class="flex" style="justify-content: flex-end;"> 
                            <a href="/counselor/finances/receipt?type=statement" target="_blank" class="btn btn-secondary">
                                <i data-lucide="file-text" stroke-width="1" width="16" height="16"></i>
                                Earnings Statement
                            </a>
                        </div>
                        </div>

                        <!-- Table card -->
                        <?php $cardTitle = null; $cardAction = null; $cardClass = 'counselor-list-card fin-card';
                        require __DIR__ . '/../common/counselor.section-card.php'; ?>

                        <?php if (!empty($payments)): ?>
                            <div class="fin-table-wrap">
                                <table class="fin-table">
                                    <thead>
                                        <tr>
                                            <th>Transaction</th>
                                            <th>Client</th>
                                            <th>Session Date</th>
                                            <th>Duration</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Paid On</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payments as $i => $p): ?>
                                            <tr class="<?= $i % 2 === 0 ? 'fin-row--even' : 'fin-row--odd' ?>">
                                                <td class="fin-txn-id"><?= htmlspecialchars($p['transactionId']) ?></td>
                                                <td>
                                                    <div class="fin-client-cell">
                                                        <img src="<?= htmlspecialchars($p['clientAvatar']) ?>"
                                                             alt="" class="fin-client-avatar">
                                                        <span><?= htmlspecialchars($p['clientName']) ?></span>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($p['sessionDate']) ?> <span class="fin-time"><?= htmlspecialchars($p['sessionTime']) ?></span></td>
                                                <td><?= $p['duration'] > 0 ? $p['duration'] . ' min' : '—' ?></td>
                                                <td class="fin-amount"><?= htmlspecialchars($p['currency']) ?> <?= htmlspecialchars($p['amount']) ?></td>
                                                <td><?= finStatusBadge($p['status']) ?></td>
                                                <td><?= htmlspecialchars($p['date']) ?></td>
                                                <td>
                                                    <a href="/counselor/finances/receipt?type=receipt&id=<?= $p['rawId'] ?>"
                                                       target="_blank"
                                                       class="fin-receipt-link"
                                                       title="View receipt">
                                                        <i data-lucide="receipt" stroke-width="1"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($paymentsPagination['totalPages'] > 1): ?>
                                <div class="fin-pagination">
                                    <?php if ($paymentsPagination['currentPage'] > 1): ?>
                                        <a href="?tab=payments&search=<?= urlencode($search) ?>&txPage=<?= $paymentsPagination['currentPage'] - 1 ?>" class="btn btn-secondary">Prev</a>
                                    <?php else: ?>
                                        <span class="btn btn-secondary fin-page-btn--disabled">Prev</span>
                                    <?php endif; ?>
                                    <span class="fin-page-info">Page <?= $paymentsPagination['currentPage'] ?> of <?= $paymentsPagination['totalPages'] ?></span>
                                    <?php if ($paymentsPagination['currentPage'] < $paymentsPagination['totalPages']): ?>
                                        <a href="?tab=payments&search=<?= urlencode($search) ?>&txPage=<?= $paymentsPagination['currentPage'] + 1 ?>" class="btn btn-link">Next</a>
                                    <?php else: ?>
                                        <span class="btn btn-secondary fin-page-btn--disabled">Next</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <?php $emptyStateMessage = 'No session payments found.';
                                  $emptyStateSubtext  = 'Completed session payments will appear here.';
                                  require __DIR__ . '/../common/counselor.empty-state.php'; ?>
                        <?php endif; ?>

                        </div><!-- /.fin-card -->

                    </section><!-- /#tab-payments -->

                    <!-- ── Weekly Payouts tab ── -->
                    <section class="toggle-section <?= $activeTab === 'payouts' ? 'active-section' : '' ?>" id="tab-payouts">

                        <!-- Toolbar -->
                        <div class="dashboard-card counselor-toolbar-card">
                            <form method="GET" class="counselor-toolbar">
                                <input type="hidden" name="tab" value="payouts">
                                <select name="payoutStatus" class="fin-filter-select">
                                    <option value="all"        <?= $statusFilter === 'all'        ? 'selected' : '' ?>>All Status</option>
                                    <option value="pending"    <?= $statusFilter === 'pending'    ? 'selected' : '' ?>>Pending</option>
                                    <option value="processing" <?= $statusFilter === 'processing' ? 'selected' : '' ?>>Processing</option>
                                    <option value="completed"  <?= $statusFilter === 'completed'  ? 'selected' : '' ?>>Paid</option>
                                    <option value="failed"     <?= $statusFilter === 'failed'     ? 'selected' : '' ?>>Failed</option>
                                </select>
                                <button class="btn btn-bg-light-green filter-button" type="submit">
                                    <i data-lucide="filter" stroke-width="1" width="16" height="16"></i>
                                    <span>Filter</span>
                                </button>
                            </form>
                        </div>

                        <!-- Table card -->
                        <?php $cardTitle = null; $cardAction = null; $cardClass = 'counselor-list-card fin-card';
                        require __DIR__ . '/../common/counselor.section-card.php'; ?>

                        <?php if (!empty($payouts)): ?>
                            <div class="fin-table-wrap">
                                <table class="fin-table">
                                    <thead>
                                        <tr>
                                            <th>Week Period</th>
                                            <th>Sessions</th>
                                            <th>Gross</th>
                                            <th>Commission</th>
                                            <th>Net Payout</th>
                                            <th>Status</th>
                                            <th>Paid On</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payouts as $i => $po):
                                            $gross    = (float) str_replace(',', '', $po['amount']);
                                            $comm     = (float) str_replace(',', '', $po['platformCommission']);
                                            $net      = $gross - $comm;
                                        ?>
                                            <tr class="<?= $i % 2 === 0 ? 'fin-row--even' : 'fin-row--odd' ?>">
                                                <td>
                                                    <span class="fin-period"><?= htmlspecialchars($po['periodLabel']) ?></span>
                                                    <span class="fin-created-label">Created <?= htmlspecialchars($po['createdAt']) ?></span>
                                                </td>
                                                <td><?= $po['sessionsCount'] ?></td>
                                                <td class="fin-amount"><?= htmlspecialchars($po['currency']) ?> <?= htmlspecialchars($po['amount']) ?></td>
                                                <td class="fin-commission">
                                                    <?= htmlspecialchars($po['currency']) ?> <?= number_format($comm, 2) ?>
                                                    <?php if ((float) $po['commissionRate'] > 0): ?>
                                                        <span class="fin-rate">(<?= htmlspecialchars($po['commissionRate']) ?>%)</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="fin-amount fin-net">
                                                    <?= htmlspecialchars($po['currency']) ?> <?= number_format($net, 2) ?>
                                                </td>
                                                <td><?= finStatusBadge($po['status']) ?></td>
                                                <td><?= $po['paidAt'] !== '-' ? htmlspecialchars($po['paidAt']) : '<span class="fin-unpaid">Pending</span>' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($payoutsPagination['totalPages'] > 1): ?>
                                <div class="fin-pagination">
                                    <?php if ($payoutsPagination['currentPage'] > 1): ?>
                                        <a href="?tab=payouts&payoutStatus=<?= urlencode($statusFilter) ?>&payoutPage=<?= $payoutsPagination['currentPage'] - 1 ?>" class="btn btn-secondary">Prev</a>
                                    <?php else: ?>
                                        <span class="btn btn-secondary fin-page-btn--disabled">Prev</span>
                                    <?php endif; ?>
                                    <span class="fin-page-info">Page <?= $payoutsPagination['currentPage'] ?> of <?= $payoutsPagination['totalPages'] ?></span>
                                    <?php if ($payoutsPagination['currentPage'] < $payoutsPagination['totalPages']): ?>
                                        <a href="?tab=payouts&payoutStatus=<?= urlencode($statusFilter) ?>&payoutPage=<?= $payoutsPagination['currentPage'] + 1 ?>" class="btn btn-link">Next</a>
                                    <?php else: ?>
                                        <span class="btn btn-secondary fin-page-btn--disabled">Next</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <?php $emptyStateMessage = 'No payouts found.';
                                  $emptyStateSubtext  = 'Weekly payouts processed by the admin will appear here.';
                                  require __DIR__ . '/../common/counselor.empty-state.php'; ?>
                        <?php endif; ?>

                        </div><!-- /.fin-card -->

                    </section><!-- /#tab-payouts -->

                </div><!-- /.body-column -->
            </div><!-- /.inner-body-content -->
        </div><!-- /.main-content-body -->
    </section>
</main>

<?php require __DIR__ . '/../common/counselor.footer.php'; ?>
</body>
</html>
