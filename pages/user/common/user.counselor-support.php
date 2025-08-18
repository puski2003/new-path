<div class="col-1-row-2 dashboard-card">
    <div class="card-header">
        <h3>Counselor Support</h3>
        <i data-lucide="user-round" stroke-width="1"></i>
    </div>

    <div class="next-session">
        <div class="session-info">
            <span class="session-label">Next Session</span>
            <span class="session-time">
                <?= htmlspecialchars($nextSessionTime) ?>
                <?php if (!empty($counselorName)): ?>
                    with <?= htmlspecialchars($counselorName) ?>
                <?php endif; ?>
            </span>
        </div>
        <a href="/user/sessions" class="btn btn-primary btn-sm">View Sessions</a>
    </div>

    <div class="counselor-notes">
        <h4 class="notes-title">Notes from Counselor</h4>
        <p class="notes-content"><?= htmlspecialchars($counselorNotes) ?></p>
        <a href="/user/counselors" class="btn btn-bg-light-green">Find a Counselor</a>
    </div>
</div>
