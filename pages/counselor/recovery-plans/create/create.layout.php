<?php
$activePage         = 'recovery';
$pageHeaderTitle    = 'Recovery Plans';
$pageHeaderSubtitle = 'Create and manage client recovery plans';
$prefilledUserId    = (int) (Request::get('userId') ?? 0);
$pageScripts = [
    '/assets/js/components/toast.js',
    '/assets/js/counselor/recovery-plans/createRecoveryPlan.js',
];
?>
<!DOCTYPE html>
<html lang="en">
<?php $pageTitle = 'Create Recovery Plan'; $pageStyle = ['counselor/viewRecoveryPlan']; require __DIR__ . '/../../common/counselor.html.head.php'; ?>
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

            <!-- ── AI assistant strip ── -->
            <div class="rp-ai-strip">
                <div class="rp-ai-strip-header">
                    <i data-lucide="sparkles" stroke-width="1.5"></i>
                    AI Plan Assistant
                </div>
                <textarea id="aiPrompt" placeholder="Describe the recovery plan you want to create, e.g. 'A 3-month alcohol recovery plan for a 35-year-old with weekly therapy sessions'…"></textarea>
                <div>
                    <button type="button" id="generatePlanBtn" class="btn btn-primary" style="font-size:var(--font-size-sm);">
                        <i data-lucide="sparkles" style="width:14px;height:14px;margin-right:6px;" stroke-width="1.5"></i>
                        Generate Plan
                    </button>
                </div>
            </div>

            <!-- ── Create form ── -->
            <form id="createPlan-form" action="" method="post" class="rp-form">

                <!-- Plan Overview -->
                <div class="cc-section">
                    <div class="cc-section-header">
                        <h4>Plan Overview</h4>
                    </div>

                    <!-- Client selector -->
                    <div class="rp-form-group">
                        <label>Assigned to</label>
                        <button type="button" class="btn btn-secondary add-client-btn"
                                onclick="showClientDropdown()"
                                style="<?= $prefilledUserId > 0 ? 'display:none' : '' ?>">
                            + Add Client
                        </button>
                        <select id="assignedTo" name="userId"
                                style="display:<?= $prefilledUserId > 0 ? 'none' : 'none' ?>">
                            <option value="" disabled <?= $prefilledUserId <= 0 ? 'selected' : '' ?>>Select Client</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= (int) $client['id'] ?>"
                                    <?= $prefilledUserId === (int) $client['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($client['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="selectedClient" style="display:<?= $prefilledUserId > 0 ? 'flex' : 'none' ?>;align-items:center;gap:var(--spacing-sm);">
                            <?php if ($prefilledUserId > 0):
                                $selected = array_values(array_filter($clients, fn($c) => (int) $c['id'] === $prefilledUserId));
                                if (!empty($selected)): ?>
                                    <span><?= htmlspecialchars($selected[0]['name']) ?></span>
                                    <span class="remove-btn" onclick="removeClient()">x</span>
                            <?php endif; endif; ?>
                        </div>
                    </div>

                    <div class="rp-form-row">
                        <div class="rp-form-group">
                            <label for="title">Plan Title</label>
                            <input id="title" name="title" type="text" placeholder="Enter plan title" required />
                        </div>
                        <div class="rp-form-group">
                            <label for="category">Category</label>
                            <input id="category" name="category" type="text" placeholder="e.g., Substance Abuse" />
                        </div>
                    </div>
                    <input type="hidden" name="planType" value="counselor" />

                    <div class="rp-form-row">
                        <div class="rp-form-group">
                            <label for="planGoal">Plan Goal</label>
                            <input id="planGoal" name="planGoal" type="text" placeholder="Enter the main goal" />
                        </div>
                        <div class="rp-form-group">
                            <label>Time Period</label>
                            <div class="rp-date-row">
                                <input type="date" id="startDate" name="startDate" required />
                                <input type="date" id="endDate" name="targetCompletionDate" required />
                            </div>
                        </div>
                    </div>

                    <div class="rp-form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" placeholder="Describe the recovery plan…"></textarea>
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
                            <div class="phase-tasks" id="phase-<?= $phaseNum ?>-tasks"></div>
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
                            <textarea name="shortTermGoalTitle"
                                      id="shortTermGoalTitle"
                                      rows="3"
                                      placeholder="e.g., Complete first week sober"
                                      style="resize:vertical;"></textarea>
                            <div class="rp-goal-days">
                                <label>Target Days:</label>
                                <input type="number" name="shortTermGoalDays"
                                       id="shortTermGoalDays"
                                       placeholder="30" min="1" max="365" />
                            </div>
                        </div>
                        <div class="rp-goal-card">
                            <label>Long-term Goal</label>
                            <textarea name="longTermGoalTitle"
                                      id="longTermGoalTitle"
                                      rows="3"
                                      placeholder="e.g., Complete full recovery program"
                                      style="resize:vertical;"></textarea>
                            <div class="rp-goal-days">
                                <label>Target Days:</label>
                                <input type="number" name="longTermGoalDays"
                                       id="longTermGoalDays"
                                       placeholder="90" min="1" max="365" />
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
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="customNotes" style="max-width:100%;"
                                  placeholder="Add any private counselor notes…"></textarea>
                    </div>
                </div>

                <!-- Actions -->
                <div class="rp-action-bar">
                    <button type="submit" class="btn btn-primary">Save Plan</button>
                    <button type="button" class="btn btn-secondary" onclick="exportPDF()">Export PDF</button>
                </div>

            </form>

        </div>
    </section>
</main>
<?php require __DIR__ . '/../../common/counselor.footer.php'; ?>
</body>
</html>
