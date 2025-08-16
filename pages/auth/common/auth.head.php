<?php

/**
 * Auth Head Component — pages/auth/common/auth.head.php
 *
 * Usage: include at the top of EVERY auth layout file.
 * Expects these variables to be set BEFORE including this file:
 *
 *   $pageTitle  (string)        — <title> text, e.g. "NewPath - Login"
 *   $authCss    (string|array)  — one CSS filename OR array of filenames
 *                                 relative to /assets/css/auth/
 *                                 e.g. "login.css" or ["login.css", "otp.css"]
 */

// Normalise $authCss to an array
$_authCssFiles = [];
if (!empty($authCss)) {
    $_authCssFiles = is_array($authCss) ? $authCss : [$authCss];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'NewPath') ?></title>

    <!-- Google Fonts: Montserrat -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Global design tokens + reset -->
    <link rel="stylesheet" href="/assets/css/global.css">
    <script src="/assets/js/components/alert.js" defer></script>

    <!-- Page-specific auth CSS -->
    <?php foreach ($_authCssFiles as $_css): ?>
        <link rel="stylesheet" href="/assets/css/auth/<?= htmlspecialchars($_css) ?>">
    <?php endforeach; ?>
</head>
