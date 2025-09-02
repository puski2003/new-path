<?php
$pageTitle = 'Edit User';
require_once __DIR__ . '/../../common/admin.html.head.php';
?>
<main class="admin-main-container">
    <?php require_once __DIR__ . '/../../common/admin.sidebar.php'; ?>

    <section class="admin-main-content">
        <div class="admin-sub-container-1" style="justify-content: space-between; align-items: center;">
            <h1>Edit User</h1>
            <a href="/admin/user-management" class="admin-button admin-button--secondary">
                <span class="button-text">Back to User Management</span>
            </a>
        </div>

        <div class="admin-sub-container-2">
            <?php if ($error !== ''): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($editUser): ?>
                <form method="POST" class="admin-form" style="max-width: 800px;">
                    <input type="hidden" name="userId" value="<?= (int) $editUser['userId'] ?>">
                    <input type="hidden" name="actorUserId" value="<?= (int) ($user['id'] ?? 0) ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="email">Email</label>
                            <input class="form-input" id="email" name="email" type="email" value="<?= htmlspecialchars($editUser['email']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="username">Username</label>
                            <input class="form-input" id="username" name="username" value="<?= htmlspecialchars($editUser['username']) ?>" placeholder="letters, numbers, underscores">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="displayName">Display Name</label>
                            <input class="form-input" id="displayName" name="displayName" value="<?= htmlspecialchars($editUser['displayName']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="phoneNumber">Phone Number</label>
                            <input class="form-input" id="phoneNumber" name="phoneNumber" value="<?= htmlspecialchars($editUser['phoneNumber']) ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="firstName">First Name</label>
                            <input class="form-input" id="firstName" name="firstName" value="<?= htmlspecialchars($editUser['firstName']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="lastName">Last Name</label>
                            <input class="form-input" id="lastName" name="lastName" value="<?= htmlspecialchars($editUser['lastName']) ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="profilePicture">Profile Picture URL</label>
                            <input class="form-input" id="profilePicture" name="profilePicture" value="<?= htmlspecialchars($editUser['profilePicture']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="age">Age</label>
                            <input class="form-input" id="age" name="age" type="number" min="13" max="120" value="<?= htmlspecialchars($editUser['age'] !== null ? (string) $editUser['age'] : '') ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="gender">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="" <?= $editUser['gender'] === '' ? 'selected' : '' ?>>Not specified</option>
                                <option value="male" <?= $editUser['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= $editUser['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= $editUser['gender'] === 'other' ? 'selected' : '' ?>>Other</option>
                                <option value="prefer_not_to_say" <?= $editUser['gender'] === 'prefer_not_to_say' ? 'selected' : '' ?>>Prefer not to say</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="role">Role</label>
                            <select class="form-select" id="role" name="role">
                                <option value="user" <?= $editUser['role'] === 'user' ? 'selected' : '' ?>>Recovering User</option>
                                <option value="counselor" <?= $editUser['role'] === 'counselor' ? 'selected' : '' ?>>Counselor</option>
                                <option value="admin" <?= $editUser['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="currentOnboardingStep">Current Onboarding Step</label>
                            <input class="form-input" id="currentOnboardingStep" name="currentOnboardingStep" type="number" min="1" max="10" value="<?= (int) $editUser['currentOnboardingStep'] ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="bio">Bio</label>
                        <textarea class="form-textarea" id="bio" name="bio" rows="4"><?= htmlspecialchars($editUser['bio']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="isActive" value="1" <?= $editUser['isActive'] ? 'checked' : '' ?>>
                            Active account
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="onboardingCompleted" value="1" <?= $editUser['onboardingCompleted'] ? 'checked' : '' ?>>
                            Onboarding completed
                        </label>
                    </div>

                    <div class="form-actions">
                        <a href="/admin/user-management" class="admin-button admin-button--secondary">Cancel</a>
                        <button type="submit" class="admin-button admin-button--primary">Update User</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../../common/admin.footer.php'; ?>
</body>
</html>
