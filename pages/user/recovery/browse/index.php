<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/../recovery.model.php';

$plans = RecoveryModel::getSystemPlans();
$activePlans = RecoveryModel::getUserActivePlans((int)$user['id']);
$hasActivePlan = !empty($activePlans);
$pageTitle = 'Browse Recovery Plans';
$pageStyle = ['user/dashboard', 'user/manage-plans', 'user/browse-plans'];
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/../../common/user.html.head.php'; ?>
<body>
<main class="main-container">
    <?php $activePage = 'recovery'; require_once __DIR__ . '/../../common/user.sidebar.php'; ?>
    <section class="main-content">
        <img src="/assets/img/main-content-head.svg" alt="" class="main-header-bg-image" />

        <div class="main-content-header">
            <div class="main-content-header-text">
                <a href="/user/recovery/manage" class="back-btn" aria-label="Back">
                    <i data-lucide="arrow-left" stroke-width="2" class="back-icon"></i>
                </a>
                <h2>Browse Recovery Plans</h2>
            </div>
            <p class="page-subtitle">Choose an admin-created plan to start your recovery journey.</p>
        </div>

        <div class="main-content-body">
            <div style="max-width:860px;margin:0 auto;padding:var(--spacing-xl);">

                <?php if (isset($_GET['success']) && $_GET['success'] === 'adopted'): ?>
                <div class="success-message" style="margin-bottom:var(--spacing-lg);">
                    Plan adopted successfully. Any previously active plans have been paused.
                </div>
                <?php endif; ?>

                <?php if (empty($plans)): ?>
                <div class="empty-state">
                    <i data-lucide="inbox" style="width:40px;height:40px;color:var(--color-text-muted);display:block;margin:0 auto var(--spacing-md);"></i>
                    <h3>No plans available yet</h3>
                    <p>System plans haven't been added by an admin yet. Check back soon or .

                    </p>
                    <div>
                        <a href="/user/counselors">find a counselor</a>
                    </div>
                </div>
                <?php else: ?>
                <div class="plans-grid">
                    <?php foreach ($plans as $plan): ?>
                    <div class="plan-card" style="padding:0;overflow:hidden;">
                        <?php if (!empty($plan['image'])): ?>
                        <img src="<?= htmlspecialchars($plan['image']) ?>" alt="<?= htmlspecialchars($plan['title']) ?>"
                             style="width:100%;height:160px;object-fit:cover;display:block;" />
                        <?php else: ?>
                        <div style="width:100%;height:120px;background:linear-gradient(135deg,var(--color-primary-light,#e8f5e9),var(--color-primary,#2e7d32));display:flex;align-items:center;justify-content:center;">
                            <i data-lucide="clipboard-list" style="width:36px;height:36px;color:#fff;opacity:.8;"></i>
                        </div>
                        <?php endif; ?>
                        <div style="padding:var(--spacing-md);">
                            <h4 class="plan-title"><?= htmlspecialchars($plan['title']) ?></h4>
                            <?php if (!empty($plan['category'])): ?>
                            <div class="plan-meta" style="margin:4px 0 8px;">
                                <span class="plan-category"><?= htmlspecialchars($plan['category']) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($plan['description'])): ?>
                            <p class="plan-description"><?= htmlspecialchars($plan['description']) ?></p>
                            <?php endif; ?>
                            <button type="button"
                                    class="btn btn-primary adopt-plan-btn"
                                    style="width:100%;margin-top:var(--spacing-md);"
                                    data-plan-id="<?= (int)$plan['planId'] ?>"
                                    data-plan-title="<?= htmlspecialchars($plan['title'], ENT_QUOTES) ?>">
                                Adopt Plan
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </section>
</main>
<!-- Adopt Plan Confirmation Modal -->
<div id="adopt-modal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,0.45);align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:32px;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
            <div style="width:40px;height:40px;border-radius:50%;background:var(--color-primary-light,#e8f5e9);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i data-lucide="clipboard-list" style="width:20px;height:20px;color:var(--color-primary,#2e7d32);"></i>
            </div>
            <h3 style="margin:0;font-size:1.1rem;font-weight:700;color:var(--color-text-primary);">Activate This Plan?</h3>
        </div>
        <p style="margin:0 0 8px;color:var(--color-text-secondary);font-size:0.95rem;">
            You are about to activate <strong id="modal-plan-title"></strong>.
        </p>
        <?php if ($hasActivePlan): ?>
        <p style="margin:0 0 24px;color:var(--color-text-secondary);font-size:0.95rem;">
            Your current active plan will be <strong>paused</strong> and you can resume it later from Manage Plans.
        </p>
        <?php else: ?>
        <p style="margin:0 0 24px;color:var(--color-text-secondary);font-size:0.95rem;">
            This plan will be added to your active plans. You can manage it from Manage Plans.
        </p>
        <?php endif; ?>
        <div style="display:flex;gap:12px;justify-content:flex-end;">
            <button type="button" id="modal-cancel" class="btn" style="background:var(--color-bg-cream,#f5f5f0);color:var(--color-text-primary);border:none;padding:10px 20px;border-radius:50px;font-weight:600;cursor:pointer;">
                Cancel
            </button>
            <form id="adopt-form" method="post" action="/user/recovery/adopt" style="margin:0;">
                <input type="hidden" id="adopt-plan-id" name="planId" value="" />
                <button type="submit" class="btn btn-primary" style="padding:10px 24px;border-radius:50px;border:none;font-weight:600;cursor:pointer;">
                    Yes, Activate
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
lucide.createIcons();

const modal    = document.getElementById('adopt-modal');
const planTitle = document.getElementById('modal-plan-title');
const planIdInput = document.getElementById('adopt-plan-id');
const cancelBtn = document.getElementById('modal-cancel');

document.querySelectorAll('.adopt-plan-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        planTitle.textContent = btn.dataset.planTitle;
        planIdInput.value = btn.dataset.planId;
        modal.style.display = 'flex';
        lucide.createIcons();
    });
});

cancelBtn.addEventListener('click', () => {
    modal.style.display = 'none';
});

modal.addEventListener('click', e => {
    if (e.target === modal) modal.style.display = 'none';
});
</script>
</body>
</html>
