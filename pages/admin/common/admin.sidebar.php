<?php

/**
 * Admin Sidebar
 * $user is already set by admin.head.php when this is included.
 * The active page is determined by current URL path.
 */
$currentPath = Request::path();

// Nav items: [label, href, lucide-icon-name]
$navItems = [
    ['Dashboard',            '/admin/dashboard',            'layout-dashboard'],
    ['User Management',      '/admin/user-management',      'users'],
    ['Counselor Management', '/admin/counselor-management', 'user-check'],
    ['Sessions',             '/admin/sessions',             'video'],
    ['Analytics',            '/admin/analytics',            'bar-chart-2'],
    ['Recovery Plans',       '/admin/recovery-plans',       'heart-pulse'],
    ['Support Groups',       '/admin/support-groups',       'users-round'],
    ['Finances',             '/admin/finances',             'dollar-sign'],
    ['Content Management',   '/admin/content-management',   'file-text'],
    ['Help Centers',         '/admin/help-centers',         'circle-help'],
    // ['Settings',             '/admin/settings',             'settings'],
];
?>

<!-- Mobile hamburger toggle (hidden on desktop via CSS) -->
<button class="sidebar-toggle" aria-label="Open navigation">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
    </svg>
</button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<section class="sidebar">

    <div class="logo-container">
        <img src="/assets/img/logo.svg" alt="New Path Logo" class="logo">
        <h1 class="logo-text">New<br>Path</h1>
    </div>

    <div class="nav">
        <?php foreach ($navItems as [$label, $href, $icon]): ?>
            <a href="<?= htmlspecialchars($href) ?>" class="sidebar-nav-link">
                <div class="sidebar-item <?= str_starts_with($currentPath, $href) ? 'sidebar-item--active' : '' ?>">
                    <i data-lucide="<?= htmlspecialchars($icon) ?>" class="sidebar-icon" stroke-width="1"></i>
                    <span class="sidebar-text"><?= htmlspecialchars($label) ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Admin Info -->
    <div class="user-info" style="cursor: pointer;">
        <div class="user-info-inner" id="adminMenuBtn">
            <img src="/assets/img/avatar.png" alt="" class="user-icon">
            <div class="user-details">
                <span class="user-name"><?= htmlspecialchars(explode(' ', $user['name'] ?? 'Admin')[0]) ?></span>
                <span class="user-role">Admin</span>
            </div>
        </div>
        <?php require_once ROOT . '/core/notification-bell.php'; ?>
        <div class="user-menu-dropdown" id="adminMenuDropdown">
            <form method="POST" action="/auth/logout" style="margin:0;">
                <button type="submit" class="menu-option">
                    <i data-lucide="log-out" stroke-width="1"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>

</section>
