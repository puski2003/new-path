<?php
// Normalise $pageStyle to an array
$_pageStyles = [];
if (!empty($pageStyle)) {
    $_pageStyles = is_array($pageStyle) ? $pageStyle : [$pageStyle];
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'New Path') ?></title>
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/components/sidebar.css">
    <link rel="stylesheet" href="/assets/css/components/profile-menu.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="/assets/js/components/alert.js" defer></script>
    <?php foreach ($_pageStyles as $_css): ?>
        <?php $_href = preg_match('#^(?:https?:)?//#', $_css) || str_starts_with($_css, '/') ? $_css : '/assets/css/' . ltrim($_css, '/') . '.css'; ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($_href) ?>">
    <?php endforeach; ?>
</head>
