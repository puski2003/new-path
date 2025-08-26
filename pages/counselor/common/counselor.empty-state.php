<?php
/*
 * Counselor empty-state partial.
 *
 * Variables consumed (set before requiring this file):
 *   $emptyStateMessage   string   — primary message
 *   $emptyStateSubtext   string   — (optional) secondary hint
 *   $emptyStateAction    string   — (optional) raw HTML for a CTA button/link
 */
?>
<div class="counselor-empty-state">
    <p><?= htmlspecialchars($emptyStateMessage ?? 'Nothing here yet.') ?></p>
    <?php if (!empty($emptyStateSubtext)): ?>
        <p><?= htmlspecialchars($emptyStateSubtext) ?></p>
    <?php endif; ?>
    <?php if (!empty($emptyStateAction)): echo $emptyStateAction; endif; ?>
</div>
