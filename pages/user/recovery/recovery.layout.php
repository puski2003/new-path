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

                <!-- Flash message -->
                <?php if ($flashMsg): ?>
                <div class="<?= $flashType === 'error' ? 'error-message' : 'success-message' ?>" style="margin:var(--spacing-md) var(--spacing-2xl) 0;">
                    <?= htmlspecialchars($flashMsg) ?>
                </div>
                <?php endif; ?>

                <!-- Sobriety start prompt -->
                <?php if (!$trackingStarted): ?>
                <div style="margin:var(--spacing-lg) var(--spacing-2xl) 0;">
                    <div style="background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark,#2a7a5e));border-radius:var(--radius-xl);padding:var(--spacing-xl) var(--spacing-2xl);display:flex;align-items:center;gap:var(--spacing-xl);flex-wrap:wrap;">
                        <div style="flex-shrink:0;width:52px;height:52px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                            <i data-lucide="heart" style="width:26px;height:26px;color:#fff;" stroke-width="1.5"></i>
                        </div>
                        <div style="flex:1;min-width:200px;">
                            <h3 style="color:#fff;font-size:var(--font-size-lg);font-weight:700;margin-bottom:4px;">Start tracking your sobriety</h3>
                            <p style="color:rgba(255,255,255,0.85);font-size:var(--font-size-sm);margin:0;">Choose the date you began your journey. This is your Day 1.</p>
                        </div>
                        <form method="post" action="/user/recovery/start-sobriety" style="display:flex;align-items:center;gap:var(--spacing-sm);flex-wrap:wrap;flex-shrink:0;">
                            <input type="date"
                                   name="sobrietyDate"
                                   max="<?= date('Y-m-d') ?>"
                                   value="<?= date('Y-m-d') ?>"
                                   style="padding:10px 14px;border-radius:var(--radius-pill);border:none;font-size:var(--font-size-sm);font-family:inherit;background:rgba(255,255,255,0.95);color:var(--color-text-primary);font-weight:500;cursor:pointer;" />
                            <button type="submit"
                                    style="padding:10px 24px;background:#fff;color:var(--color-primary-dark,#2a7a5e);border:none;border-radius:var(--radius-pill);font-size:var(--font-size-sm);font-weight:700;cursor:pointer;white-space:nowrap;font-family:inherit;">
                                Set My Start Date
                            </button>
                        </form>
                    </div>
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
