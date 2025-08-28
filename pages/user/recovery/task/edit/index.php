<?php
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../../recovery.model.php';

$userId = (int)$user['id'];
$taskId = (int)(Request::get('taskId') ?? Request::post('taskId') ?? 0);

if (Request::isPost()) {
    $planId = (int)(Request::post('planId') ?? 0);
    RecoveryModel::updateUserTask($taskId, $userId, [
        'title'    => Request::post('title') ?? '',
        'taskType' => Request::post('taskType') ?? 'custom',
        'priority' => Request::post('priority') ?? 'medium',
    ]);
    Response::redirect('/user/recovery/view?planId=' . $planId);
}

// Load task for the form
$rs = Database::search(
    "SELECT rt.task_id, rt.title, rt.task_type, rt.priority, rt.phase, rp.plan_id
     FROM recovery_tasks rt
     INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
     WHERE rt.task_id = $taskId AND rp.user_id = $userId AND rp.counselor_id IS NULL
     LIMIT 1"
);
if (!$rs || $rs->num_rows === 0) {
    Response::redirect('/user/recovery');
}
$task = $rs->fetch_assoc();

$pageTitle = 'Edit Task';
$pageStyle = ['user/dashboard'];
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/../../../common/user.html.head.php'; ?>
<body>
<main class="main-container">
    <?php $activePage = 'recovery'; require_once __DIR__ . '/../../../common/user.sidebar.php'; ?>
    <section class="main-content">
        <img src="/assets/img/main-content-head.svg" alt="" class="main-header-bg-image" />
        <div class="main-content-header">
            <div class="main-content-header-text">
                <a href="/user/recovery/view?planId=<?= (int)$task['plan_id'] ?>" class="back-btn">
                    <i data-lucide="arrow-left" stroke-width="2" class="back-icon"></i>
                </a>
                <h2>Edit Task</h2>
            </div>
        </div>
        <div class="main-content-body">
            <div style="max-width:520px;padding:var(--spacing-xl);">
                <form method="post" action="/user/recovery/task/edit"
                      style="display:flex;flex-direction:column;gap:var(--spacing-lg);">
                    <input type="hidden" name="taskId" value="<?= (int)$task['task_id'] ?>" />
                    <input type="hidden" name="planId"  value="<?= (int)$task['plan_id'] ?>" />

                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-size:var(--font-size-sm);font-weight:var(--font-weight-medium);">Task Title</label>
                        <input type="text" name="title"
                               value="<?= htmlspecialchars($task['title']) ?>"
                               required
                               style="padding:10px 14px;border:1px solid var(--color-border-primary);border-radius:var(--radius-md);font-size:var(--font-size-sm);" />
                    </div>

                    <div style="display:flex;gap:var(--spacing-md);">
                        <div style="flex:1;display:flex;flex-direction:column;gap:6px;">
                            <label style="font-size:var(--font-size-sm);font-weight:var(--font-weight-medium);">Type</label>
                            <select name="taskType" style="padding:10px 14px;border:1px solid var(--color-border-primary);border-radius:var(--radius-md);font-size:var(--font-size-sm);">
                                <?php foreach (['custom'=>'Custom','journal'=>'Journal','session'=>'Session','exercise'=>'Exercise','meditation'=>'Meditation'] as $v => $l): ?>
                                <option value="<?= $v ?>" <?= $task['task_type'] === $v ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="flex:1;display:flex;flex-direction:column;gap:6px;">
                            <label style="font-size:var(--font-size-sm);font-weight:var(--font-weight-medium);">Priority</label>
                            <select name="priority" style="padding:10px 14px;border:1px solid var(--color-border-primary);border-radius:var(--radius-md);font-size:var(--font-size-sm);">
                                <?php foreach (['low'=>'Low','medium'=>'Medium','high'=>'High'] as $v => $l): ?>
                                <option value="<?= $v ?>" <?= $task['priority'] === $v ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="display:flex;gap:var(--spacing-md);">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="/user/recovery/view?planId=<?= (int)$task['plan_id'] ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>
<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
</body>
</html>
