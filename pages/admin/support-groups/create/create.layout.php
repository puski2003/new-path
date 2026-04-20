<?php
$pageTitle = 'Create Support Group';
require_once __DIR__ . '/../../common/admin.html.head.php';
?>
<main class="admin-main-container">
    <?php require_once __DIR__ . '/../../common/admin.sidebar.php'; ?>
    <section class="admin-main-content">
        <h1>Create Support Group</h1>
        <div class="admin-sub-container-2">
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>
            <form method="POST" class="admin-form" style="max-width: 800px;">
                <div class="form-group">
                    <label class="form-label" for="name">Group Name *</label>
                    <input class="form-input" type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required />
                </div>

                <div class="form-group">
                    <label class="form-label" for="category">Category *</label>
                    <select class="form-select" id="category" name="category" required>
                        <option value="">Select a category</option>
                        <option value="alcohol" <?= ($_POST['category'] ?? '') === 'alcohol' ? 'selected' : '' ?>>Alcohol</option>
                        <option value="substance" <?= ($_POST['category'] ?? '') === 'substance' ? 'selected' : '' ?>>Substance</option>
                        <option value="gambling" <?= ($_POST['category'] ?? '') === 'gambling' ? 'selected' : '' ?>>Gambling</option>
                        <option value="general" <?= ($_POST['category'] ?? '') === 'general' ? 'selected' : '' ?>>General</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-textarea" id="description" name="description" rows="4"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="meeting_schedule">Meeting Schedule</label>
                        <input class="form-input" type="text" id="meeting_schedule" name="meeting_schedule" value="<?= htmlspecialchars($_POST['meeting_schedule'] ?? '') ?>" placeholder="e.g., Mondays 6:00 PM" />
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="max_members">Max Members</label>
                        <input class="form-input" type="number" id="max_members" name="max_members" value="<?= htmlspecialchars($_POST['max_members'] ?? '') ?>" min="1" placeholder="Leave empty for unlimited" />
                    </div>
                </div>

                <div class="form-actions">
                    <a href="/admin/support-groups" class="admin-button admin-button--secondary">Cancel</a>
                    <button type="submit" class="admin-button admin-button--primary">Create Group</button>
                </div>
            </form>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../../common/admin.footer.php'; ?>
</body>
</html>
