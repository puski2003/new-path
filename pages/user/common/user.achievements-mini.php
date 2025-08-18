<?php
// $achievements must be set by the including page via RecoveryModel::getUserAchievements($userId).
// Build a quick lookup of earned keys for the threshold checks below.
if (!isset($earnedKeys)) {
    $earnedKeys = isset($achievements)
        ? array_flip(array_column(array_filter($achievements, fn($a) => $a['earned']), 'key'))
        : [];
}
?>
<div class="achievements-mini">
    <h4 class="achievements-mini-title">Milestones</h4>
    <div class="achievements-badges">
        <div class="achievement-badge-item <?= isset($earnedKeys['sober_7d']) ? 'earned' : 'locked' ?>" title="7 Days Sober">
            <div class="badge-circle">
                <?php if (isset($earnedKeys['sober_7d'])): ?>
                    <span class="badge-icon">🗓</span>
                <?php else: ?>
                    <span class="badge-number">7</span>
                <?php endif; ?>
            </div>
            <span class="badge-label">7 Days</span>
        </div>

        <div class="achievement-badge-item <?= isset($earnedKeys['sober_30d']) ? 'earned' : 'locked' ?>" title="30 Days Sober">
            <div class="badge-circle">
                <?php if (isset($earnedKeys['sober_30d'])): ?>
                    <span class="badge-icon">🏅</span>
                <?php else: ?>
                    <span class="badge-number">30</span>
                <?php endif; ?>
            </div>
            <span class="badge-label">1 Month</span>
        </div>

        <div class="achievement-badge-item <?= isset($earnedKeys['sober_90d']) ? 'earned' : 'locked' ?>" title="90 Days Sober">
            <div class="badge-circle">
                <?php if (isset($earnedKeys['sober_90d'])): ?>
                    <span class="badge-icon">🏆</span>
                <?php else: ?>
                    <span class="badge-number">90</span>
                <?php endif; ?>
            </div>
            <span class="badge-label">3 Months</span>
        </div>

        <div class="achievement-badge-item <?= isset($earnedKeys['sober_180d']) ? 'earned' : 'locked' ?>" title="180 Days Sober">
            <div class="badge-circle">
                <?php if (isset($earnedKeys['sober_180d'])): ?>
                    <span class="badge-icon">⭐</span>
                <?php else: ?>
                    <span class="badge-number">180</span>
                <?php endif; ?>
            </div>
            <span class="badge-label">6 Months</span>
        </div>
    </div>
</div>
