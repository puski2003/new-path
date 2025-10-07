<?php
$activePage         = 'sessions';
$pageHeaderTitle    = 'Session Workspace';
$pageHeaderSubtitle = htmlspecialchars($session['clientName']) . ' · ' . $displayTime;
$pageScripts        = ['/assets/js/counselor/sessions/workspace.js'];
?>
<!DOCTYPE html>
<html lang="en">
<?php $pageTitle = 'Workspace · ' . $session['clientName']; $pageStyle = ['counselor/workspace']; require __DIR__ . '/../../common/counselor.html.head.php'; ?>
<body>
<main class="main-container theme-counselor" data-session-id="<?= (int)$sessionId ?>">
    <?php require __DIR__ . '/../../common/counselor.sidebar.php'; ?>

    <section class="main-content">
        <?php require __DIR__ . '/../../common/counselor.page-header.php'; ?>

        <div class="main-content-body">

            <!-- Back -->
            <div class="cc-back-row">
                <a class="cc-back-btn" href="/counselor/sessions">
                    <i data-lucide="arrow-left" stroke-width="1.8"></i>
                    Back to Schedule
                </a>
            </div>

            <!-- ── Join Meeting banner ── -->
            <div class="ws-meeting-banner">
                <div class="ws-meeting-info">
                    <i data-lucide="<?= $typeIcon ?>" stroke-width="1.5" class="ws-meeting-icon"></i>
                    <div>
                        <span class="ws-meeting-label"><?= htmlspecialchars($typeLabel) ?> Session</span>
                        <span class="ws-meeting-time"><?= htmlspecialchars($displayTime) ?></span>
                    </div>
                </div>
                <div class="ws-banner-actions">
                    <?php if (!empty($session['meetingLink'])): ?>
                        <a class="btn btn-primary ws-join-btn"
                           href="<?= htmlspecialchars($session['meetingLink']) ?>"
                           target="_blank" rel="noopener">
                            <i data-lucide="video" stroke-width="2"></i>
                            Join Meeting
                        </a>
                    <?php else: ?>
                        <span class="ws-no-link">No meeting link available</span>
                    <?php endif; ?>

                    <?php if (!in_array($session['status'], ['completed', 'cancelled', 'no_show'], true)): ?>
                        <button type="button" class="btn btn-secondary ws-complete-btn" id="markCompletedBtn">
                            <i data-lucide="check-circle" stroke-width="2" width="16" height="16"></i>
                            Mark as Completed
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ── Client hero ── -->
            <div class="cc-profile-card">
                <div class="cc-profile-avatar">
                    <img src="<?= htmlspecialchars($session['clientAvatar']) ?>"
                         alt="<?= htmlspecialchars($session['clientName']) ?>"
                         onerror="this.src='/assets/img/avatar.png'" />
                </div>
                <div class="cc-profile-info">
                    <h3 class="cc-profile-name"><?= htmlspecialchars($session['clientName']) ?></h3>
                    <?php if (!empty($session['clientEmail'])): ?>
                        <p class="cc-profile-email"><?= htmlspecialchars($session['clientEmail']) ?></p>
                    <?php endif; ?>
                    <?php if ($clientProfile): ?>
                        <p class="cc-profile-status"><?= htmlspecialchars($clientProfile['status']) ?></p>
                    <?php endif; ?>
                </div>
                <?php if ($clientProfile): ?>
                <div class="cc-profile-pricing">
                    <span class="stat-label">Plan Progress</span>
                    <span class="stat-value"><?= (int)$clientProfile['progressPercentage'] ?>%</span>
                    <div class="cc-progress-slider" style="--value: <?= (int)$clientProfile['progressPercentage'] ?>%">
                        <div class="bar"></div>
                        <div class="thumb"></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- ── Session details ── -->
            <div class="cc-section">
                <div class="cc-section-header">
                    <h4>Session Details</h4>
                    <span class="plan-status status-<?= htmlspecialchars($session['status']) ?>" id="sessionStatusBadge">
                        <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $session['status']))) ?>
                    </span>
                </div>
                <div class="ws-meta-grid">
                    <div class="ws-meta-item">
                        <i data-lucide="calendar" stroke-width="1.5"></i>
                        <div>
                            <span class="ws-meta-label">Date &amp; Time</span>
                            <span class="ws-meta-value"><?= htmlspecialchars($displayTime) ?></span>
                        </div>
                    </div>
                    <div class="ws-meta-item">
                        <i data-lucide="<?= $typeIcon ?>" stroke-width="1.5"></i>
                        <div>
                            <span class="ws-meta-label">Session Type</span>
                            <span class="ws-meta-value"><?= htmlspecialchars($typeLabel) ?></span>
                        </div>
                    </div>
                    <div class="ws-meta-item">
                        <i data-lucide="clock" stroke-width="1.5"></i>
                        <div>
                            <span class="ws-meta-label">Duration</span>
                            <span class="ws-meta-value"><?= (int)$session['durationMinutes'] ?> minutes</span>
                        </div>
                    </div>
                    <?php if ($clientProfile): ?>
                    <div class="ws-meta-item">
                        <i data-lucide="layers" stroke-width="1.5"></i>
                        <div>
                            <span class="ws-meta-label">Total Sessions</span>
                            <span class="ws-meta-value"><?= (int)$clientProfile['totalSessions'] ?> (<?= (int)$clientProfile['completedSessions'] ?> completed)</span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="ws-link-row">
                    <a class="ws-profile-link" href="/counselor/client-profile?id=<?= (int)$session['userId'] ?>">
                        <i data-lucide="external-link" stroke-width="1.5"></i>
                        View Full Client Profile
                    </a>
                   
                </div>
            </div>

            <!-- ── Private session notes ── -->
            <div class="cc-section">
                <div class="cc-section-header">
                    <h4>Session Notes <span class="ws-notes-private-label">private</span></h4>
                    <div class="ws-notes-actions">
                        <span id="notesSaveStatus" class="ws-save-status"></span>
                        <button type="button" class="btn btn-secondary ws-save-btn" id="saveNotesBtn">Save Notes</button>
                    </div>
                </div>
                <textarea class="ws-notes-textarea"
                          id="privateNotesInput"
                          placeholder="Record your observations, clinical notes, and follow-up actions here. These notes are private to you."
                          rows="8"><?= htmlspecialchars($session['privateNotes']) ?></textarea>
            </div>

            <!-- ── Recovery plan ── -->
            <div class="cc-section">
                <div class="cc-section-header">
                    <h4>Recovery Plan</h4>
                    <div class="ws-plan-actions">
                        <?php if ($clientProfile && !empty($clientProfile['plan']['planId'])): ?>
                        <a href="/counselor/recovery-plans/view?planId=<?= (int)$clientProfile['plan']['planId'] ?>"
                           class="btn btn-primary" style="font-size:var(--font-size-xs);">View Full Plan</a>
                    <?php endif; ?>
                       <a class="btn btn-secondary ws-create-plan-btn"
                       href="/counselor/recovery-plans/create?userId=<?= (int)$session['userId'] ?>">
                        <i data-lucide="clipboard-plus" stroke-width="1.8" width="16" height="16"></i>
                        Create Recovery Plan
                    </a>
                    </div>
                    
                </div>

                <?php if ($clientProfile && !empty($clientProfile['plan'])): ?>
                    <div class="cc-plan-card">
                        <div class="cc-plan-info">
                            <span class="cc-plan-title"><?= htmlspecialchars($clientProfile['plan']['title']) ?></span>
                            <span class="plan-status status-<?= htmlspecialchars($clientProfile['plan']['status']) ?>">
                                <?= htmlspecialchars(ucfirst($clientProfile['plan']['status'])) ?>
                            </span>
                            <?php if (!empty($clientProfile['plan']['description'])): ?>
                                <p class="cc-plan-desc"><?= htmlspecialchars($clientProfile['plan']['description']) ?></p>
                            <?php endif; ?>
                            <div class="plan-progress" style="margin-top:var(--spacing-sm);">
                                <div class="progress-bar-small">
                                    <div class="progress-fill-small"
                                         style="width:<?= (int)$clientProfile['plan']['progressPercentage'] ?>%;"></div>
                                </div>
                                <span class="progress-text"><?= (int)$clientProfile['plan']['progressPercentage'] ?>%</span>
                            </div>
                        </div>
                        <img src="/assets/img/plan.png" alt="Plan" />
                    </div>
                <?php else: ?>
                    <p style="font-size:var(--font-size-sm);color:var(--color-text-muted);">
                        No recovery plan assigned yet.
                        <a href="/counselor/recovery-plans/create?userId=<?= (int)$session['userId'] ?>" style="color:var(--color-primary);">Create one now</a>
                        to start tracking this client's progress.
                    </p>
                <?php endif; ?>
            </div>

        </div><!-- /.main-content-body -->
    </section>
<?php if (!in_array($session['status'], ['completed', 'cancelled', 'no_show'], true)): ?>
<div class="session-modal-overlay" id="completeSessionOverlay" style="display:none;">
    <div class="session-modal">
        <div class="session-modal-header">
            <h3>Complete Session</h3>
            <button type="button" class="session-modal-close" id="closeCompleteSessionModal">&times;</button>
        </div>
        <div class="session-modal-body">
            <p class="ws-complete-modal-copy">
                Mark this session as completed? This will move it into the completed schedule history.
            </p>
            <p class="ws-complete-modal-copy ws-complete-modal-copy--muted">
                Make sure your notes are saved before continuing.
            </p>
            <p id="completeSessionError" class="ws-complete-modal-error" style="display:none;"></p>
            <div class="session-modal-actions">
                <button type="button" class="btn btn-secondary" id="cancelCompleteSessionBtn">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmCompleteSessionBtn">Mark as Completed</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
</main>

<?php require __DIR__ . '/../../common/counselor.footer.php'; ?>
</body>
</html>
