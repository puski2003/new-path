<!DOCTYPE html>
<html lang="en">
<?php
$pageScripts = [
    '/assets/js/user/sessions/sessions.js',
];
?>
<?php require_once __DIR__ . '/../common/user.html.head.php'; ?>

<body>
    <main class="main-container">
        <?php $activePage = 'sessions';
        require_once __DIR__ . '/../common/user.sidebar.php'; ?>

        <section class="main-content">
            <img src="/assets/img/main-content-head.svg"
                alt="Main Content Head background"
                class="main-header-bg-image" />

            <div class="main-content-header">
                <div class="main-content-header-text">
                    <h2>My Sessions</h2>
                    <p>Your scheduled guidance, all in one place.</p>
                </div>

                <div style="width: 25%"></div>
                <img src="/assets/img/session-header.svg"
                    alt="Session"
                    class="session-image" />
            </div>

            <div class="main-content-body">
                <div class="sessions-tabs">
                    <button class="tab-btn <?= $activeTab === 'upcoming' ? 'active' : '' ?>" data-tab="upcoming">Upcoming</button>
                    <button class="tab-btn <?= $activeTab === 'history' ? 'active' : '' ?>" data-tab="history">History</button>
                    <button class="tab-btn <?= $activeTab === 'reports' ? 'active' : '' ?>" data-tab="reports">Reports & Refunds</button>
                </div>

                <div class="sessions-container <?= $activeTab === 'history' ? 'hidden' : '' ?>" id="upcoming-sessions">
                    <?php if (empty($upcomingSessions)): ?>
                        <div class="session-card">
                            <div class="session-info">
                                <h3 class="session-name">No upcoming sessions</h3>
                                <p class="session-schedule">Book a session with a counselor to get started.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($upcomingSessions as $session): ?>
                            <?php require __DIR__ . '/../common/user.session-card.php'; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if ($upcomingTotalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($upcomingCurrentPage > 1): ?>
                                <a class="pagination-btn pagination-prev" href="/user/sessions?tab=upcoming&upage=<?= $upcomingCurrentPage - 1 ?>&hpage=<?= $historyCurrentPage ?>">
                                    <i data-lucide="arrow-left" stroke-width="1.8"></i>
                                </a>
                            <?php else: ?>
                                <button class="pagination-btn pagination-prev" disabled>
                                    <i data-lucide="arrow-left" stroke-width="1.8"></i>
                                </button>
                            <?php endif; ?>

                            <button class="pagination-btn pagination-number active"><?= $upcomingCurrentPage ?></button>

                            <?php if ($upcomingCurrentPage < $upcomingTotalPages): ?>
                                <a class="pagination-btn pagination-next" href="/user/sessions?tab=upcoming&upage=<?= $upcomingCurrentPage + 1 ?>&hpage=<?= $historyCurrentPage ?>">
                                    <i data-lucide="arrow-right" stroke-width="1.8"></i>
                                </a>
                            <?php else: ?>
                                <button class="pagination-btn pagination-next" disabled>
                                    <i data-lucide="arrow-right" stroke-width="1.8"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="sessions-container <?= $activeTab === 'history' ? '' : 'hidden' ?>" id="history-sessions">
                    <?php if (empty($historySessions)): ?>
                        <div class="session-card">
                            <div class="session-info">
                                <h3 class="session-name">No session history</h3>
                                <p class="session-schedule">Completed sessions will appear here.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($historySessions as $session): ?>
                            <?php require __DIR__ . '/../common/user.session-card.php'; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if ($historyTotalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($historyCurrentPage > 1): ?>
                                <a class="pagination-btn pagination-prev" href="/user/sessions?tab=history&hpage=<?= $historyCurrentPage - 1 ?>&upage=<?= $upcomingCurrentPage ?>">
                                    <i data-lucide="arrow-left" stroke-width="1.8"></i>
                                </a>
                            <?php else: ?>
                                <button class="pagination-btn pagination-prev" disabled>
                                    <i data-lucide="arrow-left" stroke-width="1.8"></i>
                                </button>
                            <?php endif; ?>

                            <button class="pagination-btn pagination-number active"><?= $historyCurrentPage ?></button>

                            <?php if ($historyCurrentPage < $historyTotalPages): ?>
                                <a class="pagination-btn pagination-next" href="/user/sessions?tab=history&hpage=<?= $historyCurrentPage + 1 ?>&upage=<?= $upcomingCurrentPage ?>">
                                    <i data-lucide="arrow-right" stroke-width="1.8"></i>
                                </a>
                            <?php else: ?>
                                <button class="pagination-btn pagination-next" disabled>
                                    <i data-lucide="arrow-right" stroke-width="1.8"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="sessions-container <?= $activeTab === 'reports' ? '' : 'hidden' ?>" id="report-sessions">
                    <?php if (empty($reportItems)): ?>
                        <div class="session-card">
                            <div class="session-info">
                                <h3 class="session-name">No reports or refunds yet</h3>
                                <p class="session-schedule">No-show reports and refund updates will appear here.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($reportItems as $report): ?>
                            <div class="session-card">
                                <div class="session-avatar">
                                    <img src="<?= htmlspecialchars($report['profilePicture']) ?>" alt="<?= htmlspecialchars($report['counselorName']) ?>" />
                                </div>
                                <div class="session-info">
                                    <span class="session-specialty">No-Show Report</span>
                                    <h3 class="session-name"><?= htmlspecialchars($report['counselorName']) ?></h3>
                                    <p class="session-schedule"><?= htmlspecialchars($report['sessionDate']) ?></p>
                                    <div>
                                        <span class="session-reschedule-badge session-reschedule-badge--pending">
                                            Report <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $report['reportStatus']))) ?>
                                        </span>
                                        <?php if (!empty($report['refundStatus'])): ?>
                                            <span class="session-reschedule-badge <?= in_array($report['refundStatus'], ['approved', 'resolved'], true) ? 'session-reschedule-badge--approved' : ($report['refundStatus'] === 'rejected' ? 'session-reschedule-badge--rejected' : 'session-reschedule-badge--pending') ?>">
                                                Refund <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $report['refundStatus']))) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($report['description'] !== ''): ?>
                                        <p class="session-schedule"><?= htmlspecialchars($report['description']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($report['requestedAmount'])): ?>
                                        <p class="session-schedule">Requested refund: <?= htmlspecialchars($report['requestedAmount']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($report['refundedAmount'])): ?>
                                        <p class="session-schedule">Refunded: <?= htmlspecialchars($report['refundedAmount']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($report['refundAdminNotes'] !== ''): ?>
                                        <p class="session-schedule">Admin note: <?= htmlspecialchars($report['refundAdminNotes']) ?></p>
                                    <?php elseif ($report['reportAdminNote'] !== ''): ?>
                                        <p class="session-schedule">Admin note: <?= htmlspecialchars($report['reportAdminNote']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="session-actions">
                                    <a class="btn btn-bg-light-green btn-view-more" href="/user/sessions?id=<?= (int)$report['sessionId'] ?>">View Session</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if ($reportsTotalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($reportsCurrentPage > 1): ?>
                                <a class="pagination-btn pagination-prev" href="/user/sessions?tab=reports&rpage=<?= $reportsCurrentPage - 1 ?>&upage=<?= $upcomingCurrentPage ?>&hpage=<?= $historyCurrentPage ?>">
                                    <i data-lucide="arrow-left" stroke-width="1.8"></i>
                                </a>
                            <?php else: ?>
                                <button class="pagination-btn pagination-prev" disabled>
                                    <i data-lucide="arrow-left" stroke-width="1.8"></i>
                                </button>
                            <?php endif; ?>

                            <button class="pagination-btn pagination-number active"><?= $reportsCurrentPage ?></button>

                            <?php if ($reportsCurrentPage < $reportsTotalPages): ?>
                                <a class="pagination-btn pagination-next" href="/user/sessions?tab=reports&rpage=<?= $reportsCurrentPage + 1 ?>&upage=<?= $upcomingCurrentPage ?>&hpage=<?= $historyCurrentPage ?>">
                                    <i data-lucide="arrow-right" stroke-width="1.8"></i>
                                </a>
                            <?php else: ?>
                                <button class="pagination-btn pagination-next" disabled>
                                    <i data-lucide="arrow-right" stroke-width="1.8"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
    <?php require_once __DIR__ . '/../common/user.footer.php'; ?>
</body>

</html>
