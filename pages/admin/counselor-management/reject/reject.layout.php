<?php
$pageTitle = 'Reject Application';
require_once __DIR__ . '/../../common/admin.html.head.php';
?>
<main class="admin-main-container">
    <?php require_once __DIR__ . '/../../common/admin.sidebar.php'; ?>

    <section class="admin-main-content">
        <div class="admin-sub-container-1" style="justify-content: space-between; align-items: center;">
            <h1>Reject Application</h1>
            <a href="/admin/counselor-management/application-view?id=<?= (int) $applicationId ?>" class="admin-button admin-button--secondary">
                <span class="button-text">Back to Application</span>
            </a>
        </div>

        <div class="admin-sub-container-2">
            <?php if ($error !== ''): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php elseif ($application): ?>
                <div style="background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
                    <h3 style="margin: 0 0 12px; color: #721c24;">Applicant Information</h3>
                    <div style="display: grid; grid-template-columns: auto 1fr; gap: 8px 16px; font-size: 0.95rem;">
                        <strong>Name:</strong>
                        <span><?= htmlspecialchars($application['fullName']) ?></span>
                        <strong>Email:</strong>
                        <span><?= htmlspecialchars($application['email']) ?></span>
                    </div>
                </div>

                <form method="POST" action="/admin/counselor-management/reject?id=<?= (int) $applicationId ?>">
                    <div style="margin-bottom: 16px;">
                        <label for="notes" style="display: block; font-weight: 600; margin-bottom: 8px;">Rejection Reason (Optional - will be included in email)</label>
                        <textarea id="notes" name="notes" rows="3"
                                  style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem;"
                                  placeholder="Enter reason for rejection (optional)"><?= htmlspecialchars($rejectionNotes ?? '') ?></textarea>
                    </div>

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
                        <button type="submit" class="admin-button admin-button--danger">
                            <span class="button-text">Send Rejection Email & Complete</span>
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
