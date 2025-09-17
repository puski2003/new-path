<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/../common/user.html.head.php'; ?>

<body>
    <main class="main-container">
        <?php
        $activePage = 'counseling';
        require_once __DIR__ . '/../common/user.sidebar.php';
        ?>

        <section class="main-content">
            <img src="/assets/img/main-content-head.svg"
                alt="Main Content Head background"
                class="main-header-bg-image" />

            <div class="main-content-header">
                <div class="main-content-header-text">
                    <h2>Counselors</h2>
                    <p>Your path to guidance starts here.</p>
                </div>

                <div style="width: 25%"></div>
                <img src="/assets/img/counselor-header.svg"
                    alt="Counselors"
                    class="counselors-image" />
            </div>

            <div class="main-content-body">
                <div class="session-detail-header">
                    <a class="back-btn" href="/user/counselors">
                        <i data-lucide="arrow-left" stroke-width="1.8" class="back-icon" aria-label="Back"></i>
                    </a>
                </div>

                <?php require __DIR__ . '/../common/user.counselor-profile-card.php'; ?>
                <?php require __DIR__ . '/../common/user.counselor-about-section.php'; ?>
                <?php require __DIR__ . '/../common/user.counselor-availability-section.php'; ?>
                <?php require __DIR__ . '/../common/user.counselor-reviews-section.php'; ?>
            </div>
        </section>
    </main>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
    <script src="/assets/js/user/counselors/counselors.js"></script>
</body>

</html>
