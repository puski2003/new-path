<?php
$activePage = 'recovery';

$shortGoal = null;
$longGoal  = null;
foreach ($goals as $goal) {
    if ($goal['goalType'] === 'short_term' && !$shortGoal) $shortGoal = $goal;
    if ($goal['goalType'] === 'long_term'  && !$longGoal)  $longGoal  = $goal;
}

$pageHeaderTitle    = 'Recovery Plans';
$pageHeaderSubtitle = 'Create and manage client recovery plans';
$pageScripts = [
    '/assets/js/components/toast.js',
    '/assets/js/counselor/recovery-plans/createRecoveryPlan.js',
];
?>
<!DOCTYPE html>
<html lang="en">
<?php $pageTitle = 'Edit Recovery Plan'; $pageStyle = ['counselor/viewRecoveryPlan']; require __DIR__ . '/../../common/counselor.html.head.php'; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<body>
<main class="main-container theme-counselor">
    <?php require __DIR__ . '/../../common/counselor.sidebar.php'; ?>

    <section class="main-content">
        <?php require __DIR__ . '/../../common/counselor.page-header.php'; ?>

        <div class="main-content-body">

            <!-- Back -->
            <div class="cc-back-row">
                <a class="cc-back-btn" href="/counselor/recovery-plans">
                    <i data-lucide="arrow-left" stroke-width="1.8"></i>
                    Back to Recovery Plans
                </a>
            </div>

            <?php if (!empty($errorMessage)): ?>
                <div class="error-message" style="margin: 0 var(--spacing-xl) var(--spacing-md);">
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <!-- ── Hero plan card ── -->
            <div class="rp-hero-card">
                <div class="rp-hero-image">
                    <img src="/assets/img/plan.png" alt="Recovery Plan" />
                </div>
                <div class="rp-hero-info">
                    <span class="rp-hero-label">Recovery Plan</span>
                    <h3 class="rp-hero-title"><?= htmlspecialchars($plan['title']) ?></h3>
                    <p class="rp-hero-client">
                        <i data-lucide="user" style="width:13px;height:13px;vertical-align:-2px;" stroke-width="1"></i>
                        <?= htmlspecialchars($plan['clientName']) ?>
                    </p>
                    <p class="rp-hero-meta">
                        Created by <?= htmlspecialchars($currentCounselor['displayName'] ?? 'Counselor') ?>
                        &nbsp;·&nbsp; Last edited: <?= htmlspecialchars($plan['updatedAt']) ?>
                    </p>
                    <span class="plan-status status-<?= htmlspecialchars($plan['status']) ?>" style="margin-top:var(--spacing-xs);">
                        <?= htmlspecialchars(ucfirst($plan['status'])) ?>
                    </span>
                </div>
                <div class="rp-hero-side">
                    <span class="rp-progress-label">Progress</span>
                    <span class="rp-progress-value"><?= (int) $plan['progressPercentage'] ?>%</span>
                    <div class="rp-progress-bar-hero">
                        <div class="rp-progress-fill-hero" style="width:<?= (int) $plan['progressPercentage'] ?>%;"></div>
                    </div>
                </div>
            </div>

            <!-- ── Edit form ── -->
            <form id="updatePlan-form" action="" method="post" class="rp-form">
                <input type="hidden" name="planId" value="<?= (int) $plan['planId'] ?>" />

                <!-- Plan Overview -->
                <div class="cc-section">
                    <div class="cc-section-header">
                        <h4>Plan Overview</h4>
                    </div>

                    <div class="rp-form-group">
                        <label for="assignedTo">Assigned to</label>
                        <select id="assignedTo" name="userId">
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= (int) $client['id'] ?>"
                                    <?= (int) $plan['userId'] === (int) $client['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($client['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="rp-form-row">
                        <div class="rp-form-group">
                            <label for="title">Plan Title</label>
                            <input id="title" name="title" type="text"
                                   value="<?= htmlspecialchars($plan['title']) ?>" required />
                        </div>
                        <div class="rp-form-group">
                            <label for="category">Category</label>
                            <input id="category" name="category" type="text"
                                   value="<?= htmlspecialchars($plan['category']) ?>" />
                        </div>
                    </div>
                    <input type="hidden" name="planType" value="counselor" />

                    <div class="rp-form-row">
                        <div class="rp-form-group">
                            <label for="planStatus">Status</label>
                            <select id="planStatus" name="planStatus">
                                <?php foreach (['draft', 'active', 'completed', 'paused', 'cancelled'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $plan['status'] === $s ? 'selected' : '' ?>>
                                        <?= ucfirst($s) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="rp-form-group">
                            <label>Time Period</label>
                            <div class="rp-date-row">
                                <input type="date" id="startDate" name="startDate"
                                       value="<?= htmlspecialchars($plan['startDate']) ?>" required />
                                <input type="date" id="targetCompletionDate" name="targetCompletionDate"
                                       value="<?= htmlspecialchars($plan['targetCompletionDate']) ?>" required />
                            </div>
                        </div>
                    </div>

                    <div class="rp-form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"><?= htmlspecialchars($plan['description']) ?></textarea>
                    </div>
                </div>

                <!-- Phases -->
                <div class="cc-section">
                    <div class="cc-section-header">
                        <h4>Phases</h4>
                    </div>

                    <div id="phases-container" class="rp-phases">
                        <?php foreach ([1 => 'Stabilization', 2 => 'Reduction', 3 => 'Maintenance'] as $phaseNum => $phaseName): ?>
                            <div class="phase" data-phase="<?= $phaseNum ?>">
                                <span class="phase-name">Phase <?= $phaseNum ?>: <?= htmlspecialchars($phaseName) ?></span>
                                <div class="phase-actions">
                                    <button type="button" class="btn btn-link phase-btn"
                                            onclick="addTask(<?= $phaseNum ?>)">+ Add Task</button>
                                    <button type="button" class="btn btn-link phase-btn"
                                            onclick="addMilestone(<?= $phaseNum ?>)">+ Add Milestone</button>
                                </div>
                            </div>
                            <div class="phase-tasks" id="phase-<?= $phaseNum ?>-tasks">
                                <?php foreach ($tasks as $task): if ((int) $task['phase'] !== $phaseNum) continue; ?>
                                    <div class="task-item">
                                        <input type="hidden" name="taskId[]"    value="<?= (int) $task['taskId'] ?>" />
                                        <input type="hidden" name="taskPhase[]" value="<?= (int) $task['phase'] ?>" />
                                        <input type="text" name="taskTitle[]"   value="<?= htmlspecialchars($task['title']) ?>"
                                               placeholder="Task description" />
                                        <select name="taskType[]">
                                            <?php foreach (['custom', 'journal', 'meditation', 'session', 'exercise'] as $tt): ?>
                                                <option value="<?= $tt ?>" <?= $task['taskType'] === $tt ? 'selected' : '' ?>>
                                                    <?= ucfirst($tt) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <select name="recurrencePattern[]">
                                            <?php foreach (['' => 'One-time', 'daily' => 'Daily', 'weekly' => 'Weekly', 'bi-weekly' => 'Bi-weekly'] as $val => $lbl): ?>
                                                <option value="<?= $val ?>"
                                                    <?= ($task['recurrencePattern'] ?? '') === $val ? 'selected' : '' ?>>
                                                    <?= $lbl ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span class="remove-btn" onclick="this.parentElement.remove()">×</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Recovery Goals -->
                <div class="cc-section">
                    <div class="cc-section-header">
                        <h4>Recovery Goals</h4>
                    </div>

                    <div class="rp-goals-grid">
                        <div class="rp-goal-card">
                            <label>Short-term Goal</label>
                            <input type="text" name="shortTermGoalTitle"
                                   value="<?= htmlspecialchars($shortGoal['title'] ?? '') ?>"
                                   placeholder="e.g., Complete first week sober" />
                            <div class="rp-goal-days">
                                <label>Target Days:</label>
                                <input type="number" name="shortTermGoalDays"
                                       value="<?= (int) ($shortGoal['targetDays'] ?? 0) ?>"
                                       min="1" max="365" />
                            </div>
                        </div>
                        <div class="rp-goal-card">
                            <label>Long-term Goal</label>
                            <input type="text" name="longTermGoalTitle"
                                   value="<?= htmlspecialchars($longGoal['title'] ?? '') ?>"
                                   placeholder="e.g., Complete full recovery program" />
                            <div class="rp-goal-days">
                                <label>Target Days:</label>
                                <input type="number" name="longTermGoalDays"
                                       value="<?= (int) ($longGoal['targetDays'] ?? 0) ?>"
                                       min="1" max="365" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Care Notes -->
                <div class="cc-section">
                    <div class="cc-section-header">
                        <h4>Care Notes</h4>
                    </div>
                    <div class="rp-form-group">
                        <label for="customNotes">Notes</label>
                        <textarea id="customNotes" name="customNotes" style="max-width:100%;"><?= htmlspecialchars($plan['customNotes']) ?></textarea>
                    </div>
                </div>

                <!-- Actions -->
                <div class="rp-action-bar">
                    <button type="submit" class="btn btn-primary">Update Plan</button>
                    <button type="button" class="btn btn-secondary" onclick="exportPDF()">Export PDF</button>
                </div>
            </form>

        </div>
    </section>
</main>
<div id="pdf-content" style="display:none; padding:20px; font-family:Arial;">

    <h1><?= htmlspecialchars($plan['title']) ?></h1>
    <br>
    <p><strong>Client:</strong> <?= htmlspecialchars($plan['clientName']) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($plan['status']) ?></p>
    <br>
    <hr>
    <br>
    <h3>Description</h3>
    <p><?= nl2br(htmlspecialchars($plan['description'])) ?></p>
    <br>
    <h3>Phases & Tasks</h3>
    <br>
    <?php foreach ([1 => 'Stabilization', 2 => 'Reduction', 3 => 'Maintenance'] as $phaseNum => $phaseName): ?>
        <h4>Phase <?= $phaseNum ?>: <?= $phaseName ?></h4>
        <ul>
        <?php foreach ($tasks as $task): if ((int)$task['phase'] !== $phaseNum) continue; ?>
            <li><?= htmlspecialchars($task['title']) ?> (<?= htmlspecialchars($task['taskType']) ?>)</li>
        <?php endforeach; ?>
        </ul>
    <?php endforeach; ?>
    <br>
    <h3>Goals</h3>
    <p><strong>Short-term:</strong> <?= htmlspecialchars($shortGoal['title'] ?? '') ?></p>
    <p><strong>Long-term:</strong> <?= htmlspecialchars($longGoal['title'] ?? '') ?></p>
    <br>
    <h3>Notes</h3>
    <p><?= nl2br(htmlspecialchars($plan['customNotes'])) ?></p>

</div>
<?php require __DIR__ . '/../../common/counselor.footer.php'; ?>
</body>
<script>
    function exportPDF() {
        
        const element=document.getElementById('pdf-content');
        const recoveryTitle = document.querySelector('.rp-hero-title').textContent;

        element.style.display = 'block';

        const opt = {
            margin: 0.5,
            filename: `${recoveryTitle}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
        };

        html2pdf()
            .set(opt)
            .from(element)
            .save()
            .then(() => {
                element.style.display = 'none'; // hide again
            });
    }
</script>
</html>

