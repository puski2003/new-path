<?php
require_once __DIR__ . '/../../common/admin.head.php';

$planId = (int)(Request::get('planId') ?? 0);
if ($planId <= 0) {
    Response::redirect('/admin/recovery-plans');
}

Database::setUpConnection();
$planRs = Database::search(
    "SELECT * FROM system_plans WHERE plan_id = $planId LIMIT 1"
);
if (!$planRs || $planRs->num_rows === 0) {
    Response::redirect('/admin/recovery-plans');
}
$plan = $planRs->fetch_assoc();

$tasksRs = Database::search(
    "SELECT * FROM system_plan_tasks
     WHERE plan_id = $planId AND is_milestone = 0
     ORDER BY phase, sort_order"
);
$tasks = [];
while ($tasksRs && ($row = $tasksRs->fetch_assoc())) {
    $tasks[] = $row;
}

$milestonesRs = Database::search(
    "SELECT * FROM system_plan_tasks
     WHERE plan_id = $planId AND is_milestone = 1
     ORDER BY phase, sort_order"
);
$milestones = [];
while ($milestonesRs && ($row = $milestonesRs->fetch_assoc())) {
    $milestones[$row['phase']][] = $row;
}

$tasksByPhase = [];
foreach ($tasks as $t) {
    $tasksByPhase[(int)$t['phase']][] = $t;
}

$phaseNames = [1 => 'Stabilization', 2 => 'Reduction', 3 => 'Maintenance'];

$adoptionRs = Database::search(
    "SELECT COUNT(*) AS cnt FROM recovery_plans WHERE source_plan_id = $planId"
);
$adoptionCount = (int)(($adoptionRs ? $adoptionRs->fetch_assoc()['cnt'] : 0));

$pageTitle = 'View Recovery Plan';
$pageStyle = ['admin/dashboard'];
require_once __DIR__ . '/../../common/admin.html.head.php';
?>
<main class="admin-main-container">
    <?php require_once __DIR__ . '/../../common/admin.sidebar.php'; ?>
    <section class="admin-main-content">

        <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:12px;">
                <a href="/admin/recovery-plans" class="admin-button admin-button--ghost admin-button--sm">
                    <i data-lucide="arrow-left" style="width:14px;height:14px;margin-right:4px;"></i> Back
                </a>
                <h1 style="margin:0;"><?= htmlspecialchars($plan['title']) ?></h1>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="background:#e8f5e9;color:#2e7d32;padding:4px 14px;border-radius:20px;font-size:0.8rem;font-weight:600;">
                    <?= $adoptionCount ?> adoption<?= $adoptionCount !== 1 ? 's' : '' ?>
                </span>
                <?php if (!empty($plan['category'])): ?>
                <span style="background:#f3f4f6;color:#374151;padding:4px 14px;border-radius:20px;font-size:0.8rem;">
                    <?= htmlspecialchars($plan['category']) ?>
                </span>
                <?php endif; ?>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:var(--spacing-xl);margin-top:var(--spacing-xl);">

            <!-- Image -->
            <?php if (!empty($plan['image'])): ?>
            <div class="admin-sub-container-2" style="padding:0;overflow:hidden;">
                <img src="<?= htmlspecialchars($plan['image']) ?>" alt="<?= htmlspecialchars($plan['title']) ?>"
                     style="width:100%;max-height:300px;object-fit:cover;display:block;border-radius:var(--radius-lg);" />
            </div>
            <?php endif; ?>

            <!-- Overview -->
            <div class="admin-sub-container-2">
                <div class="cc-section-header"><h4>Overview</h4></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--spacing-lg);">
                    <?php if (!empty($plan['description'])): ?>
                    <div>
                        <div style="font-size:0.8rem;color:var(--color-text-muted);margin-bottom:4px;">Description</div>
                        <p style="margin:0;"><?= htmlspecialchars($plan['description']) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($plan['goal'])): ?>
                    <div>
                        <div style="font-size:0.8rem;color:var(--color-text-muted);margin-bottom:4px;">Primary Goal</div>
                        <p style="margin:0;"><?= htmlspecialchars($plan['goal']) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($plan['start_date'])): ?>
                    <div>
                        <div style="font-size:0.8rem;color:var(--color-text-muted);margin-bottom:4px;">Start Date</div>
                        <p style="margin:0;"><?= date('M j, Y', strtotime($plan['start_date'])) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($plan['end_date'])): ?>
                    <div>
                        <div style="font-size:0.8rem;color:var(--color-text-muted);margin-bottom:4px;">End Date</div>
                        <p style="margin:0;"><?= date('M j, Y', strtotime($plan['end_date'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Goals -->
            <?php if (!empty($plan['short_term_goal_title']) || !empty($plan['long_term_goal_title'])): ?>
            <div class="admin-sub-container-2">
                <div class="cc-section-header"><h4>Recovery Goals</h4></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--spacing-lg);">
                    <?php if (!empty($plan['short_term_goal_title'])): ?>
                    <div style="background:#f9fafb;border-radius:8px;padding:16px;">
                        <div style="font-size:0.75rem;color:var(--color-primary);font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Short Term · <?= (int)$plan['short_term_goal_days'] ?> days</div>
                        <p style="margin:0;font-weight:500;"><?= htmlspecialchars($plan['short_term_goal_title']) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($plan['long_term_goal_title'])): ?>
                    <div style="background:#f9fafb;border-radius:8px;padding:16px;">
                        <div style="font-size:0.75rem;color:var(--color-primary);font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Long Term · <?= (int)$plan['long_term_goal_days'] ?> days</div>
                        <p style="margin:0;font-weight:500;"><?= htmlspecialchars($plan['long_term_goal_title']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Phases & Tasks -->
            <div class="admin-sub-container-2">
                <div class="cc-section-header"><h4>Phases &amp; Tasks</h4></div>
                <?php if (empty($tasks) && empty($milestones)): ?>
                    <p style="color:var(--color-text-muted);">No tasks or milestones added to this plan.</p>
                <?php else: ?>
                <?php for ($p = 1; $p <= 3; $p++):
                    $phaseTasks = $tasksByPhase[$p] ?? [];
                    $phaseMilestones = $milestones[$p] ?? [];
                    if (empty($phaseTasks) && empty($phaseMilestones)) continue;
                ?>
                <div style="margin-bottom:var(--spacing-xl);">
                    <h5 style="font-size:0.9rem;font-weight:700;color:var(--color-primary);margin:0 0 10px;">
                        Phase <?= $p ?>: <?= $phaseNames[$p] ?>
                    </h5>
                    <?php if (!empty($phaseTasks)): ?>
                    <table class="admin-table" style="margin-bottom:var(--spacing-md);">
                        <thead class="admin-table-header">
                            <tr class="admin-table-row">
                                <th class="admin-table-th">Task</th>
                                <th class="admin-table-th">Type</th>
                                <th class="admin-table-th">Recurrence</th>
                            </tr>
                        </thead>
                        <tbody class="admin-table-body">
                            <?php foreach ($phaseTasks as $i => $t): ?>
                            <tr class="admin-table-row <?= $i % 2 === 0 ? 'admin-table-row--even' : 'admin-table-row--odd' ?>">
                                <td class="admin-table-td"><?= htmlspecialchars($t['title']) ?></td>
                                <td class="admin-table-td"><?= ucfirst(htmlspecialchars($t['task_type'])) ?></td>
                                <td class="admin-table-td"><?= $t['recurrence_pattern'] ? ucfirst(htmlspecialchars($t['recurrence_pattern'])) : 'One-time' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                    <?php if (!empty($phaseMilestones)): ?>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">
                        <?php foreach ($phaseMilestones as $m): ?>
                        <span style="background:#fff8e1;color:#b45309;padding:4px 12px;border-radius:20px;font-size:0.8rem;">
                            ⭐ <?= htmlspecialchars($m['title']) ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endfor; ?>
                <?php endif; ?>
            </div>

            <?php if (!empty($plan['notes'])): ?>
            <div class="admin-sub-container-2">
                <div class="cc-section-header"><h4>Notes</h4></div>
                <p style="margin:0;"><?= htmlspecialchars($plan['notes']) ?></p>
            </div>
            <?php endif; ?>

        </div>
    </section>
</main>
<?php require_once __DIR__ . '/../../common/admin.footer.php'; ?>
