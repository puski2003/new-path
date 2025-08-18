<div class="col-3-row-1 dashboard-card">
    <div class="card-header">
        <h3>Goals &amp; Rewards</h3>
    </div>

    <?php if (!empty($shortTermGoal)): ?>
        <div class="goal-item">
            <div class="goal-header">
                <span class="goal-title"><?= htmlspecialchars($shortTermGoal['title']) ?></span>
                <span class="goal-days"><?= (int)$shortTermGoal['currentProgress'] ?>/<?= (int)$shortTermGoal['targetDays'] ?> Days</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= (int)$shortTermGoal['progressPercentage'] ?>%"></div>
            </div>
        </div>
    <?php else: ?>
        <div class="goal-item">
            <div class="goal-header">
                <span class="goal-title">Short Term Goal</span>
                <span class="goal-days">Not set</span>
            </div>
            <p class="goal-empty-text">No short-term goal has been set yet.</p>
        </div>
    <?php endif; ?>

    <?php if (!empty($longTermGoal)): ?>
        <div class="goal-item">
            <div class="goal-header">
                <span class="goal-title"><?= htmlspecialchars($longTermGoal['title']) ?></span>
                <span class="goal-days"><?= (int)$longTermGoal['currentProgress'] ?>/<?= (int)$longTermGoal['targetDays'] ?> Days</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= (int)$longTermGoal['progressPercentage'] ?>%"></div>
            </div>
        </div>
    <?php else: ?>
        <div class="goal-item">
            <div class="goal-header">
                <span class="goal-title">Long Term Goal</span>
                <span class="goal-days">Not set</span>
            </div>
            <p class="goal-empty-text">No long-term goal has been set yet.</p>
        </div>
    <?php endif; ?>

    <div class="rewards-section">
        <h4 class="rewards-title">Unlocked Rewards</h4>
        <div class="rewards-grid">
            <div class="reward-item <?= $progressPercentage >= 25 ? '' : 'locked' ?>">
                <i data-lucide="<?= $progressPercentage >= 25 ? 'trophy' : 'lock' ?>" class="sidebar-icon" stroke-width="1"></i>
                <span class="reward-text">25% Complete</span>
            </div>
            <div class="reward-item <?= $progressPercentage >= 50 ? '' : 'locked' ?>">
                <i data-lucide="<?= $progressPercentage >= 50 ? 'medal' : 'lock' ?>" class="sidebar-icon" stroke-width="1"></i>
                <span class="reward-text">Halfway There</span>
            </div>
            <div class="reward-item <?= $progressPercentage >= 100 ? '' : 'locked' ?>">
                <i data-lucide="<?= $progressPercentage >= 100 ? 'award' : 'lock' ?>" class="sidebar-icon" stroke-width="1"></i>
                <span class="reward-text">Plan Complete</span>
            </div>
        </div>
    </div>
</div>
