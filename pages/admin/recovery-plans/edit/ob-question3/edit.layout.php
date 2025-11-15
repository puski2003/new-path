<?php
$pageTitle = 'Edit Step 3 Question';
require_once __DIR__ . '/../../../../admin/common/admin.html.head.php';
?>
<main class="admin-main-container">
    <?php require_once __DIR__ . '/../../../../admin/common/admin.sidebar.php'; ?>

    <section class="admin-main-content">
        <h1>Edit Step 3 Question</h1>

        <div class="admin-sub-container-2">
            <?php if ($error !== ''): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($editQuestion): ?>
                <form method="POST" class="admin-form" style="max-width: 800px;">
                    <input type="hidden" name="questionId" value="<?= (int) $editQuestion['id'] ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="moduleId">Module</label>
                            <select class="form-select" id="moduleId" name="moduleId">
                                <?php foreach ($modules as $module): ?>
                                    <option value="<?= (int) $module['id'] ?>" <?= $editQuestion['moduleId'] === $module['id'] ? 'selected' : '' ?>><?= htmlspecialchars($module['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="scaleId">Scale Type</label>
                            <select class="form-select" id="scaleId" name="scaleId">
                                <?php foreach ($scales as $scale): ?>
                                    <option value="<?= (int) $scale['id'] ?>" <?= $editQuestion['scaleId'] === $scale['id'] ? 'selected' : '' ?>><?= htmlspecialchars($scale['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="questionText">Question Text</label>
                        <textarea class="form-textarea" id="questionText" name="questionText" rows="3" required><?= htmlspecialchars($editQuestion['questionText']) ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="weight">Weight</label>
                            <input class="form-input" id="weight" name="weight" type="number" step="0.1" min="0" max="10" value="<?= htmlspecialchars((string) $editQuestion['weight']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="ACTIVE" <?= $editQuestion['status'] === 'ACTIVE' ? 'selected' : '' ?>>Active</option>
                                <option value="DISABLED" <?= $editQuestion['status'] === 'DISABLED' ? 'selected' : '' ?>>Disabled</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="/admin/recovery-plans?tab=onboarding" class="admin-button admin-button--secondary">Cancel</a>
                        <button type="submit" class="admin-button admin-button--primary">Update Question</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../../../../admin/common/admin.footer.php'; ?>
</body>
</html>