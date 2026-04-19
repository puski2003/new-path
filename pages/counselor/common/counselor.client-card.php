<?php
$client ??= [];
$avatarUrl  = htmlspecialchars($client['avatarUrl'] ?? '/assets/img/avatar.png');
$name       = htmlspecialchars($client['name'] ?? 'Client');
$status     = htmlspecialchars($client['status'] ?? 'Active');
$sessions   = (int) ($client['sessionCount'] ?? 0);
$progress   = (int) ($client['progressPercentage'] ?? 0);
$clientId   = (int) ($client['id'] ?? 0);
$hasPlan    = !empty($client['latestPlanId']);
$planId     = (int) ($client['latestPlanId'] ?? 0);
?>
<div class="cc-client-row" data-name="<?= strtolower($name) ?>">

    <div class="cc-client-avatar">
        <img src="<?= $avatarUrl ?>" alt="<?= $name ?>" />
    </div>

    <div class="cc-client-info">
        <span class="cc-client-badge"><?= $status ?></span>
        <h3 class="cc-client-name"><?= $name ?></h3>
        <p class="cc-client-sub">Plan progress: <?= $progress ?>%</p>
        <div class="cc-client-pills">
            <span class="cc-pill">
                <i data-lucide="calendar-check" stroke-width="1"></i>
                <?= $sessions ?> session<?= $sessions !== 1 ? 's' : '' ?>
            </span>
            <span class="cc-pill">
                <i data-lucide="trending-up" stroke-width="1"></i>
                <?= $progress ?>% progress
            </span>
        </div>
    </div>

    <div class="cc-client-actions">
        <a href="/counselor/client-profile?id=<?= $clientId ?>" class="btn btn-primary" style="font-size:var(--font-size-xs);">
            <i data-lucide="user" style="width:14px;height:14px;margin-right:4px;" stroke-width="1"></i>
            View Profile
        </a>
        <?php if ($hasPlan): ?>
            <a href="/counselor/recovery-plans/view?planId=<?= $planId ?>" class="btn btn-secondary" style="font-size:var(--font-size-xs);">
                <i data-lucide="clipboard-list" style="width:14px;height:14px;margin-right:4px;" stroke-width="1"></i>
                View Plan
            </a>
        <?php else: ?>
            <a href="/counselor/recovery-plans/create?userId=<?= $clientId ?>" class="btn btn-secondary" style="font-size:var(--font-size-xs);">
                <i data-lucide="plus" style="width:14px;height:14px;margin-right:4px;" stroke-width="1"></i>
                Create Plan
            </a>
        <?php endif; ?>
    </div>

</div>
