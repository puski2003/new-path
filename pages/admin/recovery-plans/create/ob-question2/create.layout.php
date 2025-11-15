<?php
$pageTitle = 'Create Step 2 Question';
require_once __DIR__ . '/../../../../admin/common/admin.html.head.php';
?>
<main class="admin-main-container">
    <?php require_once __DIR__ . '/../../../../admin/common/admin.sidebar.php'; ?>

    <section class="admin-main-content">
        <h1>Create Step 2 Question</h1>

        <div class="admin-sub-container-2">
            <?php if ($error !== ''): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" class="admin-form" style="max-width: 800px;">
                <div class="form-group">
                    <label class="form-label" for="questionText">Question Text</label>
                    <textarea class="form-textarea" id="questionText" name="questionText" rows="3" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="scaleId">Scale Type</label>
                        <select class="form-select" id="scaleId" name="scaleId">
                            <?php foreach ($scales as $scale): ?>
                                <option value="<?= (int) $scale['id'] ?>"><?= htmlspecialchars($scale['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="path">Path</label>
                        <select class="form-select" id="path" name="path">
                            <option value="BOTH">Both</option>
                            <option value="LITE">Lite</option>
                            <option value="DEEP">Deep</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="weight">Weight</label>
                        <input class="form-input" id="weight" name="weight" type="number" step="0.1" min="0" max="10" value="1.0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="ACTIVE">Active</option>
                            <option value="DISABLED">Disabled</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="/admin/recovery-plans?tab=onboarding" class="admin-button admin-button--secondary">Cancel</a>
                    <button type="submit" class="admin-button admin-button--primary">Create Question</button>
                </div>
            </form>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../../../../admin/common/admin.footer.php'; ?>
</body>
</html>