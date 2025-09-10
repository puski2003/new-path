<?php
$activePage         = 'clients';
$pageHeaderTitle    = 'Clients';
$pageHeaderSubtitle = 'Your client directory';
?>
<!DOCTYPE html>
<html lang="en">
<?php $pageTitle = 'Clients'; $pageStyle = ['counselor/clients']; require __DIR__ . '/../common/counselor.html.head.php'; ?>
<body>
<main class="main-container theme-counselor">
    <?php require __DIR__ . '/../common/counselor.sidebar.php'; ?>

    <section class="main-content">
        <?php require __DIR__ . '/../common/counselor.page-header.php'; ?>

        <div class="main-content-body">

            <!-- Toolbar: filter + search -->
            <div class="cc-toolbar">
                <button class="btn btn-bg-light-green filter-button" type="button">
                    <i data-lucide="filter" stroke-width="1" width="16" height="16" class="filter-icon"></i>
                    <span>Filter</span>
                </button>
                <?php require __DIR__ . '/../common/counselor.searchbar.php'; ?>
            </div>

            <!-- Client cards -->
            <?php if (!empty($clients)): ?>
                <div class="cc-clients-container">
                    <?php foreach ($clients as $client): ?>
                        <?php require __DIR__ . '/../common/counselor.client-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="cc-empty">
                    <i data-lucide="users" stroke-width="1"></i>
                    <p>No clients yet.</p>
                    <p>Clients will appear here once they book a session with you.</p>
                </div>
            <?php endif; ?>

        </div>
    </section>
</main>
<script>lucide.createIcons();</script>
</body>
</html>
