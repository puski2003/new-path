<?php
/**
 * GET  — list pending task change requests for this counselor
 * POST — resolve (approve / reject) a request
 */
$pageTitle          = 'Task Change Requests';
$pageStyle          = ['counselor/recoveryPlans'];
$pageHeaderTitle    = 'Task Change Requests';
require_once __DIR__ . '/../../common/counselor.head.php';
require_once __DIR__ . '/../../common/counselor.data.php';

$counselorId = (int)$counselor['counselor_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = (int)($_POST['requestId'] ?? 0);
    $decision  = $_POST['decision'] ?? '';
    $note      = trim($_POST['note'] ?? '');

    if ($requestId > 0 && in_array($decision, ['approved', 'rejected'], true)) {
        CounselorData::resolveChangeRequest($requestId, $counselorId, $decision, $note);
    }
    Response::redirect('/counselor/recovery-plans/task-changes?resolved=1');
}

$requests = CounselorData::getChangeRequestsForCounselor($counselorId);
$pageHeaderSubtitle = count($requests) . ' pending request' . (count($requests) !== 1 ? 's' : '');
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/../../common/counselor.html.head.php'; ?>
<body>
<main class="main-container theme-counselor">
    <?php $activePage = 'task-changes'; require_once __DIR__ . '/../../common/counselor.sidebar.php'; ?>

    <section class="main-content">
        <?php require_once __DIR__ . '/../../common/counselor.page-header.php'; ?>

        <div class="main-content-body">

            <?php if (!empty($_GET['resolved'])): ?>
            <div class="success-message" style="margin-bottom:var(--spacing-md);display:flex;align-items:center;gap:8px;">
                <i data-lucide="check-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
                Decision saved and client notified.
            </div>
            <?php endif; ?>

            <?php if (empty($requests)): ?>
            <div class="rp-empty">
                <i data-lucide="inbox" stroke-width="1"></i>
                <p>No pending change requests.</p>
            </div>

            <?php else: ?>
            <div class="rp-plans-container">
                <?php foreach ($requests as $req): ?>
                <div class="rp-plan-row">

                    <div class="rp-plan-thumb">
                        <i data-lucide="file-pen-line" stroke-width="1"
                           style="width:36px;height:36px;color:var(--color-primary);"></i>
                    </div>

                    <div class="rp-plan-info">
                        <span class="rp-plan-label">Change Request</span>
                        <h3 class="rp-plan-title"><?= htmlspecialchars($req['taskTitle']) ?></h3>
                        <p class="rp-plan-client">Client: <?= htmlspecialchars($req['clientName']) ?></p>

                        <div style="margin-top:var(--spacing-sm);">
                            <p style="font-size:var(--font-size-sm);">
                                <strong>Reason:</strong> <?= htmlspecialchars($req['reason']) ?>
                            </p>
                            <p style="font-size:var(--font-size-sm);margin-top:4px;">
                                <strong>New title requested:</strong> <?= htmlspecialchars($req['requestedChange']) ?>
                            </p>
                        </div>

                        <div class="rp-plan-pills" style="margin-top:var(--spacing-sm);">
                            <span class="plan-status status-pending">Pending</span>
                            <span class="rp-pill">
                                <i data-lucide="calendar" stroke-width="1"></i>
                                <?= htmlspecialchars($req['createdAt']) ?>
                            </span>
                        </div>
                    </div>

                    <div class="rp-plan-actions" style="flex-direction:column;align-items:stretch;gap:var(--spacing-sm);">
                        <form method="POST" style="display:flex;flex-direction:column;gap:var(--spacing-sm);">
                            <input type="hidden" name="requestId" value="<?= $req['requestId'] ?>">
                            <textarea name="note"
                                      style="width:100%;min-width:200px;padding:8px 10px;border:1px solid var(--color-border);border-radius:8px;font-size:var(--font-size-sm);font-family:inherit;resize:vertical;background:var(--color-bg-primary);color:var(--color-text-primary);"
                                      rows="2" maxlength="500"
                                      placeholder="Optional note to client…"></textarea>
                            <div style="display:flex;gap:var(--spacing-sm);">
                                <button type="submit" name="decision" value="approved"
                                        class="btn btn-primary" style="flex:1;justify-content:center;font-size:var(--font-size-xs);">
                                    <i data-lucide="check" style="width:14px;height:14px;margin-right:4px;" stroke-width="2"></i>
                                    Approve
                                </button>
                                <button type="submit" name="decision" value="rejected"
                                        class="btn btn-secondary" style="flex:1;justify-content:center;font-size:var(--font-size-xs);color:#f43a3a;">
                                    <i data-lucide="x" style="width:14px;height:14px;margin-right:4px;" stroke-width="2"></i>
                                    Reject
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>
    </section>
</main>
<script>lucide.createIcons();</script>
</body>
</html>
