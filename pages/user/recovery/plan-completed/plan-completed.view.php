<?php
$plan = $data['plan'];
$stats = $data['stats'];
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
                <h2>Plan Completed</h2>
            </div>
        </div>

        <div class="main-content-body">
            <div style="max-width:780px;margin:0 auto;padding:var(--spacing-xl);">

                <div style="background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark,#2a7a5e));border-radius:var(--radius-xl);padding:var(--spacing-2xl);text-align:center;color:#fff;margin-bottom:var(--spacing-xl);">
                    <i data-lucide="trophy" style="width:52px;height:52px;display:block;margin:0 auto var(--spacing-md);"></i>
                    <h2 style="font-size:var(--font-size-2xl,1.5rem);font-weight:var(--font-weight-bold);margin-bottom:var(--spacing-sm);">
                        You completed your plan!
                    </h2>
                    <p style="font-size:var(--font-size-base);opacity:0.9;margin-bottom:var(--spacing-lg);">
                        <?= htmlspecialchars($plan['title']) ?>
                    </p>

                    <div style="display:flex;justify-content:center;gap:var(--spacing-2xl);flex-wrap:wrap;">
                        <div>
                            <div style="font-size:2rem;font-weight:700;"><?= $plan['daysTaken'] ?></div>
                            <div style="font-size:var(--font-size-xs);opacity:0.85;text-transform:uppercase;letter-spacing:.05em;">Days taken</div>
                        </div>
                        <div>
                            <div style="font-size:2rem;font-weight:700;"><?= (int)$stats['daysSober'] ?></div>
                            <div style="font-size:var(--font-size-xs);opacity:0.85;text-transform:uppercase;letter-spacing:.05em;">Days sober</div>
                        </div>
                        <div>
                            <div style="font-size:2rem;font-weight:700;"><?= (int)$stats['urgesLogged'] ?></div>
                            <div style="font-size:var(--font-size-xs);opacity:0.85;text-transform:uppercase;letter-spacing:.05em;">Urges resisted</div>
                        </div>
                    </div>
                </div>

                <h3 style="font-size:var(--font-size-base);font-weight:var(--font-weight-semibold);color:var(--color-text-secondary);text-transform:uppercase;letter-spacing:.06em;margin-bottom:var(--spacing-md);">
                    What would you like to do next?
                </h3>

                <div style="display:flex;flex-direction:column;gap:var(--spacing-md);">

                    <?php if ($plan['counselorId']): ?>
                    <div class="plan-card" style="display:flex;align-items:center;gap:var(--spacing-lg);border:2px solid var(--color-primary);">
                        <div class="plan-icon" style="flex-shrink:0;background:var(--color-primary);color:#fff;">
                            <i data-lucide="user-check" style="width:22px;height:22px;"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <h4 style="font-size:var(--font-size-base);font-weight:var(--font-weight-semibold);margin-bottom:4px;">
                                Continue with <?= htmlspecialchars($plan['counselorName'] ?? 'your counselor') ?>
                            </h4>
                            <p style="font-size:var(--font-size-sm);color:var(--color-text-muted);margin:0;">
                                Send a follow-up request. Your counselor will be notified and create a new personalised plan for you.
                            </p>
                        </div>
                        <form method="post" action="/user/recovery/request-followup" style="flex-shrink:0;">
                            <input type="hidden" name="planId" value="<?= $plan['planId'] ?>" />
                            <button type="submit" class="btn btn-primary" style="white-space:nowrap;">
                                Request Follow-up
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>

                    <div class="plan-card" style="display:flex;align-items:center;gap:var(--spacing-lg);">
                        <div class="plan-icon" style="flex-shrink:0;">
                            <i data-lucide="layout-template" style="width:22px;height:22px;"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <h4 style="font-size:var(--font-size-base);font-weight:var(--font-weight-semibold);margin-bottom:4px;">
                                Start a new plan
                            </h4>
                            <p style="font-size:var(--font-size-sm);color:var(--color-text-muted);margin:0;">
                                Browse our library of evidence-based templates or request a custom plan from a counselor.
                            </p>
                        </div>
                        <a href="/user/recovery/browse" class="btn btn-secondary" style="flex-shrink:0;white-space:nowrap;">
                            Browse Plans
                        </a>
                    </div>

                </div>

                <div style="text-align:center;margin-top:var(--spacing-xl);">
                    <a href="/user/recovery" style="font-size:var(--font-size-sm);color:var(--color-text-muted);text-decoration:none;">
                        Back to Recovery Dashboard
                    </a>
                </div>

            </div>
        </div>
    </section>
</main>

<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
</body>
</html>