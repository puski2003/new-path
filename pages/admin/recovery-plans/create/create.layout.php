<?php
$pageTitle = 'Create Recovery Plan - Admin';
$pageStyle = ['admin/create-recovery-plan'];
$pageScripts = [
    '/assets/js/components/toast.js',
    'admin/recovery-plans/create',
];
require_once __DIR__ . '/../../common/admin.html.head.php';
?>

<main class="admin-main-container">
    <?php require_once __DIR__ . '/../../common/admin.sidebar.php'; ?>

    <section class="admin-main-content">
        <div class="page-header">
            <h1>Create Recovery Plan</h1>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="admin-alert admin-alert--error">
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <!-- AI assistant strip -->
        <div class="rp-ai-strip">
            <div class="rp-ai-strip-header">
                <i data-lucide="sparkles" stroke-width="1.5"></i>
                AI Plan Assistant
            </div>
            <textarea id="aiPrompt" placeholder="Describe the recovery plan you want to create, e.g. 'A 3-month alcohol recovery plan for a 35-year-old with weekly therapy sessions'…"></textarea>
            <div>
                <button type="button" id="generatePlanBtn" class="admin-button admin-button--primary">
                    <i data-lucide="sparkles" style="width:14px;height:14px;margin-right:6px;" stroke-width="1.5"></i>
                    Generate Plan
                </button>
            </div>
        </div>

        <!-- Create form -->
        <form id="createPlan-form" action="" method="post" enctype="multipart/form-data" class="rp-form" style="display:flex;flex-direction:column;gap:var(--spacing-2xl);">

            <!-- Plan Overview -->
            <div class="admin-sub-container-2">
                <div class="cc-section-header">
                    <h4>Plan Overview</h4>
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

                <div class="rp-form-group">
                    <label for="planImage">Plan Image <span style="font-weight:400;color:var(--color-text-muted);">(optional — JPEG, PNG, WebP)</span></label>
                    <input id="planImage" name="planImage" type="file" accept="image/jpeg,image/png,image/webp,image/gif"
                           style="padding:8px;border:1px solid var(--color-border-primary);border-radius:var(--radius-md);width:100%;cursor:pointer;"
                           onchange="previewPlanImage(this)" />
                    <div id="planImagePreview" style="margin-top:10px;display:none;">
                        <img id="planImagePreviewImg" src="" alt="Preview"
                             style="max-height:160px;border-radius:8px;object-fit:cover;border:1px solid var(--color-border-primary);" />
                    </div>
                </div>

                <input type="hidden" name="planType" value="counselor" />
                <input type="hidden" name="isTemplate" value="1" />

                <div class="rp-form-row">
                    <div class="rp-form-group">
                        <label for="planGoal">Plan Goal</label>
                        <input id="planGoal" name="planGoal" type="text" placeholder="Enter the main goal" />
                    </div>
                    <div class="rp-form-group">
                        <label>Time Period</label>
                        <div class="rp-date-row">
                            <input type="date" id="startDate" name="startDate" required />
                            <input type="date" id="targetCompletionDate" name="targetCompletionDate" required />
                        </div>
                    </div>
                </div>

                <div class="rp-form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Describe the recovery plan…"></textarea>
                </div>
            </div>

            <!-- Phases -->
            <div class="admin-sub-container-2">
                <div class="cc-section-header">
                    <h4>Phases</h4>
                </div>

                <div id="phases-container" class="rp-phases">
                    <?php foreach ([1 => 'Stabilization', 2 => 'Reduction', 3 => 'Maintenance'] as $phaseNum => $phaseName): ?>
                        <div class="phase" data-phase="<?= $phaseNum ?>">
                            <span class="phase-name">Phase <?= $phaseNum ?>: <?= htmlspecialchars($phaseName) ?></span>
                            <div class="phase-actions">
                                <button type="button" class="admin-button admin-button--ghost admin-button--sm"
                                        onclick="addTask(<?= $phaseNum ?>)">+ Add Task</button>
                                <button type="button" class="admin-button admin-button--ghost admin-button--sm"
                                        onclick="addMilestone(<?= $phaseNum ?>)">+ Add Milestone</button>
                            </div>
                        </div>
                        <div class="phase-tasks" id="phase-<?= $phaseNum ?>-tasks"></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recovery Goals -->
            <div class="admin-sub-container-2">
                <div class="cc-section-header">
                    <h4>Recovery Goals</h4>
                </div>

                <div class="rp-goals-grid">
                    <div class="rp-goal-card">
                        <label>Short-term Goal</label>
                        <input type="text" name="shortTermGoalTitle"
                               id="shortTermGoalTitle"
                               placeholder="e.g., Complete first week sober" />
                        <div class="rp-goal-days">
                            <label>Target Days:</label>
                            <input type="number" name="shortTermGoalDays"
                                   id="shortTermGoalDays"
                                   placeholder="30" min="1" max="365" />
                        </div>
                    </div>
                    <div class="rp-goal-card">
                        <label>Long-term Goal</label>
                        <input type="text" name="longTermGoalTitle"
                               id="longTermGoalTitle"
                               placeholder="e.g., Complete full recovery program" />
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
            <div class="admin-sub-container-2">
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
                <button type="submit" class="admin-button admin-button--primary">Save Plan</button>
                <button type="button" class="admin-button admin-button--ghost" onclick="exportPDF()">Export PDF</button>
            </div>

        </form>
    </section>
</main>

<script>
function previewPlanImage(input) {
    const preview = document.getElementById('planImagePreview');
    const img     = document.getElementById('planImagePreviewImg');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            img.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}
</script>

<?php require_once __DIR__ . '/../../common/admin.footer.php'; ?>
