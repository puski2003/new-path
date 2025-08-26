<?php
$_defaultScripts = [
    '/assets/js/components/polling.js',
    '/assets/js/components/sidebar.js',
    '/assets/js/components/profile-menu.js',
    '/assets/js/components/notification-bell.js',
    '/assets/js/counselor/counselor-search.js',
    '/assets/js/components/followup-thread.js',
    '/assets/js/counselor/sessions/followUp.js',
];
$_pageScripts = [];
if (!empty($pageScripts)) {
    $_pageScripts = is_array($pageScripts) ? $pageScripts : [$pageScripts];
}
$_renderScripts = array_values(array_unique(array_merge($_defaultScripts, $_pageScripts)));
foreach ($_renderScripts as $_script):
    $_src = preg_match('#^(?:https?:)?//#', $_script) || str_starts_with($_script, '/')
        ? $_script
        : '/assets/js/' . ltrim($_script, '/') . '.js';
?>
<script src="<?= htmlspecialchars($_src) ?>"></script>
<?php endforeach; ?>
<script>
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}
</script>
