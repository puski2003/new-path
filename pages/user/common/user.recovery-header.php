<?php if (!empty($pendingPlans)): ?>
    <?php foreach ($pendingPlans as $pendingPlan): ?>
        <div class="recovery-header">
            <div class="recovery-status">
                <i data-lucide="bell-ring" stroke-width="1.5" style="color: var(--color-primary); flex-shrink: 0;"></i>
                <span class="status-text">Your counselor shared a new plan: "<?= htmlspecialchars($pendingPlan['title']) ?>"</span>
            </div>
            <div style="display: flex; gap: var(--spacing-sm); flex-shrink: 0;">
                <a href="/user/recovery/view?planId=<?= (int)$pendingPlan['planId'] ?>" class="btn btn-secondary btn-sm">
                    <i data-lucide="eye" stroke-width="1.5"></i>
                    View Plan
                </a>
                <form action="/user/recovery/accept" method="post" style="display: inline">
                    <input type="hidden" name="planId" value="<?= (int)$pendingPlan['planId'] ?>" />
                    <button type="submit" class="btn btn-primary btn-sm">Accept Plan</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (empty($pendingPlans) && empty($activePlans)): ?>
    <div class="recovery-header">
        <div class="recovery-status">
            <i data-lucide="clipboard-list" stroke-width="1.5" style="color: var(--color-primary); flex-shrink: 0;"></i>
            <span class="status-text">No active recovery plan. Browse available plans to get started!</span>
        </div>
        <a href="/user/recovery/browse" class="btn btn-primary btn-sm" style="flex-shrink: 0;">
            <i data-lucide="search" stroke-width="1.5"></i>
            Browse Plans
        </a>
    </div>
<?php endif; ?>

<?php if (!empty($activePlans)): ?>
    <div class="recovery-header">
        <div class="recovery-status">
            <i data-lucide="activity" stroke-width="1.5" style="color: var(--color-primary); flex-shrink: 0;"></i>
            <span class="status-text">Active Plan: <?= htmlspecialchars($activePlans[0]['title']) ?></span>
        </div>
        <a href="/user/recovery/manage" class="btn btn-primary btn-sm" style="flex-shrink: 0;">
            <i data-lucide="settings-2" stroke-width="1.5" width="16" height="16"></i>
            Manage Plan
        </a>
    </div>
<?php endif; ?>
