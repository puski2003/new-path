<?php
$activePage         = 'recovery';
$pageHeaderTitle    = 'Recovery Plans';
$pageHeaderSubtitle = 'Create and manage client recovery plans';
?>
<!DOCTYPE html>
<html lang="en">
<?php $pageTitle = 'Recovery Plans'; $pageStyle = ['counselor/recoveryPlans']; require __DIR__ . '/../common/counselor.html.head.php'; ?>
<body>
<main class="main-container theme-counselor">
    <?php require __DIR__ . '/../common/counselor.sidebar.php'; ?>

    <section class="main-content">
        <?php require __DIR__ . '/../common/counselor.page-header.php'; ?>

        <div class="main-content-body">

            <?php if (!empty($_GET['updated'])): ?>
                <div class="success-message" style="margin: 0 0 var(--spacing-md);">Plan updated successfully.</div>
            <?php endif; ?>
            
            <!-- Toolbar -->
            <div class="rp-toolbar">
                <div style="display:flex;align-items:center;gap:var(--spacing-md);flex-wrap:wrap;">
                    <span class="rp-toolbar-title">Created Plans</span>
                    
                </div>
                <div class="rp-filter">
                    <form method="GET" action="">
                        <select name="filter">
                            <option value="">All</option>
                            <option value="active" <?= isset($_GET['filter']) && $_GET['filter']==='active' ? 'selected':'' ?> >Active</option>
                            <option value="paused" <?= isset($_GET['filter']) && $_GET['filter']==='paused' ? 'selected':'' ?>>Paused</option>
                            <option value="completed" <?= isset($_GET['filter']) && $_GET['filter']==='completed' ? 'selected':'' ?>>Completed</option>
                            <option value="cancelled" <?= isset($_GET['filter']) && $_GET['filter']==='cancelled' ? 'selected':'' ?>>Cancelled</option>
                            <option value="draft" <?= isset($_GET['filter']) && $_GET['filter']==='draft' ? 'selected':'' ?>>Drafted</option>
                        </select>
                        <button class="btn btn-bg-light-green filter-button" type="submit">
                            <i data-lucide="filter" stroke-width="1" width="16" height="16" class="filter-icon"></i>
                            <span>Filter</span>
                        </button>
                    </form>
                </div>
                
                <div class="flex "style="gap:10px">
                    <a href="/counselor/recovery-plans/task-changes"
                       class="btn btn-secondary"
                       style="font-size:var(--font-size-xs);display:inline-flex;align-items:center;gap:6px;">
                        <i data-lucide="file-pen-line" style="width:14px;height:14px;" stroke-width="1.8"></i>
                        Change Requests<?= $pendingChangeRequestCount > 0 ? ' (' . $pendingChangeRequestCount . ')' : '' ?>
                    </a>
                    <a href="/counselor/recovery-plans/create" class="btn btn-primary">
                    
                   + Create New Plan
                    </a>

                    
                
                </div>
            </div>

            <?php if ($pendingChangeRequestCount > 0): ?>
                <div class="success-message" style="margin: 0 0 var(--spacing-md);display:flex;align-items:center;justify-content:space-between;gap:var(--spacing-md);flex-wrap:wrap;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <i data-lucide="inbox" style="width:16px;height:16px;flex-shrink:0;"></i>
                        <span><?= $pendingChangeRequestCount ?> pending change request<?= $pendingChangeRequestCount !== 1 ? 's' : '' ?> need review.</span>
                    </div>
                    <a href="/counselor/recovery-plans/task-changes" class="btn btn-secondary" style="font-size:var(--font-size-xs);">Review Requests</a>
                </div>
            <?php endif; ?>

            <!-- Plan cards -->
            <?php if (!empty($plans)): ?>
                <div class="rp-plans-container">
                    <?php foreach ($plans as $plan): ?>
                    <div class="rp-plan-row">

                        <div class="rp-plan-thumb">
                            <img src="/assets/img/plan.png" alt="Plan" />
                        </div>

                        <div class="rp-plan-info">
                            <span class="rp-plan-label">Recovery Plan</span>
                            <h3 class="rp-plan-title"><?= htmlspecialchars($plan['title']) ?></h3>
                            <p class="rp-plan-client">Client: <?= htmlspecialchars($plan['clientName']) ?></p>
                            <?php if (!empty($plan['description'])): ?>
                                <p class="rp-plan-desc"><?= htmlspecialchars($plan['description']) ?></p>
                            <?php endif; ?>
                            <div class="rp-plan-pills">
                                <span class="plan-status status-<?= htmlspecialchars($plan['status']) ?>">
                                    <?= htmlspecialchars(ucfirst($plan['status'])) ?>
                                </span>
                                <span class="rp-pill">
                                    <i data-lucide="trending-up" stroke-width="1"></i>
                                    <?= (int) $plan['progressPercentage'] ?>% progress
                                </span>
                            </div>
                        </div>

                        <div class="rp-plan-actions">
                            <a href="/counselor/recovery-plans/view?planId=<?= (int) $plan['planId'] ?>"
                               class="btn btn-primary" style="font-size:var(--font-size-xs);">
                                <i data-lucide="eye" style="width:14px;height:14px;margin-right:4px;" stroke-width="1"></i>
                                View Plan
                            </a>
                            <a href="/counselor/recovery-plans/view?planId=<?= (int) $plan['planId'] ?>"
                               class="btn btn-secondary" style="font-size:var(--font-size-xs);">
                                <i data-lucide="pencil" style="width:14px;height:14px;margin-right:4px;" stroke-width="1"></i>
                                Edit
                            </a> 
                            <a href="/counselor/recovery-plans/delete?planId=<?= (int) $plan['planId'] ?>"
                               onclick="return confirm('Are you sure you want to delete this plan?');"
                               class="btn btn-secondary" style="font-size:var(--font-size-xs);color:#f43a3a;">
                                <i data-lucide="trash-2" style="width:14px;height:14px;margin-right:4px;" stroke-width="1"></i>
                                Delete
                            </a>
                        </div>

                    </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif(!empty($filter)): ?>
                <div class="rp-empty">
                    <i data-lucide="clipboard-plus" stroke-width="1"></i>
                    <p>No "<?=htmlspecialchars($filter) ?>" plans </p>
                </div>
            <?php else: ?>
                <div class="rp-empty">
                    <i data-lucide="clipboard-plus" stroke-width="1"></i>
                    <p>No recovery plans created yet.</p>
                    <a href="/counselor/recovery-plans/create" class="btn btn-primary">Create First Plan</a>
                </div>
            <?php endif; ?>

        </div> 
    </section>
</main>
<script>lucide.createIcons();</script>
</body>
</html>
