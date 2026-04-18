<?php

/**
 * Route: /user/sessions/extend/success
 *
 * Shown after a successful extension payment.
 * Displays the new end time and the (same) meeting link.
 */

require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../extend.model.php';

$extensionId = (int)(Request::get('extension_id') ?? 0);
$userId      = (int)$user['id'];

if ($extensionId <= 0) {
    Response::redirect('/user/sessions');
    exit;
}

$ext = ExtendModel::getFullById($extensionId);

// Verify ownership and that payment succeeded
if (!$ext || $ext['userId'] !== $userId || $ext['status'] !== 'paid') {
    Response::redirect('/user/sessions');
    exit;
}

$totalMinutes    = $ext['originalDuration'] + $ext['extendedMinutes'];
$newEndTs        = strtotime((string)$ext['sessionDatetime']) + ($totalMinutes * 60);
$newEndLabel     = date('g:i A', $newEndTs);
$sessionDateLabel = date('F j, Y \a\t g:i A', strtotime((string)$ext['sessionDatetime']));

$pageTitle = 'Session Extended';
$pageStyle = ['user/sessions'];
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/../../../common/user.html.head.php'; ?>

<body>
    <main class="main-container">
        <?php $activePage = 'sessions';
        require_once __DIR__ . '/../../../common/user.sidebar.php'; ?>

        <section class="main-content">
            <img src="/assets/img/main-content-head.svg" alt="" class="main-header-bg-image" />

            <div class="main-content-header">
                <div class="main-content-header-text">
                    <h2>Session Extended!</h2>
                    <p>Your extension payment was successful.</p>
                </div>
                <div style="width:25%"></div>
                <img src="/assets/img/session-header.svg" alt="Session" class="session-image" />
            </div>

            <div class="main-content-body">

                <div style="max-width:560px;margin:0 auto;">

                    <!-- Success banner -->
                    <div class="success-message" style="margin-bottom:var(--spacing-xl);">
                        <strong>Payment confirmed!</strong>&nbsp;Your session has been extended by <?= (int)$ext['durationMinutes'] ?> minutes.
                    </div>

                    <!-- Extension details card -->
                    <div class="summary-card" style="padding:var(--spacing-xl);margin-bottom:var(--spacing-lg);">
                        <h3 style="margin-bottom:var(--spacing-lg);">Extension Details</h3>

                        <div style="display:flex;flex-direction:column;gap:var(--spacing-md);">
                            <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:var(--spacing-sm);border-bottom:1px solid var(--color-border);">
                                <span style="color:var(--color-text-secondary);">Counselor</span>
                                <span style="font-weight:600;"><?= htmlspecialchars($ext['counselorName']) ?></span>
                            </div>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:var(--spacing-sm);border-bottom:1px solid var(--color-border);">
                                <span style="color:var(--color-text-secondary);">Session Date</span>
                                <span style="font-weight:600;"><?= htmlspecialchars($sessionDateLabel) ?></span>
                            </div>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:var(--spacing-sm);border-bottom:1px solid var(--color-border);">
                                <span style="color:var(--color-text-secondary);">Extended By</span>
                                <span style="font-weight:600;"><?= (int)$ext['durationMinutes'] ?> minutes</span>
                            </div>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:var(--spacing-sm);border-bottom:1px solid var(--color-border);">
                                <span style="color:var(--color-text-secondary);">New End Time</span>
                                <span style="font-weight:600;color:var(--color-primary);"><?= htmlspecialchars($newEndLabel) ?></span>
                            </div>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:var(--spacing-sm);border-bottom:1px solid var(--color-border);">
                                <span style="color:var(--color-text-secondary);">Amount Paid</span>
                                <span style="font-weight:600;">LKR <?= number_format($ext['fee'], 2) ?></span>
                            </div>
                        </div>

                        <?php if (!empty($ext['meetingLink'])): ?>
                        <div style="margin-top:var(--spacing-lg);padding:var(--spacing-md);background:var(--color-surface-alt, #f9f9f9);border-radius:var(--radius-md);">
                            <p style="font-size:var(--font-size-sm);color:var(--color-text-secondary);margin-bottom:var(--spacing-sm);">
                                The meeting link remains the same — just stay in the call.
                            </p>
                            <a href="<?= htmlspecialchars($ext['meetingLink']) ?>"
                               target="_blank" rel="noopener"
                               class="btn btn-primary"
                               style="display:inline-flex;align-items:center;gap:8px;">
                                <i data-lucide="video" stroke-width="2" width="16" height="16"></i>
                                Rejoin Meeting
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div style="display:flex;gap:var(--spacing-md);">
                        <a href="/user/sessions?id=<?= (int)$ext['sessionId'] ?>" class="btn btn-secondary" style="flex:1;text-align:center;">
                            View Session
                        </a>
                        <a href="/user/sessions" class="btn btn-primary" style="flex:1;text-align:center;">
                            My Sessions
                        </a>
                    </div>

                </div>

            </div><!-- /.main-content-body -->
        </section>
    </main>

    <script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
    <?php require_once __DIR__ . '/../../../common/user.footer.php'; ?>
</body>
</html>
