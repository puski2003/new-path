<?php
$activePlans = $data['activePlans'];
$pendingPlans = $data['pendingPlans'];
$pausedPlans = $data['pausedPlans'];
$success = $data['success'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/../../common/user.html.head.php'; ?>
<body>
<main class="main-container">
    <?php $activePage = 'recovery'; require_once __DIR__ . '/../../common/user.sidebar.php'; ?>

    <section class="main-content">
        <img
            src="/assets/img/main-content-head.svg"
            alt="Main Content Head background"
            class="main-header-bg-image"
        />

        <div class="main-content-header">
            <div class="main-content-header-text">
                <a href="/user/recovery" class="back-btn" aria-label="Back to recovery">
                    <i data-lucide="arrow-left" stroke-width="2" class="back-icon"></i>
                </a>
                <h2>Manage Recovery Plans</h2>
            </div>
            <div style="display:flex;align-items:center;gap:var(--spacing-md);padding-right:var(--spacing-lg);">
                <p class="page-subtitle" style="margin:0;">View and activate your recovery plans</p>
                <a href="/user/recovery/browse" class="btn btn-primary" style="white-space:nowrap;">
                    <i data-lucide="layout-template" style="width:14px;height:14px;margin-right:6px;vertical-align:-2px;"></i>
                    Browse Recovery Plans
                </a>
            </div>
        </div>

        <div class="main-content-body">
            <?php if ($success === 'adopted'): ?>
            <div class="success-message" style="margin:var(--spacing-md) var(--spacing-2xl) 0;">
                Plan activated successfully. Any previously active plans have been paused.
            </div>
            <?php endif; ?>
            <?php if ($success === 'resumed'): ?>
            <div class="success-message" style="margin:var(--spacing-md) var(--spacing-2xl) 0;">
                Plan resumed. Your previous active plan has been paused.
            </div>
            <?php endif; ?>
            <div class="plans-container">
                <?php if (!empty($activePlans)): ?>
                    <div class="plans-section">
                        <h3 class="section-title">
                            <span class="section-icon active">
                                <i data-lucide="check-circle" stroke-width="2"></i>
                            </span>
                            Active Plans
                        </h3>
                        <div class="plans-list">
                            <?php foreach ($activePlans as $plan): ?>
                                <div class="plan-card active">
                                    <div class="plan-card-header">
                                        <h4 class="plan-title"><?= htmlspecialchars($plan['title']) ?></h4>
                                        <span class="plan-status status-active">Active</span>
                                    </div>
                                    <p class="plan-description"><?= htmlspecialchars($plan['description']) ?></p>
                                    <div class="plan-progress">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?= (int)$plan['progressPercentage'] ?>%"></div>
                                        </div>
                                        <span class="progress-text"><?= (int)$plan['progressPercentage'] ?>%</span>
                                    </div>
                                    <div class="plan-actions">
                                        <a href="/user/recovery/view?planId=<?= (int)$plan['planId'] ?>" class="btn btn-primary">View Plan</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($pendingPlans)): ?>
                    <div class="plans-section">
                        <h3 class="section-title">
                            <span class="section-icon pending">
                                <i data-lucide="clock" stroke-width="2"></i>
                            </span>
                            Pending Approval
                        </h3>
                        <div class="plans-list">
                            <?php foreach ($pendingPlans as $plan): ?>
                                <div class="plan-card pending">
                                    <div class="plan-card-header">
                                        <h4 class="plan-title"><?= htmlspecialchars($plan['title']) ?></h4>
                                        <span class="plan-status status-pending">Pending</span>
                                    </div>
                                    <p class="plan-description"><?= htmlspecialchars($plan['description']) ?></p>
                                    <div class="plan-info">
                                        <span>Assigned by your counselor</span>
                                    </div>
                                    <div class="plan-actions">
                                        <form action="/user/recovery/accept" method="post" style="display: inline">
                                            <input type="hidden" name="planId" value="<?= (int)$plan['planId'] ?>" />
                                            <button type="submit" class="btn btn-primary">Accept Plan</button>
                                        </form>
                                        <a href="/user/recovery/view?planId=<?= (int)$plan['planId'] ?>" class="btn btn-secondary">View Details</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($pausedPlans)): ?>
                    <div class="plans-section">
                        <h3 class="section-title">
                            <span class="section-icon paused">
                                <i data-lucide="pause-circle" stroke-width="2"></i>
                            </span>
                            Paused Plans
                        </h3>
                        <div class="plans-list">
                            <?php foreach ($pausedPlans as $plan): ?>
                                <div class="plan-card paused">
                                    <div class="plan-card-header">
                                        <h4 class="plan-title"><?= htmlspecialchars($plan['title']) ?></h4>
                                        <span class="plan-status status-paused">Paused</span>
                                    </div>
                                    <p class="plan-description"><?= htmlspecialchars($plan['description']) ?></p>
                                    <div class="plan-progress">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?= (int)$plan['progressPercentage'] ?>%"></div>
                                        </div>
                                        <span class="progress-text"><?= (int)$plan['progressPercentage'] ?>%</span>
                                    </div>
                                    <div class="plan-actions">
                                        <form action="/user/recovery/resume" method="post" style="display: inline">
                                            <input type="hidden" name="planId" value="<?= (int)$plan['planId'] ?>" />
                                            <button type="submit" class="btn btn-primary">Resume Plan</button>
                                        </form>
                                        <a href="/user/recovery/view?planId=<?= (int)$plan['planId'] ?>" class="btn btn-secondary">View Details</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (empty($activePlans) && empty($pendingPlans) && empty($pausedPlans)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i data-lucide="clipboard-list" stroke-width="1.5"></i>
                        </div>
                        <h3>No Recovery Plans</h3>
                        <p>You don't have any active recovery plans yet.</p>
                        <a href="/user/recovery/browse" class="btn btn-primary">Browse Recovery Plans</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
</body>
</html>