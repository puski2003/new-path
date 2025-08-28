<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/../recovery.model.php';

$userId = (int)$user['id'];
$planId = (int)(Request::get('planId') ?? 0);
if ($planId <= 0) {
    Response::redirect('/user/recovery');
}

$plan = RecoveryModel::getPlanByIdForUser($planId, $userId);
if ($plan === null) {
    Response::status(404);
    require ROOT . '/pages/404.php';
    exit;
}

$goals = RecoveryModel::getGoalsByPlanId($planId);
$tasks = RecoveryModel::getTasksByPlanId($planId, $userId);

// Group tasks by phase
$tasksByPhase = [];
foreach ($tasks as $task) {
    $tasksByPhase[$task['phase']][] = $task;
}
ksort($tasksByPhase);

// Find the current unlocked phase (lowest phase with incomplete tasks)
$currentPhase = null;
foreach ($tasksByPhase as $phase => $phaseTasks) {
    foreach ($phaseTasks as $t) {
        if ($t['status'] !== 'completed') {
            $currentPhase = $phase;
            break 2;
        }
}
}

$isSelfManaged = empty($plan['counselorId']);

$pageTitle = 'View Recovery Plan';
$pageStyle = ['user/dashboard', 'user/manage-plans', 'user/recovery'];
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/../../common/user.html.head.php'; ?>
<body>
<main class="main-container">
    <?php $activePage = 'recovery'; require_once __DIR__ . '/../../common/user.sidebar.php'; ?>

    <section class="main-content">
        <img src="/assets/img/main-content-head.svg" alt="" class="main-header-bg-image" />

        <!-- Header -->
        <div class="main-content-header">
            <div class="main-content-header-text">
                <a href="/user/recovery/manage" class="back-btn" aria-label="Back">
                    <i data-lucide="arrow-left" stroke-width="2" class="back-icon"></i>
                </a>
                <div>
                    <h2><?= htmlspecialchars($plan['title']) ?></h2>
                    <p style="font-size:var(--font-size-sm);color:var(--color-text-secondary);margin-top:2px;">
                        Plan details, goals and tasks.
                    </p>
                </div>
            </div>
            <?php if (($plan['assignedStatus'] ?? '') === 'pending'): ?>
            <p class="page-subtitle">
                <span style="background:#fef3c7;color:#b45309;padding:4px 12px;border-radius:20px;font-size:var(--font-size-xs);font-weight:600;">Pending Acceptance</span>
            </p>
            <?php endif; ?>
        </div>

        <div class="main-content-body">
            <div class="plans-container" style="max-width:900px;">

                <?php if (($plan['assignedStatus'] ?? '') === 'pending'): ?>
                <div style="display:flex;gap:12px;margin-bottom:var(--spacing-lg);">
                    <form method="post" action="/user/recovery/accept">
                        <input type="hidden" name="planId" value="<?= (int)$plan['planId'] ?>" />
                        <button class="btn btn-primary" type="submit">Accept Plan</button>
                    </form>
                    <form method="post" action="/user/recovery/reject">
                        <input type="hidden" name="planId" value="<?= (int)$plan['planId'] ?>" />
                        <button class="btn btn-secondary" type="submit">Reject Plan</button>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Plan Overview Card -->
                <div class="plan-card active" style="margin-bottom:0;">
                    <div class="plan-card-header">
                        <h4 class="plan-title"><?= htmlspecialchars($plan['title']) ?></h4>
                        <span class="plan-status status-<?= htmlspecialchars($plan['status']) ?>"><?= ucfirst(htmlspecialchars($plan['status'])) ?></span>
                    </div>
                    <?php if (!empty($plan['description'])): ?>
                    <p class="plan-description"><?= htmlspecialchars($plan['description']) ?></p>
                    <?php endif; ?>
                    <div class="plan-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width:<?= (int)$plan['progressPercentage'] ?>%"></div>
                        </div>
                        <span class="progress-text"><?= (int)$plan['progressPercentage'] ?>%</span>
                    </div>
                </div>

                <!-- Two-column layout: Goals + Tasks -->
                <div style="display:grid;grid-template-columns:1fr 2fr;gap:var(--spacing-xl);margin-top:var(--spacing-xl);">

                    <!-- Goals -->
                    <div>
                        <div class="plans-section">
                            <h3 class="section-title" style="font-size:var(--font-size-base);margin-bottom:var(--spacing-sm);">
                                <span class="section-icon active">
                                    <i data-lucide="target" stroke-width="2"></i>
                                </span>
                                Goals
                            </h3>

                            <?php if (empty($goals)): ?>
                            <div class="plan-card" style="text-align:center;padding:var(--spacing-xl);">
                                <p class="plan-description" style="margin:0;">No goals set for this plan.</p>
                            </div>
                            <?php else: ?>
                            <div class="plans-list">
                                <?php foreach ($goals as $goal): ?>
                                <div class="goal-item">
                                    <div class="goal-header">
                                        <span class="goal-title"><?= htmlspecialchars($goal['title']) ?></span>
                                        <span class="goal-days"><?= (int)$goal['currentProgress'] ?>/<?= (int)$goal['targetDays'] ?>d</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width:<?= (int)$goal['progressPercentage'] ?>%"></div>
                                    </div>
                                    <span style="font-size:var(--font-size-xs);color:var(--color-text-muted);text-transform:capitalize;">
                                        <?= str_replace('_', ' ', htmlspecialchars($goal['goalType'])) ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Tasks grouped by phase -->
                    <div>
                        <div class="plans-section">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--spacing-sm);">
                                <h3 class="section-title" style="font-size:var(--font-size-base);margin:0;">
                                    <span class="section-icon active">
                                        <i data-lucide="list-checks" stroke-width="2"></i>
                                    </span>
                                    Tasks
                                </h3>
                                <?php if ($isSelfManaged): ?>
                                <button type="button" onclick="document.getElementById('add-task-form').style.display=document.getElementById('add-task-form').style.display==='none'?'flex':'none'"
                                        class="btn btn-secondary" style="padding:6px 14px;font-size:var(--font-size-xs);">
                                    + Add Task
                                </button>
                                <?php endif; ?>
                            </div>

                            <?php if ($isSelfManaged): ?>
                            <form id="add-task-form" method="post" action="/user/recovery/task/add"
                                  style="display:none;flex-direction:column;gap:var(--spacing-sm);background:var(--color-bg-light-green);border-radius:var(--radius-md);padding:var(--spacing-md);margin-bottom:var(--spacing-md);">
                                <input type="hidden" name="planId" value="<?= (int)$plan['planId'] ?>" />
                                <input type="text" name="title" placeholder="Task title" required
                                       style="padding:8px 12px;border:1px solid var(--color-border-primary);border-radius:var(--radius-md);font-size:var(--font-size-sm);" />
                                <div style="display:flex;gap:var(--spacing-sm);">
                                    <select name="phase" style="flex:1;padding:8px 12px;border:1px solid var(--color-border-primary);border-radius:var(--radius-md);font-size:var(--font-size-sm);">
                                        <?php foreach (array_keys($tasksByPhase) ?: [1] as $p): ?>
                                        <option value="<?= (int)$p ?>">Phase <?= (int)$p ?></option>
                                        <?php endforeach; ?>
                                        <option value="<?= count($tasksByPhase) + 1 ?>">New Phase</option>
                                    </select>
                                    <select name="taskType" style="flex:1;padding:8px 12px;border:1px solid var(--color-border-primary);border-radius:var(--radius-md);font-size:var(--font-size-sm);">
                                        <option value="custom">Custom</option>
                                        <option value="journal">Journal</option>
                                        <option value="session">Session</option>
                                        <option value="exercise">Exercise</option>
                                        <option value="meditation">Meditation</option>
                                    </select>
                                    <select name="priority" style="flex:1;padding:8px 12px;border:1px solid var(--color-border-primary);border-radius:var(--radius-md);font-size:var(--font-size-sm);">
                                        <option value="low">Low</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                                <div style="display:flex;gap:var(--spacing-sm);">
                                    <button type="submit" class="btn btn-primary" style="padding:6px 14px;font-size:var(--font-size-xs);">Add</button>
                                    <button type="button" onclick="document.getElementById('add-task-form').style.display='none'"
                                            class="btn btn-secondary" style="padding:6px 14px;font-size:var(--font-size-xs);">Cancel</button>
                                </div>
                            </form>
                            <?php endif; ?>

                            <?php if (empty($tasks)): ?>
                            <div class="plan-card" style="text-align:center;padding:var(--spacing-xl);">
                                <p class="plan-description" style="margin:0;">No tasks in this plan.</p>
                            </div>
                            <?php else: ?>
                            <div style="display:flex;flex-direction:column;gap:var(--spacing-lg);">
                                <?php foreach ($tasksByPhase as $phase => $phaseTasks):
                                    $phaseComplete = array_reduce($phaseTasks, fn($carry, $t) => $carry && $t['status'] === 'completed', true);
                                    $isCurrentPhase = ($phase === $currentPhase);
                                    $isLocked = ($currentPhase !== null && $phase > $currentPhase);
                                ?>
                                <div>
                                    <!-- Phase header -->
                                    <div style="display:flex;align-items:center;gap:var(--spacing-sm);margin-bottom:var(--spacing-sm);">
                                        <span style="
                                            font-size:var(--font-size-xs);
                                            font-weight:var(--font-weight-semibold);
                                            padding:3px 10px;
                                            border-radius:var(--radius-pill);
                                            background:<?= $phaseComplete ? 'var(--color-primary)' : ($isCurrentPhase ? 'var(--color-bg-light-green)' : '#f3f4f6') ?>;
                                            color:<?= $phaseComplete ? '#fff' : ($isCurrentPhase ? 'var(--color-primary-dark)' : '#6b7280') ?>;
                                            ">
                                            Phase <?= $phase ?>
                                        </span>
                                        <?php if ($phaseComplete): ?>
                                            <span style="font-size:var(--font-size-xs);color:var(--color-primary);display:flex;align-items:center;gap:4px;">
                                                <i data-lucide="check-circle-2" style="width:13px;height:13px;"></i> Complete
                                            </span>
                                        <?php elseif ($isCurrentPhase): ?>
                                            <span style="font-size:var(--font-size-xs);color:var(--color-text-muted);">Current phase</span>
                                        <?php elseif ($isLocked): ?>
                                            <span style="font-size:var(--font-size-xs);color:#9ca3af;display:flex;align-items:center;gap:4px;">
                                                <i data-lucide="lock" style="width:12px;height:12px;"></i> Locked
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Task cards -->
                                    <div class="tasks-list" style="min-height:0;">
                                        <?php foreach ($phaseTasks as $task):
                                            $done = $task['status'] === 'completed';
                                            $priorityColor = match($task['priority']) {
                                                'high'   => '#ef4444',
                                                'medium' => '#f59e0b',
                                                default  => '#6b7280',
                                            };
                                        ?>
                                        <div class="task-card" style="<?= ($isLocked && !$done) ? 'opacity:0.55;' : '' ?>">
                                            <div class="task-info">
                                                <div class="task-icon <?= $done ? 'completed' : 'pending' ?>">
                                                    <i data-lucide="<?= $done ? 'check-circle' : 'circle' ?>" stroke-width="2"></i>
                                                </div>
                                                <div class="task-details">
                                                    <span class="task-name" style="<?= $done ? 'text-decoration:line-through;color:var(--color-text-muted);' : '' ?>">
                                                        <?= htmlspecialchars($task['title']) ?>
                                                    </span>
                                                    <span class="task-status-text">
                                                        <?= htmlspecialchars(str_replace('_', ' ', $task['taskType'])) ?>
                                                        <?= $task['dueDate'] ? ' · Due ' . htmlspecialchars($task['dueDate']) : '' ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div style="display:flex;align-items:center;gap:var(--spacing-sm);flex-shrink:0;">
                                                <span style="
                                                    font-size:var(--font-size-xs);
                                                    font-weight:600;
                                                    padding:2px 8px;
                                                    border-radius:var(--radius-pill);
                                                    background:<?= $priorityColor ?>18;
                                                    color:<?= $priorityColor ?>;
                                                    text-transform:capitalize;">
                                                    <?= htmlspecialchars($task['priority']) ?>
                                                </span>
                                                <?php if (!$done && !$isLocked): ?>
                                                <form method="post" action="/user/recovery/task/complete">
                                                    <input type="hidden" name="taskId" value="<?= (int)$task['taskId'] ?>" />
                                                    <button class="btn btn-primary" style="padding:6px 14px;font-size:var(--font-size-xs);" type="submit">Complete</button>
                                                </form>
                                                <?php if ($isSelfManaged): ?>
                                                <a href="/user/recovery/task/edit?taskId=<?= (int)$task['taskId'] ?>"
                                                   class="btn btn-secondary"
                                                   style="padding:6px 14px;font-size:var(--font-size-xs);">
                                                    <i data-lucide="pencil" style="width:13px;height:13px;margin-right:4px;" stroke-width="1.5"></i>
                                                    Edit
                                                </a>
                                                <form method="post" action="/user/recovery/task/delete"
                                                      onsubmit="return confirm('Delete this task?')">
                                                    <input type="hidden" name="taskId" value="<?= (int)$task['taskId'] ?>" />
                                                    <input type="hidden" name="planId"  value="<?= (int)$plan['planId'] ?>" />
                                                    <button type="submit" class="btn btn-secondary"
                                                            style="padding:6px 14px;font-size:var(--font-size-xs);color:#ef4444;border-color:#ef4444;">
                                                        <i data-lucide="trash-2" style="width:13px;height:13px;" stroke-width="1.5"></i>
                                                    </button>
                                                </form>
                                                <?php elseif (!empty($plan['counselorId'])): ?>
                                                <a href="/user/recovery/task/request-change?taskId=<?= (int)$task['taskId'] ?>"
                                                   class="btn btn-secondary"
                                                   style="padding:6px 14px;font-size:var(--font-size-xs);">
                                                    <i data-lucide="file-pen-line" style="width:13px;height:13px;margin-right:4px;" stroke-width="1.5"></i>
                                                    Request Change
                                                </a>
                                                <?php endif; ?>
                                                <?php elseif ($isLocked && !$done): ?>
                                                <span style="font-size:var(--font-size-xs);color:#9ca3af;">
                                                    <i data-lucide="lock" style="width:13px;height:13px;vertical-align:middle;"></i>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div><!-- /grid -->
            </div>
        </div>
    </section>
</main>

<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
</body>
</html>
