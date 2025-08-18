<?php

/**
 * Searchbar Component
 * Accepts a parameter 'placeholder' via variables
 */
$placeholder = $placeholder ?? "Search...";
?>
<div class="search-bar">
    <button class="search-button">
        <i data-lucide="search" stroke-width="1" class="search-icon"></i>
    </button>
    <input type="text" class="search-input" placeholder="<?= htmlspecialchars($placeholder) ?>" />
</div>