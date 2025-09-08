<?php
$pageTitle = 'Approve Application';
require_once __DIR__ . '/../../common/admin.html.head.php';
?>
<main class="admin-main-container">
    <?php require_once __DIR__ . '/../../common/admin.sidebar.php'; ?>

    <section class="admin-main-content">
        <div class="admin-sub-container-1" style="justify-content: space-between; align-items: center;">
            <h1>Approve Application</h1>
            <a href="/admin/counselor-management/application-view?id=<?= (int) $applicationId ?>" class="admin-button admin-button--secondary">
                <span class="button-text">Back to Application</span>
            </a>
        </div>

        <div class="admin-sub-container-2">
            <?php if ($error !== ''): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php elseif ($application): ?>
                <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
                    <h3 style="margin: 0 0 12px; color: #155724;">Applicant Credentials (Read-Only)</h3>
                    <div style="display: grid; grid-template-columns: auto 1fr; gap: 8px 16px; font-size: 0.95rem;">
                        <strong>Email:</strong>
                        <span><?= htmlspecialchars($application['email']) ?></span>
                        <strong>Username:</strong>
                        <span><?= htmlspecialchars($username ?? '') ?></span>
                        <strong>Password:</strong>
                        <span style="font-family: monospace; background: #fff; padding: 4px 8px; border-radius: 4px;">
                            <?= htmlspecialchars($password ?? '') ?>
                        </span>
                    </div>
                </div>

                <form method="POST" action="/admin/counselor-management/approve?id=<?= (int) $applicationId ?>">
                    <div style="margin-bottom: 16px;">
                        <label for="subject" style="display: block; font-weight: 600; margin-bottom: 8px;">Email Subject</label>
                        <input type="text" id="subject" name="subject" 
                               value="<?= htmlspecialchars($defaultSubject ?? '') ?>" 
                               required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem;">
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label for="body" style="display: block; font-weight: 600; margin-bottom: 8px;">Email Body (HTML)</label>
                        <textarea id="body" name="body" rows="20" required
                                  style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.9rem; font-family: monospace;"><?= htmlspecialchars($defaultBody ?? '') ?></textarea>
                    </div>

                    <div class="admin-sub-container-1" style="gap: var(--spacing-sm);">
                        <button type="submit" class="admin-button admin-button--success">
                            <span class="button-text">Send Approval Email & Complete</span>
                        </button>
                        <a href="/admin/counselor-management/application-view?id=<?= (int) $applicationId ?>" class="admin-button admin-button--secondary">
                            <span class="button-text">Cancel</span>
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../../common/admin.footer.php'; ?>
