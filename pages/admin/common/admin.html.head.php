<?php
$pageTitle = $pageTitle ?? 'Admin';
$pageStyle = $pageStyle ?? [];
$extraCss = $extraCss ?? [];
$extraJs = $extraJs ?? [];
$_pageStyles = is_array($pageStyle) ? $pageStyle : [$pageStyle];
$_extraCss = is_array($extraCss) ? $extraCss : [$extraCss];
$_renderCss = array_merge($_pageStyles, $_extraCss);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - New Path</title>
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/admin/layout.css">
    <link rel="stylesheet" href="/assets/css/admin/components/sidebar.css">
    <link rel="stylesheet" href="/assets/css/admin/components/button.css">
    <link rel="stylesheet" href="/assets/css/admin/components/dropdown.css">
    <link rel="stylesheet" href="/assets/css/admin/components/searchbar.css">
    <link rel="stylesheet" href="/assets/css/admin/components/table.css">
    <link rel="stylesheet" href="/assets/css/admin/components/table-action-button.css">
    <link rel="stylesheet" href="/assets/css/admin/components/summary-card.css">
    <link rel="stylesheet" href="/assets/css/admin/components/data-card.css">
    <link rel="stylesheet" href="/assets/css/admin/components/tab-nav.css">
    <link rel="stylesheet" href="/assets/css/admin/components/icon-button.css">
    <link rel="stylesheet" href="/assets/css/admin/components/pending-submission.css">
    <link rel="stylesheet" href="/assets/css/admin/components/content-management.css">
    <link rel="stylesheet" href="/assets/css/admin/components/recovery-plans.css">
    <link rel="stylesheet" href="/assets/css/admin/components/policy-card.css">
    <link rel="stylesheet" href="/assets/css/admin/components/alert-item.css">
    <link rel="stylesheet" href="/assets/css/admin/components/settings.css">
    <link rel="stylesheet" href="/assets/css/admin/components/session-appointments.css">
    <link rel="stylesheet" href="/assets/css/admin/components/date-input.css">
    <link rel="stylesheet" href="/assets/css/admin/components/text-input.css">
    <link rel="stylesheet" href="/assets/css/admin/components/toggle-switch.css">
    <link rel="stylesheet" href="/assets/css/admin/components/file-upload.css">
    <link rel="stylesheet" href="/assets/css/admin/components/pagination.css">
    <link rel="stylesheet" href="/assets/css/components/profile-menu.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script src="/assets/js/components/alert.js" defer></script>
    <?php foreach ($_renderCss as $css): ?>
        <?php if (empty($css)) continue; ?>
        <?php $_href = preg_match('#^(?:https?:)?//#', $css) || str_starts_with($css, '/') ? $css : '/assets/css/' . ltrim($css, '/') . '.css'; ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($_href) ?>">
    <?php endforeach; ?>
</head>
<body>
