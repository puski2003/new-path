<?php
if (!isset($pagination) || !is_array($pagination)) {
    return;
}

$forceShow = $forceShow ?? false;
$basePath = $basePath ?? Request::path();
$query = isset($query) && is_array($query) ? $query : $_GET;
unset($query['page']);

$currentPage = (int) ($pagination['currentPage'] ?? 1);
$totalPages = (int) ($pagination['totalPages'] ?? 1);
$fromRow = (int) ($pagination['fromRow'] ?? 0);
$toRow = (int) ($pagination['toRow'] ?? 0);
$totalRows = (int) ($pagination['totalRows'] ?? 0);

if (!$forceShow && $totalPages <= 1 && $totalRows === 0) {
    return;
}

$makeUrl = static function (int $page) use ($basePath, $query): string {
    $params = $query;
    $params['page'] = $page;
    return $basePath . '?' . http_build_query($params);
};

$windowStart = max(1, $currentPage - 2);
$windowEnd = min($totalPages, $currentPage + 2);
?>
<div class="admin-pagination">
    <div class="admin-pagination__summary">
        Showing <?= $fromRow ?>-<?= $toRow ?> of <?= $totalRows ?>
    </div>

    <div class="admin-pagination__controls">
        <?php if ($currentPage > 1): ?>
            <a class="admin-pagination__link" href="<?= htmlspecialchars($makeUrl($currentPage - 1)) ?>">Prev</a>
        <?php else: ?>
            <span class="admin-pagination__link admin-pagination__link--disabled">Prev</span>
        <?php endif; ?>

        <?php if ($windowStart > 1): ?>
            <a class="admin-pagination__link" href="<?= htmlspecialchars($makeUrl(1)) ?>">1</a>
            <?php if ($windowStart > 2): ?>
                <span class="admin-pagination__ellipsis">...</span>
            <?php endif; ?>
        <?php endif; ?>

        <?php for ($page = $windowStart; $page <= $windowEnd; $page++): ?>
            <?php if ($page === $currentPage): ?>
                <span class="admin-pagination__link admin-pagination__link--active"><?= $page ?></span>
            <?php else: ?>
                <a class="admin-pagination__link" href="<?= htmlspecialchars($makeUrl($page)) ?>"><?= $page ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($windowEnd < $totalPages): ?>
            <?php if ($windowEnd < $totalPages - 1): ?>
                <span class="admin-pagination__ellipsis">...</span>
            <?php endif; ?>
            <a class="admin-pagination__link" href="<?= htmlspecialchars($makeUrl($totalPages)) ?>"><?= $totalPages ?></a>
        <?php endif; ?>

        <?php if ($currentPage < $totalPages): ?>
            <a class="admin-pagination__link" href="<?= htmlspecialchars($makeUrl($currentPage + 1)) ?>">Next</a>
        <?php else: ?>
            <span class="admin-pagination__link admin-pagination__link--disabled">Next</span>
        <?php endif; ?>
    </div>
</div>
