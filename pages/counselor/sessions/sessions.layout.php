<?php
$activePage = 'sessions';
$pageScripts = [
    '/assets/js/counselor/sessions/sessions.js',
    '/assets/js/counselor/sessions/rescheduleRequests.js',
];
?>
<!DOCTYPE html>
<html lang="en">
<?php $pageTitle = 'Schedule'; $pageStyle = ['counselor/sessions']; require __DIR__ . '/../common/counselor.html.head.php'; ?>
<body>
<main class="main-container theme-counselor">
    <?php require __DIR__ . '/../common/counselor.sidebar.php'; ?>

    <section class="main-content">
        <?php require __DIR__ . '/../common/counselor.page-header.php'; ?>

        <div class="main-content-body ">
            <div class="inner-body-content">
                <div class="body-column">

                    <!-- <?php require __DIR__ . '/../common/counselor.toolbar.php'; ?> -->

                    <div class="dashboard-card counselor-tab-card">
                        <div class="counselor-tab-row">
                            <span onclick="showSection('tab-today')"     class="toggle-button active-button" id="btn-today">Today</span>
                            <span onclick="showSection('tab-upcoming')"  class="toggle-button"               id="btn-upcoming">Upcoming</span>
                            <span onclick="showSection('tab-completed')" class="toggle-button"               id="btn-completed">Completed</span>
                            <span onclick="showSection('tab-cancelled')" class="toggle-button"               id="btn-cancelled">Cancelled / No-show</span>
                            <span onclick="showSection('tab-reschedule')" class="toggle-button"              id="btn-reschedule">Reschedule Requests <span class="reschedule-badge" id="rescheduleBadge" style="display:none;"></span></span>
                            <span onclick="showSection('tab-disputes')"  class="toggle-button"               id="btn-disputes">Absence Reports <span class="reschedule-badge" id="disputesBadge" style="display:none;"></span></span>
                        </div>
                    </div>

                    <?php $cardTitle = null; $cardAction = null; $cardClass = 'counselor-list-card';
                    require __DIR__ . '/../common/counselor.section-card.php'; ?>

                        <!-- Today -->
                        <section class="toggle-section active-section" id="tab-today">
                            <?php if (!empty($tabToday)): ?>
                                <?php foreach ($tabToday as $session): $isUpcoming = true; require __DIR__ . '/../common/counselor.session-card.php'; endforeach; ?>
                            <?php else: ?>
                                <?php $emptyStateMessage = 'No sessions scheduled for today.'; require __DIR__ . '/../common/counselor.empty-state.php'; ?>
                            <?php endif; ?>
                        </section>

                        <!-- Upcoming -->
                        <section class="toggle-section" id="tab-upcoming">
                            <?php if (!empty($tabUpcoming)): ?>
                                <?php foreach ($tabUpcoming as $session): $isUpcoming = true; require __DIR__ . '/../common/counselor.session-card.php'; endforeach; ?>
                            <?php else: ?>
                                <?php $emptyStateMessage = 'No upcoming sessions.'; require __DIR__ . '/../common/counselor.empty-state.php'; ?>
                            <?php endif; ?>
                        </section>

                        <!-- Completed -->
                        <section class="toggle-section" id="tab-completed">
                            <?php if (!empty($tabCompleted)): ?>
                                <?php foreach ($tabCompleted as $session): $isUpcoming = false; require __DIR__ . '/../common/counselor.session-card.php'; endforeach; ?>
                            <?php else: ?>
                                <?php $emptyStateMessage = 'No completed sessions yet.'; require __DIR__ . '/../common/counselor.empty-state.php'; ?>
                            <?php endif; ?>
                        </section>

                        <!-- Cancelled / No-show -->
                        <section class="toggle-section" id="tab-cancelled">
                            <?php if (!empty($tabCancelled)): ?>
                                <?php foreach ($tabCancelled as $session): $isUpcoming = false; require __DIR__ . '/../common/counselor.session-card.php'; endforeach; ?>
                            <?php else: ?>
                                <?php $emptyStateMessage = 'No cancelled or missed sessions.'; require __DIR__ . '/../common/counselor.empty-state.php'; ?>
                            <?php endif; ?>
                        </section>

                        <!-- Reschedule Requests (loaded via AJAX) -->
                        <section class="toggle-section" id="tab-reschedule">
                            <div id="reschedule-requests-list">
                                <p class="empty-state-text">Loading requests…</p>
                            </div>
                        </section>

                        <!-- Absence Reports (loaded via AJAX) -->
                        <section class="toggle-section" id="tab-disputes">
                            <div id="disputes-list">
                                <p class="empty-state-text">Loading reports…</p>
                            </div>
                        </section>

                    </div><!-- /.counselor-list-card -->

                </div><!-- /.body-column -->
            </div><!-- /.inner-body-content -->
        </div><!-- /.main-content-body -->
    </section>
    <!-- Absence report detail modal -->
    <div class="session-modal-overlay" id="disputeDetailOverlay" style="display:none;">
        <div class="session-modal">
            <div class="session-modal-header">
                <h3>Absence Report Details</h3>
                <button type="button" class="session-modal-close" id="closeDisputeDetail">&times;</button>
            </div>
            <div class="session-modal-body" id="disputeDetailBody">
            </div>
        </div>
    </div>

    <!-- Reschedule request review modal -->
    <div class="session-modal-overlay" id="rescheduleReviewOverlay" style="display:none;">
        <div class="session-modal">
            <div class="session-modal-header">
                <h3 id="rescheduleReviewTitle">Review Request</h3>
                <button type="button" class="session-modal-close" id="closeRescheduleReview">&times;</button>
            </div>
            <div class="session-modal-body">
                <p id="rescheduleReviewMeta" style="color:var(--color-text-secondary);margin-bottom:var(--spacing-md);font-size:var(--font-size-sm);"></p>
                <p id="rescheduleReviewReason" style="margin-bottom:var(--spacing-lg);"></p>
                <div class="form-group">
                    <label for="rescheduleReviewNote">Note to client <span class="optional">(optional)</span></label>
                    <textarea class="form-input" id="rescheduleReviewNote" rows="3" maxlength="500"
                        placeholder="Briefly explain your decision…"></textarea>
                </div>
                <p id="rescheduleReviewError" style="color:#dc2626;font-size:var(--font-size-sm);display:none;"></p>
                <div class="session-modal-actions">
                    <button type="button" class="btn btn-secondary" id="closeRescheduleReview2">Cancel</button>
                    <button type="button" class="btn btn-danger"    id="rejectRescheduleBtn">Decline</button>
                    <button type="button" class="btn btn-primary"   id="approveRescheduleBtn">Approve</button>
                </div>
            </div>
        </div>
    </div>
</main>


<?php require __DIR__ . '/../common/counselor.footer.php'; ?>

<script>
(function () {
    var disputesLoaded = false;
    var statusColors = {
        pending:   '#f59e0b',
        reviewed:  '#3b82f6',
        resolved:  '#10b981',
        dismissed: '#6b7280',
    };

    // Auto-open disputes tab if URL has ?tab=disputes
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('tab') === 'disputes' && typeof showSection === 'function') {
        showSection('tab-disputes');
    }

    // Hook into tab switching — load on first open
    var btnDisputes = document.getElementById('btn-disputes');
    if (btnDisputes) {
        btnDisputes.addEventListener('click', function () {
            if (!disputesLoaded) {
                loadDisputes();
            }
        });
    }

    function loadDisputes() {
        disputesLoaded = true;
        var container = document.getElementById('disputes-list');
        container.innerHTML = '<p class="empty-state-text">Loading…</p>';

        fetch('/counselor/sessions?ajax=get_no_show_disputes', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.success) {
                container.innerHTML = '<p class="empty-state-text">Failed to load reports.</p>';
                return;
            }
            renderDisputes(data.disputes, container);
        })
        .catch(function () {
            container.innerHTML = '<p class="empty-state-text">Network error.</p>';
        });
    }

    function renderDisputes(disputes, container) {
        if (!disputes || disputes.length === 0) {
            container.innerHTML = '<p class="empty-state-text">No absence reports filed against your sessions.</p>';
            // Hide badge
            var badge = document.getElementById('disputesBadge');
            if (badge) badge.style.display = 'none';
            return;
        }

        // Badge: count pending ones
        var pendingCount = disputes.filter(function (d) { return d.disputeStatus === 'pending'; }).length;
        var badge = document.getElementById('disputesBadge');
        if (badge) {
            if (pendingCount > 0) {
                badge.textContent = pendingCount;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }

        var html = '<div style="display:grid;gap:12px;">';
        disputes.forEach(function (d) {
            var color = statusColors[d.disputeStatus] || '#6b7280';
            var label = d.disputeStatus.charAt(0).toUpperCase() + d.disputeStatus.slice(1);
            var refundLine = '';
            if (d.disputeStatus === 'resolved') {
                refundLine = '<p style="margin:4px 0 0;font-size:.82rem;color:#10b981;font-weight:600;">Refund approved' + (d.amount ? ' — ' + d.amount : '') + '</p>';
            } else if (d.disputeStatus === 'dismissed') {
                refundLine = '<p style="margin:4px 0 0;font-size:.82rem;color:#6b7280;">Report dismissed.</p>';
            } else {
                refundLine = '<p style="margin:4px 0 0;font-size:.82rem;color:#f59e0b;">Under admin review.</p>';
            }

            var noteLine = d.adminNote
                ? '<p style="margin:6px 0 0;font-size:.82rem;color:#6b7280;"><strong>Admin note:</strong> ' + esc(d.adminNote) + '</p>'
                : '';

            html += '<div style="border:1px solid #e5e7eb;border-radius:10px;padding:16px;background:#fff;">'
                + '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">'
                + '<div style="display:flex;align-items:center;gap:10px;">'
                + '<img src="' + esc(d.clientAvatar) + '" style="width:36px;height:36px;border-radius:50%;object-fit:cover;" alt="">'
                + '<div>'
                + '<p style="margin:0;font-weight:600;font-size:.9rem;">' + esc(d.clientName) + '</p>'
                + '<p style="margin:0;font-size:.8rem;color:#6b7280;">' + esc(d.sessionDate) + '</p>'
                + '</div></div>'
                + '<span style="padding:2px 10px;border-radius:12px;font-size:.78rem;font-weight:600;background:' + color + '22;color:' + color + ';">' + label + '</span>'
                + '</div>'
                + (d.description ? '<p style="margin:0 0 4px;font-size:.85rem;color:#374151;font-style:italic;">&ldquo;' + esc(d.description) + '&rdquo;</p>' : '')
                + refundLine
                + noteLine
                + '<p style="margin:6px 0 0;font-size:.78rem;color:#9ca3af;">Reported ' + esc(d.reportedAt) + '</p>'
                + '</div>';
        });
        html += '</div>';
        container.innerHTML = html;
    }

    function esc(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // Close dispute detail modal
    var closeBtn = document.getElementById('closeDisputeDetail');
    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            document.getElementById('disputeDetailOverlay').style.display = 'none';
        });
    }
})();
</script>
</body>
</html>
