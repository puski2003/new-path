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
                        <button type="button" class="btn btn-secondary" id="extendSessionBtn">
                            <i data-lucide="clock-arrow-up" stroke-width="2" width="16" height="16"></i>
                            Extend Session
                        </button>
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

            <!-- ── Extension Requests ── -->
            <?php if (!empty($extensionRequests)): ?>
            <div class="cc-section">
                <div class="cc-section-header">
                    <h4>Extension Requests</h4>
                    <button type="button" class="btn btn-secondary" onclick="location.reload()" style="font-size:var(--font-size-xs);">
                        <i data-lucide="refresh-cw" stroke-width="1.8" width="14" height="14"></i>
                        Refresh
                    </button>
                </div>
                <table style="width:100%;border-collapse:collapse;font-size:var(--font-size-sm);">
                    <thead>
                        <tr style="border-bottom:2px solid var(--color-border);text-align:left;">
                            <th style="padding:8px 12px;">Duration</th>
                            <th style="padding:8px 12px;">Fee</th>
                            <th style="padding:8px 12px;">Status</th>
                            <th style="padding:8px 12px;">Sent At</th>
                            <th style="padding:8px 12px;">Responded At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($extensionRequests as $ext): ?>
                        <tr style="border-bottom:1px solid var(--color-border);">
                            <td style="padding:8px 12px;"><?= (int)$ext['durationMinutes'] ?> min</td>
                            <td style="padding:8px 12px;">LKR <?= number_format($ext['fee'], 2) ?></td>
                            <td style="padding:8px 12px;">
                                <span class="plan-status status-<?= htmlspecialchars($ext['status']) ?>">
                                    <?= htmlspecialchars(ucfirst($ext['status'])) ?>
                                </span>
                            </td>
                            <td style="padding:8px 12px;"><?= htmlspecialchars($ext['sentAt'] ?? '—') ?></td>
                            <td style="padding:8px 12px;"><?= htmlspecialchars($ext['respondedAt'] ?? '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

        </div><!-- /.main-content-body -->
    </section>
<?php if (!in_array($session['status'], ['completed', 'cancelled', 'no_show'], true)): ?>
<!-- ── Extend Session modal ── -->
<div class="session-modal-overlay" id="extendSessionOverlay" style="display:none;">
    <div class="session-modal">
        <div class="session-modal-header">
            <h3>Request Session Extension</h3>
            <button type="button" class="session-modal-close" id="closeExtendModal">&times;</button>
        </div>
        <div class="session-modal-body">
            <p style="color:var(--color-text-secondary);margin-bottom:var(--spacing-lg);">
                Select how long you'd like to extend the session. The client will be notified and must accept and pay before the extension begins.
            </p>
            <div id="extendOptions" style="display:flex;flex-direction:column;gap:var(--spacing-sm);margin-bottom:var(--spacing-lg);">
                <?php
                $counselorFeeRs = Database::search(
                    "SELECT consultation_fee FROM counselors WHERE counselor_id = $counselorId LIMIT 1"
                );
                $counselorFeeRow  = $counselorFeeRs ? $counselorFeeRs->fetch_assoc() : null;
                $hourlyRate       = (float)($counselorFeeRow['consultation_fee'] ?? 0);
                $extendOptionList = [
                    ['duration_minutes' => 30, 'fee' => round($hourlyRate / 2, 2)],
                    ['duration_minutes' => 60, 'fee' => round($hourlyRate,     2)],
                ];
                foreach ($extendOptionList as $opt): ?>
                <label class="extend-option-label" style="display:flex;align-items:center;gap:var(--spacing-md);padding:var(--spacing-md);border:1px solid var(--color-border);border-radius:var(--radius-md);cursor:pointer;">
                    <input type="radio" name="extendDuration" value="<?= (int)$opt['duration_minutes'] ?>" style="accent-color:var(--color-primary);">
                    <span style="flex:1;font-weight:500;"><?= (int)$opt['duration_minutes'] ?> minutes</span>
                    <span style="color:var(--color-primary);font-weight:600;">LKR <?= number_format($opt['fee'], 2) ?></span>
                </label>
                <?php endforeach; ?>
            </div>
            <p id="extendError" style="color:#dc2626;font-size:var(--font-size-sm);display:none;margin-bottom:var(--spacing-md);"></p>
            <p id="extendSuccess" style="color:#16a34a;font-size:var(--font-size-sm);display:none;margin-bottom:var(--spacing-md);"></p>
            <div class="session-modal-actions">
                <button type="button" class="btn btn-secondary" id="cancelExtendBtn">Cancel</button>
                <button type="button" class="btn btn-primary" id="sendExtendBtn">Send Request</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

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

<?php if (!in_array($session['status'], ['completed', 'cancelled', 'no_show'], true)): ?>
<script>
(function () {
    var SESSION_ID = <?= (int)$sessionId ?>;
    var overlay    = document.getElementById('extendSessionOverlay');
    var errorEl    = document.getElementById('extendError');
    var sendBtn    = document.getElementById('sendExtendBtn');

    function openExtend() {
        overlay.querySelectorAll('input[type=radio]').forEach(function(r){ r.checked = false; });
        errorEl.style.display = 'none';
        sendBtn.disabled = false; sendBtn.textContent = 'Send Request';
        overlay.style.display = 'flex'; overlay.offsetHeight; overlay.classList.add('show');
    }
    function closeExtend() {
        overlay.classList.remove('show');
        setTimeout(function(){ overlay.style.display = 'none'; }, 300);
    }

    document.getElementById('extendSessionBtn').addEventListener('click', openExtend);
    document.getElementById('closeExtendModal').addEventListener('click', closeExtend);
    document.getElementById('cancelExtendBtn').addEventListener('click', closeExtend);
    overlay.addEventListener('click', function(e){ if (e.target === overlay) closeExtend(); });

    sendBtn.addEventListener('click', function () {
        var selected = overlay.querySelector('input[name=extendDuration]:checked');
        if (!selected) {
            errorEl.textContent = 'Please select an extension duration.';
            errorEl.style.display = 'block';
            return;
        }
        sendBtn.disabled = true; sendBtn.textContent = 'Sending…';
        var fd = new FormData();
        fd.append('session_id', SESSION_ID);
        fd.append('duration_minutes', selected.value);
        fetch('/counselor/sessions/workspace?ajax=request_extension&session_id=' + SESSION_ID, { method:'POST', body:fd })
        .then(function(r){ return r.json(); })
        .then(function(data){
            if (data.success) {
                location.reload();
            } else {
                errorEl.textContent = data.error || 'Could not send request.';
                errorEl.style.display = 'block';
                sendBtn.disabled = false; sendBtn.textContent = 'Send Request';
            }
        })
        .catch(function(){
            errorEl.textContent = 'Network error. Please try again.';
            errorEl.style.display = 'block';
            sendBtn.disabled = false; sendBtn.textContent = 'Send Request';
        });
    });
})();
</script>
<?php endif; ?>
</body>
</html>
