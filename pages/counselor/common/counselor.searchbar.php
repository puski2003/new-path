<div class="search-bar">
    <button class="search-button" type="button">
        <i data-lucide="search" class="search-icon" stroke-width="1"></i>
    </button>
    <input type="text"
           class="search-input"
           placeholder="<?= htmlspecialchars($searchPlaceholder ?? 'Search') ?>"
           <?= !empty($searchFilterType) ? 'data-filter="' . htmlspecialchars($searchFilterType) . '"' : '' ?> />
</div>
