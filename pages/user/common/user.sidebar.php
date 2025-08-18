<?php

/**
 * User Sidebar
 * $user is already set by user.head.php when this is included.
 * Expects $activePage to be set before including this file.
 */
$currentPath = Request::path();
?>
<!-- Mobile hamburger toggle (hidden on desktop via CSS) -->
<button class="sidebar-toggle" aria-label="Open navigation">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
    </svg>
</button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<?php

// Nav items: [label, href, lucide-icon-name, page-key]
$navItems = [
    ['Dashboard',      '/user/dashboard',      'home',      'dashboard'],
    ['Counselors',     '/user/counselors',      'user-star', 'counselors'],
    ['Recovery',       '/user/recovery',        'heart',     'recovery'],
    ['Community',      '/user/community',       'users',     'community'],
    ['Sessions',       '/user/sessions',        'video',     'sessions'],
    
];
?>
<section class="sidebar">
    <div class="logo-container">
        <img src="/assets/img/logo.svg" alt="Logo" class="logo" />
        <h1 class="logo-text">New<br />Path</h1>
    </div>

    <div class="nav">
        <?php foreach ($navItems as [$label, $href, $icon, $pageKey]): ?>
            <a href="<?= $href ?>" class="sidebar-nav-link">
                <div class="sidebar-item <?= (isset($activePage) && $activePage === $pageKey) ? 'sidebar-item--active' : '' ?>">
                    <i data-lucide="<?= $icon ?>" class="sidebar-icon" stroke-width="1"></i>
                    <span class="sidebar-text"><?= htmlspecialchars($label) ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="emergency-banner">
        <h1>Emergency Help</h1>
        <p>Instant support when you need it most.</p>
        <i data-lucide="shield-alert" stroke-width="1" class="emergency-illustration"></i>
        <a class="btn btn-primary" href="/user/help">Get Help Now</a>
    </div>

    <!-- User Info -->
    <div class="user-info"  style="cursor: pointer;">
    <div class="user-info-inner" id="userMenuBtn">
    <img src="<?= !empty($user['profilePictureUrl']) ? htmlspecialchars($user['profilePictureUrl']) : '/assets/img/avatar.png' ?>"
            alt="User Icon"
            class="user-icon" />
        <div class="user-details">
            <span class="user-name"><?= htmlspecialchars(explode(' ', $user['name'] ?? 'User')[0]) ?></span>
            <span class="user-role"><?= htmlspecialchars($user['role'] ?? 'User') ?></span>
        </div>
    </div>  
        <?php require_once __DIR__ . '/user.notification-bell.php'; ?>
        <div class="user-menu-dropdown" id="userMenuDropdown">
            <button class="menu-option" id="editProfileBtn">
                <i data-lucide="user" stroke-width="1"></i>
                <span>Edit Profile</span>
            </button>
            <form method="POST" action="/auth/logout" style="margin:0;">
                <button type="submit" class="menu-option" style="width:100%;background:none;border:none;cursor:pointer;">
                    <i data-lucide="log-out" stroke-width="1"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</section>

<!-- User Profile Modal -->
<?php require_once __DIR__ . '/user-profile-modal.php'; ?>