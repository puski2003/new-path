<?php
// receives: $data, $user — no DB calls, no logic
$goals      = $data['goals'];
$activePlan = $data['activePlan'];
$editGoal   = $data['editGoal'];

$formAction = $editGoal ? '/user/recovery/goal/update' : '/user/recovery/goal/create';
$formGoal   = $editGoal ?? ['title' => '', 'description' => '', 'goalType' => 'short_term', 'targetDays' => ''];
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
                <a href="/user/recovery" class="back-btn" aria-label="Back">
                    <i data-lucide="arrow-left" stroke-width="2" class="back-icon"></i>
                </a>
                <div>
                    <h2>My Goals</h2>
                    <?php if ($activePlan): ?>
                    <p style="font-size:var(--font-size-sm);color:var(--color-text-secondary);margin-top:2px;">
                        Linked to: <strong><?= htmlspecialchars($activePlan['title']) ?></strong>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            <p class="page-subtitle">Goals are always tied to your active recovery plan.</p>
        </div>

        <div class="main-content-body">
            <div class="plans-container" style="max-width:960px;">

                <!-- Flash messages -->
                <?php
                $goalMsgMap = [
                    'created'       => ['success', 'Goal created successfully.'],
                    'updated'       => ['success', 'Goal updated.'],
                    'deleted'       => ['success', 'Goal removed.'],
                    'progress_logged' => ['success', 'Progress logged!'],
                    'no_active_plan'  => ['error',   'You need an active recovery plan before setting goals.'],
                ];
                $gStatus = $_GET['status'] ?? '';
                $gMsg    = $_GET['msg'] ?? '';
                if ($gStatus && isset($goalMsgMap[$gMsg])):
                    [$gType, $gText] = $goalMsgMap[$gMsg];
                ?>
                <div class="<?= $gType === 'error' ? 'error-message' : 'success-message' ?>" style="margin:0 0 var(--spacing-lg);">
                    <?= htmlspecialchars($gText) ?>
                </div>
                <?php elseif ($gStatus === 'success'): ?>
                <div class="success-message" style="margin:0 0 var(--spacing-lg);">Action completed.</div>
                <?php elseif ($gStatus === 'error'): ?>
                <div class="error-message" style="margin:0 0 var(--spacing-lg);">Something went wrong.</div>
                <?php endif; ?>

                <?php if (!$activePlan): ?>
                <div class="plan-card" style="text-align:center;padding:var(--spacing-2xl);">
                    <i data-lucide="target" style="width:40px;height:40px;color:var(--color-text-muted);display:block;margin:0 auto var(--spacing-md);"></i>
                    <h4 style="margin-bottom:var(--spacing-sm);">No Active Plan</h4>
                    <p class="plan-description">Goals must be linked to an active recovery plan.</p>
                    <a href="/user/recovery/browse" class="btn btn-primary" style="margin-top:var(--spacing-md);display:inline-block;">Browse Plans</a>
                </div>

                <?php else: ?>

                <div style="display:grid;grid-template-columns:3fr 2fr;gap:var(--spacing-xl);align-items:start;">

                    <!-- Left: goal list -->
                    <div>
                        <div class="plans-section">
                            <h3 class="section-title" style="font-size:var(--font-size-base);margin-bottom:var(--spacing-md);">
                                <span class="section-icon active"><i data-lucide="target" stroke-width="2"></i></span>
                                Current Goals (<?= count($goals) ?>)
                            </h3>

                            <?php if (empty($goals)): ?>
                            <div class="plan-card" style="text-align:center;padding:var(--spacing-xl);">
                                <p class="plan-description" style="margin:0;">No goals yet. Add one using the form.</p>
                            </div>
                            <?php else: ?>
                            <div class="plans-list">
                                <?php foreach ($goals as $g):
                                    $isAchieved = $g['status'] === 'achieved';
                                    $typeLabel  = $g['goalType'] === 'short_term' ? 'Short-term' : 'Long-term';
                                    $typeColor  = $g['goalType'] === 'short_term' ? 'var(--color-primary-dark)' : '#7c3aed';
                                    $typeBg     = $g['goalType'] === 'short_term' ? 'var(--color-primary-light)' : '#ede9fe';
                                ?>
                                <div class="plan-card <?= $isAchieved ? '' : 'active' ?>" style="<?= $isAchieved ? 'border-left:4px solid var(--color-primary);opacity:0.8;' : '' ?>">
                                    <div class="plan-card-header">
                                        <div style="display:flex;align-items:center;gap:8px;flex:1;min-width:0;">
                                            <?php if ($isAchieved): ?>
                                            <i data-lucide="check-circle-2" style="width:16px;height:16px;color:var(--color-primary);flex-shrink:0;"></i>
                                            <?php endif; ?>
                                            <h4 class="plan-title" style="<?= $isAchieved ? 'text-decoration:line-through;color:var(--color-text-muted);' : '' ?>"><?= htmlspecialchars(mb_strimwidth($g['title'], 0, 40, '…')) ?></h4>
                                        </div>
                                        <span style="font-size:var(--font-size-xs);font-weight:600;padding:3px 10px;border-radius:var(--radius-pill);background:<?= $typeBg ?>;color:<?= $typeColor ?>;flex-shrink:0;">
                                            <?= $typeLabel ?>
                                        </span>
                                    </div>

                                    <?php if (!empty($g['description'])): ?>
                                    <p class="plan-description" style="margin-bottom:var(--spacing-sm);"><?= htmlspecialchars($g['description']) ?></p>
                                    <?php endif; ?>

                                    <div class="plan-progress" style="margin-bottom:var(--spacing-sm);">
                                        <div class="progress-bar" style="flex:1;">
                                            <div class="progress-fill" style="width:<?= $g['progressPercentage'] ?>%;<?= $isAchieved ? 'background:var(--color-primary);' : '' ?>"></div>
                                        </div>
                                        <span class="progress-text"><?= $g['currentProgress'] ?>/<?= $g['targetDays'] ?>d</span>
                                    </div>

                                    <div class="plan-actions" style="gap:8px;">
                                        <?php if (!$isAchieved): ?>
                                        <form method="post" action="/user/recovery/goal/log-progress" style="display:inline;">
                                            <input type="hidden" name="goal_id" value="<?= $g['goalId'] ?>" />
                                            <input type="hidden" name="days" value="1" />
                                            <button type="submit" class="btn btn-primary" style="padding:6px 14px;font-size:var(--font-size-xs);">
                                                <i data-lucide="plus" style="width:12px;height:12px;vertical-align:middle;"></i> +1 Day
                                            </button>
                                        </form>
                                        <a href="/user/recovery/goals?edit=<?= $g['goalId'] ?>" class="btn btn-secondary" style="padding:6px 14px;font-size:var(--font-size-xs);">
                                            <i data-lucide="pencil" style="width:12px;height:12px;vertical-align:middle;"></i> Edit
                                        </a>
                                        <?php else: ?>
                                        <span style="font-size:var(--font-size-xs);color:var(--color-primary);font-weight:600;padding:6px 0;">Achieved!</span>
                                        <?php endif; ?>

                                        <form method="post" action="/user/recovery/goal/delete" style="display:inline;margin-left:auto;">
                                            <input type="hidden" name="goal_id" value="<?= $g['goalId'] ?>" />
                                            <button type="submit" class="btn btn-secondary" style="padding:6px 12px;font-size:var(--font-size-xs);color:#ef4444;border-color:#fecaca;"
                                                onclick="return confirm('Delete this goal?')">
                                                <i data-lucide="trash-2" style="width:12px;height:12px;vertical-align:middle;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Right: create / edit form -->
                    <div>
                        <div class="plans-section">
                            <h3 class="section-title" style="font-size:var(--font-size-base);margin-bottom:var(--spacing-md);">
                                <span class="section-icon active">
                                    <i data-lucide="<?= $editGoal ? 'pencil' : 'plus-circle' ?>" stroke-width="2"></i>
                                </span>
                                <?= $editGoal ? 'Edit Goal' : 'Add New Goal' ?>
                            </h3>

                            <div class="plan-card active">
                                <form method="post" action="<?= $formAction ?>" style="display:flex;flex-direction:column;gap:var(--spacing-md);">
                                    <?php if ($editGoal): ?>
                                    <input type="hidden" name="goal_id" value="<?= $editGoal['goalId'] ?>" />
                                    <?php endif; ?>

                                    <div>
                                        <label style="font-size:var(--font-size-xs);font-weight:600;color:var(--color-text-secondary);display:block;margin-bottom:4px;">Goal Title *</label>
                                        <input type="text" name="title" required
                                            value="<?= htmlspecialchars($formGoal['title']) ?>"
                                            placeholder="e.g. Attend therapy 3 times a week"
                                            style="width:100%;padding:10px 14px;border:1px solid var(--color-border-primary);border-radius:var(--radius-sm);font-size:var(--font-size-sm);font-family:inherit;background:var(--color-bg-white);box-sizing:border-box;">
                                    </div>

                                    <div>
                                        <label style="font-size:var(--font-size-xs);font-weight:600;color:var(--color-text-secondary);display:block;margin-bottom:4px;">Goal Type *</label>
                                        <select name="goal_type" style="width:100%;padding:10px 14px;border:1px solid var(--color-border-primary);border-radius:var(--radius-sm);font-size:var(--font-size-sm);font-family:inherit;background:var(--color-bg-white);box-sizing:border-box;">
                                            <option value="short_term" <?= $formGoal['goalType']==='short_term'?'selected':'' ?>>Short-term (days to weeks)</option>
                                            <option value="long_term"  <?= $formGoal['goalType']==='long_term' ?'selected':'' ?>>Long-term (months)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label style="font-size:var(--font-size-xs);font-weight:600;color:var(--color-text-secondary);display:block;margin-bottom:4px;">Target Days *</label>
                                        <input type="number" name="target_days" required min="1" max="365"
                                            value="<?= htmlspecialchars($formGoal['targetDays']) ?>"
                                            placeholder="e.g. 21"
                                            style="width:100%;padding:10px 14px;border:1px solid var(--color-border-primary);border-radius:var(--radius-sm);font-size:var(--font-size-sm);font-family:inherit;background:var(--color-bg-white);box-sizing:border-box;">
                                        <p style="font-size:var(--font-size-xs);color:var(--color-text-muted);margin-top:4px;">How many days to reach this goal?</p>
                                    </div>

                                    <div>
                                        <label style="font-size:var(--font-size-xs);font-weight:600;color:var(--color-text-secondary);display:block;margin-bottom:4px;">Description <span style="font-weight:400;">(optional)</span></label>
                                        <textarea name="description" rows="3"
                                            placeholder="Why is this goal important to your recovery?"
                                            style="width:100%;padding:10px 14px;border:1px solid var(--color-border-primary);border-radius:var(--radius-sm);font-size:var(--font-size-sm);font-family:inherit;background:var(--color-bg-white);resize:vertical;box-sizing:border-box;"><?= htmlspecialchars($formGoal['description']) ?></textarea>
                                    </div>

                                    <div style="display:flex;gap:8px;">
                                        <button type="submit" class="btn btn-primary">
                                            <?= $editGoal ? 'Save Changes' : 'Add Goal' ?>
                                        </button>
                                        <?php if ($editGoal): ?>
                                        <a href="/user/recovery/goals" class="btn btn-secondary">Cancel</a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div><!-- /grid -->
                <?php endif; ?>

            </div>
        </div>
    </section>
</main>

<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
</body>
</html>
