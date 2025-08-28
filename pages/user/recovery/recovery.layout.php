<!DOCTYPE html>
<html lang="en">
<?php
$pageScripts = [
    '/assets/js/components/toast.js',
    '/assets/js/user/recovery/recovery.js',
];
?>
<?php require_once __DIR__ . '/../common/user.html.head.php'; ?>

<body>
    <main class="main-container">
        <?php
        $activePage = 'recovery';
        require_once __DIR__ . '/../common/user.sidebar.php';
        ?>

        <section class="main-content">
            <img
                src="/assets/img/main-content-head.svg"
                alt="Main Content Head background"
                class="main-header-bg-image" />

            <div class="main-content-header">
                <div class="main-content-header-text">
                    <h2>Recovery Plan</h2>
                    <p>Track your progress and stay on course.</p>
                </div>

                <div class="card-container">
                    <div class="card days-sober-card">
                        <div class="days-sober-content">
                            <p>DAYS SOBER</p>
                            <i data-lucide="heart" stroke-width="1" style="color: #335346"></i>
                        </div>
                        <h2><?= $daysSober ?></h2>
                    </div>

                    <div class="card milestone-progress-card">
                        <p>PLAN PROGRESS</p>
                        <span><?= $progressPercentage ?>%</span>
                        <div class="progress" style="--value: <?= $progressPercentage ?>%">
                            <div class="bar"></div>
                            <div class="thumb" aria-label="Plan progress <?= $progressPercentage ?> percent"></div>
                        </div>
                    </div>
                </div>

                <img
                    src="/assets/img/recovery-head.svg"
                    alt="Recovery"
                    class="session-image" />
            </div>

            <div class="main-content-body">

                <!-- Flash messages -->
                <?php if ($flashStarted): ?>
                <div class="success-message" style="margin:var(--spacing-md) var(--spacing-2xl) 0;display:flex;align-items:center;gap:8px;">
                    <i data-lucide="heart" style="width:16px;height:16px;flex-shrink:0;"></i>
                    <?= htmlspecialchars($flashStarted) ?>
                </div>
                <?php endif; ?>
                <?php if ($flashCheckin): ?>
                <div class="success-message" style="margin:var(--spacing-md) var(--spacing-2xl) 0;"><?= htmlspecialchars($flashCheckin) ?></div>
                <?php endif; ?>
                <?php if ($flashUrge): ?>
                <div class="success-message" style="margin:var(--spacing-md) var(--spacing-2xl) 0;"><?= htmlspecialchars($flashUrge) ?></div>
                <?php endif; ?>
                <?php if (!empty($_GET['followupRequested'])): ?>
                <div class="success-message" style="margin:var(--spacing-md) var(--spacing-2xl) 0;display:flex;align-items:center;gap:8px;">
                    <i data-lucide="send" style="width:16px;height:16px;flex-shrink:0;"></i>
                    Follow-up request sent! Your counselor will create a new plan for you soon.
                </div>
                <?php endif; ?>
                <?php if ($flashEcSaved): ?>
                <div class="success-message" style="margin:var(--spacing-md) var(--spacing-2xl) 0;"><?= htmlspecialchars($flashEcSaved) ?></div>
                <?php endif; ?>
                <?php if (!empty($_GET['taskBlocked'])): ?>
                <div class="error-message" style="margin:var(--spacing-md) var(--spacing-2xl) 0;">
                    Complete all tasks in the current phase before moving to the next.
                </div>
                <?php endif; ?>

                <!-- Quick actions -->
                <div class="recovery-quick-actions">
                    <a href="/user/recovery/checkin" class="recovery-quick-btn <?= $checkedInToday ? 'done' : '' ?>">
                        <i data-lucide="<?= $checkedInToday ? 'check-circle-2' : 'clipboard-list' ?>" style="width:16px;height:16px;"></i>
                        <?= $checkedInToday ? 'Check-in Done' : 'Daily Check-in' ?>
                    </a>
                    <a href="/user/recovery/log-urge" class="recovery-quick-btn">
                        <i data-lucide="activity" style="width:16px;height:16px;"></i>
                        Log an Urge
                    </a>
                    <a href="/user/recovery/urge-history" class="recovery-quick-btn">
                        <i data-lucide="list" style="width:16px;height:16px;"></i>
                        Urge History
                    </a>
                    <a href="/user/recovery/journal" class="recovery-quick-btn">
                        <i data-lucide="book-open" style="width:16px;height:16px;"></i>
                        Journal
                    </a>
                    <a href="/user/recovery/progress" class="recovery-quick-btn">
                        <i data-lucide="bar-chart-2" style="width:16px;height:16px;"></i>
                        Progress
                    </a>
                    <a href="/user/recovery/task/change-requests" class="recovery-quick-btn">
                        <i data-lucide="file-pen-line" style="width:16px;height:16px;"></i>
                        Change Requests
                    </a>
                </div>

                <!-- Plan status alert banner -->
                <div class="recovery-plan-banner">
                    <?php require __DIR__ . '/../common/user.recovery-header.php'; ?>
                </div>

                <div class="inner-body-content">

                    <!-- Column 1: Tasks & Goals -->
                    <div class="body-column">
                        <?php require __DIR__ . '/../common/user.daily-tasks.php'; ?>
                        <?php require __DIR__ . '/../common/user.goals-rewards.php'; ?>
                    </div>

                    <!-- Column 2: Progress & Counselor -->
                    <div class="body-column">
                        <?php require __DIR__ . '/../common/user.progress-tracker.php'; ?>
                        <?php require __DIR__ . '/../common/user.counselor-support.php'; ?>
                    </div>

                    <!-- Column 3: Reflection & Tools -->
                    <div class="body-column">
                        <?php require __DIR__ . '/../common/user.daily-reflection.php'; ?>
                        <?php require __DIR__ . '/../common/user.coping-tools.php'; ?>
                    </div>

                </div>
            </div>
        </section>
    </main>

    <?php require_once __DIR__ . '/../common/user.footer.php'; ?>
</body>

</html>
