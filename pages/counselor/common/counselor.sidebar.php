<?php
$navItems = [
    ['Today',           '/counselor/dashboard',                  'home',           'dashboard'],
    ['Schedule',        '/counselor/sessions',                   'calendar-days',  'sessions'],
    ['Clients',         '/counselor/clients',                    'heart-pulse',    'clients'],
    ['Recovery Plans',  '/counselor/recovery-plans',             'clipboard-plus', 'recovery'],
    ['Finances',        '/counselor/finances',                   'wallet',         'finances'],
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
                    <i data-lucide="<?= htmlspecialchars($icon) ?>" class="sidebar-icon" stroke-width="1"></i>
                    <span class="sidebar-text"><?= htmlspecialchars($label) ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Counselor Info -->
    <div class="user-info" style="cursor: pointer;">
        <div class="user-info-inner" id="counselorMenuBtn">
            <img src="<?= htmlspecialchars($currentCounselor['profilePictureUrl'] ?? '/assets/img/avatar.png') ?>"
                 alt="Counselor Icon"
                 class="user-icon" />
            <div class="user-details">
                <span class="user-name"><?= htmlspecialchars(explode(' ', $currentCounselor['displayName'] ?? 'Counselor')[0]) ?></span>
                <span class="user-role"><?= htmlspecialchars($currentCounselor['title'] ?? 'Counselor') ?></span>
            </div>
        </div>
        <?php require_once ROOT . '/core/notification-bell.php'; ?>
        <div class="user-menu-dropdown" id="counselorMenuDropdown">
            <button class="menu-option" id="editCounselorProfileBtn" type="button">
                <i data-lucide="user" stroke-width="1"></i>
                <span>Edit Profile</span>
            </button>
            <button class="menu-option" id="counselorLogoutBtn" type="button">
                <i data-lucide="log-out" stroke-width="1"></i>
                <span>Logout</span>
            </button>
        </div>
    </div>
</section>

<form id="counselorLogoutForm" method="POST" action="/auth/logout" style="display: none;"></form>

<?php require_once __DIR__ . '/counselor-profile-modal.php'; ?>
<?php require_once __DIR__ . '/counselor.followup-popup.php'; ?>
