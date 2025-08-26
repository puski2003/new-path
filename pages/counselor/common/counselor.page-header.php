<?php
/*
 * Counselor page header partial.
 *
 * Variables consumed (set before requiring this file):
 *   $pageHeaderTitle    string   — main heading
 *   $pageHeaderSubtitle string   — sub-heading paragraph
 *   $pageHeaderCards    string   — (optional) raw HTML placed inside .card-container
 */
?>
<img src="/assets/img/main-content-head.svg" alt="" class="main-header-bg-image" />
<div class="main-content-header">
    <div class="main-content-header-text">
        <h2><?= htmlspecialchars($pageHeaderTitle ?? '') ?></h2>
        <p><?= htmlspecialchars($pageHeaderSubtitle ?? '') ?></p>
    </div>
    <?php if (!empty($pageHeaderCards)): ?>
        <div class="card-container"><?= $pageHeaderCards ?></div>
    <?php endif; ?>
</div>
