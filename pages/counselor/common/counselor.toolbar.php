<?php
/*
 * Counselor toolbar partial.
 *
 * Variables consumed (set before requiring this file):
 *   $searchPlaceholder   string   — placeholder text for search input (usually set by controller)
 *   $toolbarShowFilter   bool     — (optional, default true) show the filter button
 *   $toolbarActions      string   — (optional) extra HTML appended after the search bar
 */
$toolbarShowFilter = $toolbarShowFilter ?? true;
?>
<div class="dashboard-card counselor-toolbar-card">
    <div class="counselor-toolbar">
        <?php if ($toolbarShowFilter): ?>
            <button class="btn btn-bg-light-green filter-button" type="button">
                <i data-lucide="filter" stroke-width="1" width="16" height="16"></i>
                <span>Filter</span>
            </button>
        <?php endif; ?>
        <?php require __DIR__ . '/counselor.searchbar.php'; ?>
        <?php if (!empty($toolbarActions)): echo $toolbarActions; endif; ?>
    </div>
</div>
