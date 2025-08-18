<?php
$emptyIcon = $emptyIcon ?? 'message-circle';
$emptyTitle = $emptyTitle ?? 'Nothing here yet';
$emptyText = $emptyText ?? '';
?>
<div class="empty-state">
    <i data-lucide="<?= htmlspecialchars($emptyIcon) ?>" class="empty-state-icon" stroke-width="1.5"></i>
    <h4 class="empty-state-title"><?= htmlspecialchars($emptyTitle) ?></h4>
    <p class="empty-state-text"><?= htmlspecialchars($emptyText) ?></p>
</div>
