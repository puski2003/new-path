<div class="col-2-row-1 dashboard-card">
    <div class="card-header">
        <h3>Daily Tasks</h3>
        <div style="display:flex;align-items:center;gap:var(--spacing-sm);">
            <span class="tasks-progress"><?= $completedCount ?>/<?= ($completedCount + $pendingCount) ?> completed</span>
            <a href="/user/recovery/goals" class="view-all-link">
                <i data-lucide="target" stroke-width="2"></i>
                Goals
            </a>
        </div>
    </div>

    <div class="tasks-list">
        <?php if (empty($tasks)): ?>
            <div class="empty-tasks">
                <p>No tasks yet. Browse recovery plans to get started!</p>
            </div>
        <?php else: ?>
            <?php foreach ($tasks as $task): ?>
                <div class="task-card">
                    <div class="task-info">
                        <?php if (($task['status'] ?? '') === 'completed'): ?>
                            <div class="task-icon completed"><i data-lucide="check-circle" stroke-width="2"></i></div>
                        <?php elseif (($task['priority'] ?? '') === 'high'): ?>
                            <div class="task-icon urgent"><i data-lucide="alert-circle" stroke-width="2"></i></div>
                        <?php else: ?>
                            <div class="task-icon pending"><i data-lucide="circle" stroke-width="2"></i></div>
                        <?php endif; ?>

                        <div class="task-details">
                            <span class="task-name"><?= htmlspecialchars($task['title']) ?></span>
                            <span class="task-status-text">
                                <?php if (($task['status'] ?? '') === 'completed'): ?>
                                    Completed
                                <?php elseif (!empty($task['dueDate'])): ?>
                                    Due: <?= htmlspecialchars($task['dueDate']) ?>
                                <?php else: ?>
                                    <?= htmlspecialchars($task['taskType'] ?? 'Task') ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                    <?php if (($task['status'] ?? '') !== 'completed'): ?>
                        <form action="/user/recovery/task/complete" method="post" class="task-complete-form">
                            <input type="hidden" name="taskId" value="<?= (int)$task['taskId'] ?>" />
                            <button type="submit" class="btn btn-primary btn-sm">Complete</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="tasks-pagination" id="tasksPagination" style="display:none;">
        <button type="button" class="tasks-page-btn" id="tasksPrev" aria-label="Previous page">&#8592;</button>
        <span class="tasks-page-info" id="tasksPageInfo"></span>
        <button type="button" class="tasks-page-btn" id="tasksNext" aria-label="Next page">&#8594;</button>
    </div>
</div>
