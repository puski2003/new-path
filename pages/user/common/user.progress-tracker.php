<div class="col-1-row-1 dashboard-card">
    <div class="card-header">
        <h3>Progress Tracker</h3>
        <a href="/user/recovery/progress" class="view-all-link">
            View All
            <i data-lucide="arrow-right" stroke-width="2"></i>
        </a>
    </div>

    <div class="current-streak">
        <div class="streak-info">
            <span class="streak-number"><?= $daysSober ?></span>
            <span class="streak-label">Days Sober</span>
        </div>
        <div class="streak-ring">
            <svg viewBox="0 0 100 100" class="streak-svg">
                <circle cx="50" cy="50" r="45" fill="none" stroke="var(--color-progress-bg)" stroke-width="8" />
                <circle cx="50" cy="50" r="45" fill="none" stroke="var(--color-primary)" stroke-width="8"
                    stroke-dasharray="282.7"
                    stroke-dashoffset="<?= htmlspecialchars($strokeOffset) ?>"
                    transform="rotate(-90 50 50)" />
            </svg>
            <div class="streak-percentage"><?= $progressCirclePercentage ?>%</div>
        </div>
    </div>

    <div class="progress-stats">
        <div class="stat-item">
            <span class="stat-value"><?= $totalDaysTracked ?></span>
            <span class="stat-label">Days Tracked</span>
        </div>
        <div class="stat-item">
            <span class="stat-value"><?= $urgesLogged ?></span>
            <span class="stat-label">Urges Logged</span>
        </div>
        <div class="stat-item">
            <span class="stat-value"><?= $sessionsCompleted ?></span>
            <span class="stat-label">Sessions Done</span>
        </div>
    </div>

    <div class="progress-actions">
        <?php if (!$trackingStarted): ?>
            <form action="/user/recovery/start-sobriety" method="post" style="width: 100%">
                <button type="submit" class="btn btn-primary start-tracking-btn">Start Tracking</button>
            </form>
        <?php else: ?>
            <a href="/user/recovery/journal" class="btn btn-primary" style="text-align:center;">Log Progress</a>
            <button type="button" class="btn btn-secondary reset-btn" onclick="showResetModal()">Reset</button>
        <?php endif; ?>
    </div>

    <div id="resetSobrietyModal" class="modal-overlay" style="display: none">
        <div class="modal-content reset-modal">
            <h4>Reset Sobriety Counter?</h4>
            <p>This will reset your counter to Day 0. Your earned achievements will be preserved.</p>
            <form action="/user/recovery/reset-sobriety" method="post">
                <div class="form-group">
                    <label for="resetReason">Reason (optional):</label>
                    <textarea name="reason" id="resetReason" rows="2" placeholder="What happened?"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideResetModal()">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reset Counter</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showResetModal() { document.getElementById('resetSobrietyModal').style.display = 'flex'; }
        function hideResetModal() { document.getElementById('resetSobrietyModal').style.display = 'none'; }
    </script>
</div>
