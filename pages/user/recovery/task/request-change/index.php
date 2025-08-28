<?php
/**
 * GET  ?taskId=X — show the change-request form
 * POST            — submit request, redirect to change-requests list
 */
$pageTitle = 'Request Task Change';
$pageStyle = ['user/recovery', 'user/checkin'];
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../../recovery.model.php';

$userId = (int)$user['id'];
$taskId = (int)($_GET['taskId'] ?? $_POST['taskId'] ?? 0);

if ($taskId <= 0) Response::redirect('/user/recovery');

// Fetch the task title so we can show it as context
$taskRs = Database::search(
    "SELECT rt.title, rp.counselor_id
     FROM recovery_tasks rt
     INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
     WHERE rt.task_id = $taskId AND rp.user_id = $userId
     LIMIT 1"
);
$taskRow   = $taskRs ? $taskRs->fetch_assoc() : null;
$taskTitle = $taskRow['title'] ?? 'Task';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = trim($_POST['reason'] ?? '');
    $change = trim($_POST['requested_change'] ?? '');

    if (strlen($reason) < 5) {
        $error = 'Please explain why you want to change this task.';
    } elseif (strlen($change) < 3) {
        $error = 'Please provide the new task title.';
    } else {
        $ok = RecoveryModel::createChangeRequest($taskId, $userId, $reason, $change);
        if ($ok) {
            Response::redirect('/user/recovery/task/change-requests?sent=1');
        } else {
            $error = 'This task cannot be changed — it may not be part of a counselor-assigned plan.';
        }
    }
}
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
                <h2>Request Task Change</h2>
                <p>Ask your counselor to modify this task.</p>
            </div>
        </div>

        <div class="main-content-body">
            <div class="checkin-container">

                <!-- Navigation row -->
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--spacing-lg);">
                    <div class="back-navigation">
                        <a href="/user/recovery" class="back-btn" title="Back to Recovery">
                            <i data-lucide="chevron-left" style="width:18px;height:18px;"></i>
                        </a>
                    </div>
                    <a href="/user/recovery/task/change-requests"
                       style="font-size:var(--font-size-sm);color:var(--color-primary);display:flex;align-items:center;gap:4px;text-decoration:none;">
                        <i data-lucide="inbox" style="width:14px;height:14px;"></i>
                        My Change Requests
                    </a>
                </div>

                <!-- Task context card -->
                <div style="background:var(--color-bg-secondary);border:1px solid var(--color-border);border-radius:12px;padding:var(--spacing-md) var(--spacing-lg);margin-bottom:var(--spacing-lg);display:flex;align-items:center;gap:var(--spacing-sm);">
                    <i data-lucide="list-checks" stroke-width="1.5" style="width:20px;height:20px;color:var(--color-primary);flex-shrink:0;"></i>
                    <div>
                        <p style="font-size:var(--font-size-xs);color:var(--color-text-muted);margin-bottom:2px;">Requesting change for</p>
                        <p style="font-weight:var(--font-weight-semibold);color:var(--color-text-primary);">
                            <?= htmlspecialchars($taskTitle) ?>
                        </p>
                    </div>
                </div>

                <?php if ($error): ?>
                <div class="error-message" style="margin-bottom:var(--spacing-md);">
                    <i data-lucide="alert-circle" style="width:15px;height:15px;flex-shrink:0;"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <form method="POST" class="checkin-form">
                    <input type="hidden" name="taskId" value="<?= $taskId ?>">

                    <div class="checkin-section">
                        <h4 class="checkin-section-title">
                            <i data-lucide="message-square" style="width:16px;height:16px;"></i>
                            Why do you want to change this task?
                        </h4>
                        <textarea name="reason" class="checkin-notes" rows="4" maxlength="500"
                                  placeholder="Explain your reason — e.g. it's not relevant to my current situation…"
                                  required><?= htmlspecialchars($_POST['reason'] ?? '') ?></textarea>
                        <p style="font-size:var(--font-size-xs);color:var(--color-text-muted);margin-top:4px;text-align:right;">
                            <span id="reasonCount">0</span>/500
                        </p>
                    </div>

                    <div class="checkin-section">
                        <h4 class="checkin-section-title">
                            <i data-lucide="pencil" style="width:16px;height:16px;"></i>
                            What should the new task title be?
                        </h4>
                        <textarea name="requested_change" class="checkin-notes" rows="3" maxlength="200"
                                  placeholder="Enter the new task title you'd like…"
                                  required><?= htmlspecialchars($_POST['requested_change'] ?? '') ?></textarea>
                        <p style="font-size:var(--font-size-xs);color:var(--color-text-muted);margin-top:4px;text-align:right;">
                            <span id="changeCount">0</span>/200
                        </p>
                    </div>

                    <button type="submit" class="btn btn-primary"
                            style="width:100%;justify-content:center;padding:var(--spacing-md);margin-top:var(--spacing-sm);">
                        <i data-lucide="send" style="width:16px;height:16px;margin-right:6px;"></i>
                        Send Request
                    </button>
                </form>

            </div>
        </div>
    </section>
</main>

<script>
    lucide.createIcons();

    // Character counters
    const reasonTA = document.querySelector('textarea[name="reason"]');
    const changeTA = document.querySelector('textarea[name="requested_change"]');
    const reasonCount = document.getElementById('reasonCount');
    const changeCount = document.getElementById('changeCount');

    function updateCount(ta, el) {
        el.textContent = ta.value.length;
    }

    reasonTA.addEventListener('input', () => updateCount(reasonTA, reasonCount));
    changeTA.addEventListener('input', () => updateCount(changeTA, changeCount));

    // Init counts on page load (if form was re-submitted with values)
    updateCount(reasonTA, reasonCount);
    updateCount(changeTA, changeCount);
</script>
</body>
</html>
