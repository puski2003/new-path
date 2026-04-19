<?php
$taskTitle = $data['taskTitle'];
$taskId = $data['taskId'];
$error = $data['error'];
$postedReason = $data['postedReason'] ?? '';
$postedChange = $data['postedChange'] ?? '';
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
                                  required><?= htmlspecialchars($postedReason) ?></textarea>
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
                                  required><?= htmlspecialchars($postedChange) ?></textarea>
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
    const reasonTA = document.querySelector('textarea[name="reason"]');
    const changeTA = document.querySelector('textarea[name="requested_change"]');
    const reasonCount = document.getElementById('reasonCount');
    const changeCount = document.getElementById('changeCount');
    function updateCount(ta, el) { el.textContent = ta.value.length; }
    reasonTA.addEventListener('input', () => updateCount(reasonTA, reasonCount));
    changeTA.addEventListener('input', () => updateCount(changeTA, changeCount));
    updateCount(reasonTA, reasonCount);
    updateCount(changeTA, changeCount);
</script>
</body>
</html>