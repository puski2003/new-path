<?php
$taskId = (int)($task['taskId'] ?? 0);
$taskStatus = $task['status'] ?? 'pending';
$taskName = $task['title'] ?? 'Task';
$taskStatusText = $taskStatus === 'completed' ? 'Completed' : 'Pending';
$priority = $task['priority'] ?? null;
$buttonClass = $taskStatus === 'completed' ? 'btn-link' : 'btn-primary';
$buttonText = $taskStatus === 'completed' ? 'View' : 'Complete';
?>

<div class="task-item <?= htmlspecialchars($taskStatus) ?>" data-task-id="<?= $taskId ?>">
    <div class="task-checkbox-container">
        <input type="checkbox" class="task-checkbox" id="task-<?= $taskId ?>" <?= $taskStatus === 'completed' ? 'checked disabled' : '' ?> />
        <label for="task-<?= $taskId ?>" class="checkbox-label"></label>
    </div>
    <div class="task-content">
        <div class="task-details">
            <span class="task-name"><?= htmlspecialchars($taskName) ?></span>
            <span class="task-status"><?= htmlspecialchars($taskStatusText) ?></span>
            <?php if (!empty($priority)): ?>
                <span class="task-priority"><?= htmlspecialchars($priority) ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="task-actions">
        <button class="btn <?= htmlspecialchars($buttonClass) ?> task-action-btn"><?= htmlspecialchars($buttonText) ?></button>
    </div>
</div>
