<?php
$pageTitle = 'User Details';
require_once __DIR__ . '/../../common/admin.html.head.php';

$formatNullable = static function ($value, string $fallback = '-'): string {
    $stringValue = trim((string) ($value ?? ''));
    return $stringValue !== '' ? $stringValue : $fallback;
};
?>
<main class="admin-main-container">
    <?php require_once __DIR__ . '/../../common/admin.sidebar.php'; ?>

    <section class="admin-main-content">
        <div class="admin-sub-container-1" style="justify-content: space-between; align-items: center;">
            <h1>User Details</h1>
            <a href="/admin/user-management" class="admin-button admin-button--secondary">
                <span class="button-text">Back to User Management</span>
            </a>
        </div>

        <div class="admin-sub-container-2">
            <?php if ($error !== ''): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php elseif ($viewUser): ?>
                <table class="admin-table">
                    <tbody class="admin-table-body">
                        <tr class="admin-table-row admin-table-row--even"><td class="admin-table-td"><strong>User ID</strong></td><td class="admin-table-td">#<?= (int) $viewUser['userId'] ?></td></tr>
                        <tr class="admin-table-row admin-table-row--odd"><td class="admin-table-td"><strong>Email</strong></td><td class="admin-table-td"><?= htmlspecialchars($formatNullable($viewUser['email'])) ?></td></tr>
                        <tr class="admin-table-row admin-table-row--even"><td class="admin-table-td"><strong>Username</strong></td><td class="admin-table-td"><?= htmlspecialchars($formatNullable($viewUser['username'])) ?></td></tr>
                        <tr class="admin-table-row admin-table-row--odd"><td class="admin-table-td"><strong>Role</strong></td><td class="admin-table-td"><?= htmlspecialchars(ucfirst($viewUser['role'])) ?></td></tr>
                        <tr class="admin-table-row admin-table-row--even"><td class="admin-table-td"><strong>Display Name</strong></td><td class="admin-table-td"><?= htmlspecialchars($formatNullable($viewUser['displayName'])) ?></td></tr>
                        <tr class="admin-table-row admin-table-row--odd"><td class="admin-table-td"><strong>First Name</strong></td><td class="admin-table-td"><?= htmlspecialchars($formatNullable($viewUser['firstName'])) ?></td></tr>
                        <tr class="admin-table-row admin-table-row--even"><td class="admin-table-td"><strong>Last Name</strong></td><td class="admin-table-td"><?= htmlspecialchars($formatNullable($viewUser['lastName'])) ?></td></tr>
                        <tr class="admin-table-row admin-table-row--odd"><td class="admin-table-td"><strong>Phone Number</strong></td><td class="admin-table-td"><?= htmlspecialchars($formatNullable($viewUser['phoneNumber'])) ?></td></tr>
                        <tr class="admin-table-row admin-table-row--even"><td class="admin-table-td"><strong>Age</strong></td><td class="admin-table-td"><?= $viewUser['age'] !== null ? (int) $viewUser['age'] : '-' ?></td></tr>
                        <tr class="admin-table-row admin-table-row--odd"><td class="admin-table-td"><strong>Gender</strong></td><td class="admin-table-td"><?= htmlspecialchars($formatNullable($viewUser['gender'])) ?></td></tr>
                        <tr class="admin-table-row admin-table-row--even"><td class="admin-table-td"><strong>Profile Picture URL</strong></td><td class="admin-table-td"><?= htmlspecialchars($formatNullable($viewUser['profilePicture'])) ?></td></tr>
                        <tr class="admin-table-row admin-table-row--odd"><td class="admin-table-td"><strong>Bio</strong></td><td class="admin-table-td"><?= nl2br(htmlspecialchars($formatNullable($viewUser['bio']))) ?></td></tr>
                        <tr class="admin-table-row admin-table-row--even"><td class="admin-table-td"><strong>Active</strong></td><td class="admin-table-td"><?= $viewUser['isActive'] ? 'Yes' : 'No' ?></td></tr>
                        <tr class="admin-table-row admin-table-row--odd"><td class="admin-table-td"><strong>Onboarding Completed</strong></td><td class="admin-table-td"><?= $viewUser['onboardingCompleted'] ? 'Yes' : 'No' ?></td></tr>
                        <tr class="admin-table-row admin-table-row--even"><td class="admin-table-td"><strong>Current Onboarding Step</strong></td><td class="admin-table-td"><?= (int) $viewUser['currentOnboardingStep'] ?></td></tr>
                        <tr class="admin-table-row admin-table-row--odd"><td class="admin-table-td"><strong>Created At</strong></td><td class="admin-table-td"><?= htmlspecialchars($formatNullable($viewUser['createdAt'])) ?></td></tr>
                        <tr class="admin-table-row admin-table-row--even"><td class="admin-table-td"><strong>Last Login</strong></td><td class="admin-table-td"><?= htmlspecialchars($formatNullable($viewUser['lastLogin'])) ?></td></tr>
                    </tbody>
                </table>

                <div class="admin-sub-container-1" style="margin-top: var(--spacing-lg); gap: var(--spacing-sm);">
                    <a href="/admin/user-management/edit?id=<?= (int) $viewUser['userId'] ?>" class="admin-button admin-button--primary">
                        <span class="button-text">Edit User</span>
                    </a>
                    <button type="button" class="admin-button admin-button--danger" onclick="deleteUserFromView(<?= (int) $viewUser['userId'] ?>, <?= json_encode($viewUser['fullName']) ?>)">
                        <span class="button-text">Delete User</span>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<script>
function deleteUserFromView(userId, fullName) {
    if (!confirm('Delete "' + fullName + '"? This action cannot be undone.')) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/admin/user-management/delete';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'userId';
    input.value = String(userId);

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php require_once __DIR__ . '/../../common/admin.footer.php'; ?>
</body>
</html>
