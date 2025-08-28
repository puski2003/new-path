<?php
$pageTitle = 'My Change Requests';
$pageStyle = ['user/recovery', 'user/journal'];
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../../recovery.model.php';

$userId   = (int)$user['id'];
$requests = RecoveryModel::getChangeRequestsForUser($userId);
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
                <h2>My Change Requests</h2>
                <p>Track your counselor's responses.</p>
            </div>
        </div>

        <div class="main-content-body">
            <div class="journal-container">

                <div class="journal-toolbar">
                    <a href="/user/recovery" class="back-btn" title="Back to Recovery">
                        <i data-lucide="chevron-left" style="width:18px;height:18px;"></i>
                    </a>
                </div>

                <?php if (!empty($_GET['sent'])): ?>
                <div class="success-message" style="display:flex;align-items:center;gap:8px;">
                    <i data-lucide="check-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
                    Request sent. Your counselor will review it soon.
                </div>
                <?php endif; ?>

                <?php if (empty($requests)): ?>
                <div class="journal-empty">
                    <i data-lucide="inbox" style="width:48px;height:48px;color:var(--color-text-muted);display:block;margin:0 auto var(--spacing-md);opacity:0.5;"></i>
                    <p>No change requests yet.</p>
                </div>

                <?php else: ?>
                <div class="journal-list">
                    <?php foreach ($requests as $req):
                        $statusClass = match($req['status']) {
                            'approved' => 'success',
                            'rejected' => 'danger',
                            default    => 'warning',
                        };
                    ?>
                    <div class="journal-card">
                        <div class="journal-card-meta">
                            <span class="journal-mood-tag <?= $statusClass ?>">
                                <?= ucfirst(htmlspecialchars($req['status'])) ?>
                            </span>
                            <span class="journal-date"><?= htmlspecialchars($req['createdAt']) ?></span>
                        </div>
                        <h4 class="journal-card-title"><?= htmlspecialchars($req['taskTitle']) ?></h4>
                        <p class="journal-card-preview">
                            <strong>Reason:</strong> <?= htmlspecialchars($req['reason']) ?>
                        </p>
                        <p class="journal-card-preview">
                            <strong>Requested change:</strong> <?= htmlspecialchars($req['requestedChange']) ?>
                        </p>
                        <?php if ($req['counselorNote'] !== ''): ?>
                        <div style="padding:var(--spacing-sm) var(--spacing-md);background:var(--color-bg-light-green);border-radius:var(--radius-lg);border-left:3px solid var(--color-primary);">
                            <p style="font-size:var(--font-size-xs);color:var(--color-text-muted);margin-bottom:2px;font-weight:var(--font-weight-semibold);text-transform:uppercase;letter-spacing:0.05em;">Counselor note</p>
                            <p style="font-size:var(--font-size-sm);color:var(--color-text-primary);"><?= htmlspecialchars($req['counselorNote']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </section>
</main>
<script>lucide.createIcons();</script>
</body>
</html>
