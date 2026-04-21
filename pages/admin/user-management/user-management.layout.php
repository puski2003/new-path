<?php
$pageTitle = 'User Management';
require_once __DIR__ . '/../common/admin.html.head.php';
?>
<main class="admin-main-container">
    <?php require_once __DIR__ . '/../common/admin.sidebar.php'; ?>

    <section class="admin-main-content">
        <h1>User Management</h1>

        <form method="GET" action="/admin/user-management" class="admin-sub-container-2" style="padding: var(--spacing-lg); border-radius: var(--radius-sm);">
            <div class="admin-sub-container-1" style="justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <div class="admin-sub-container-1" style="flex-wrap: wrap;">
                    <label>Role:
                        <select name="role" class="admin-dropdown">
                            <?php foreach (['all' => 'All Roles', 'Recovering User' => 'Recovering User', 'Counselor' => 'Counselor', 'Admin' => 'Admin'] as $value => $label): ?>
                                <option value="<?= htmlspecialchars($value) ?>" <?= $filters['role'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Status:
                        <select name="status" class="admin-dropdown">
                            <?php foreach (['all' => 'All Status', 'Active' => 'Active', 'Pending' => 'Pending', 'Inactive' => 'Inactive'] as $value => $label): ?>
                                <option value="<?= htmlspecialchars($value) ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Date Joined:
                        <input type="date" name="dateJoined" value="<?= htmlspecialchars($filters['dateJoined']) ?>">
                    </label>
                    <label>Engagement:
                        <select name="engagement" class="admin-dropdown">
                            <?php foreach (['all' => 'All Levels', 'High' => 'High', 'Medium' => 'Medium', 'Low' => 'Low'] as $value => $label): ?>
                                <option value="<?= htmlspecialchars($value) ?>" <?= $filters['engagement'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <button type="submit" class="admin-button admin-button--primary"><span class="button-text">Apply Filters</span></button>
            </div>

            <div class="admin-sub-container-1" style="justify-content: space-between; align-items: center;">
                <input type="text" name="search" class="admin-searchbar" placeholder="Search by name or email..." value="<?= htmlspecialchars($filters['search']) ?>" style="max-width:400px;">
                <a href="/admin/user-management" class="admin-button admin-button--secondary"><span class="button-text">Reset</span></a>
            </div>
        </form>

        <div class="admin-table-container">
            <table class="admin-table">
                <thead class="admin-table-header">
                <tr class="admin-table-row">
                    <th class="admin-table-th">User ID</th>
                    <th class="admin-table-th">Full Name</th>
                    <th class="admin-table-th">Role</th>
                    <th class="admin-table-th">Status</th>
                    <th class="admin-table-th">Last Active</th>
                    <th class="admin-table-th">Registration</th>
                    <th class="admin-table-th">Actions</th>
                </tr>
                </thead>
                <tbody class="admin-table-body">
                <?php if ($users === []): ?>
                    <tr class="admin-table-row">
                        <td class="admin-table-td" colspan="7">No users found.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($users as $index => $item): ?>
                    <tr class="admin-table-row <?= $index % 2 === 0 ? 'admin-table-row--even' : 'admin-table-row--odd' ?>">
                        <td class="admin-table-td">#<?= $item['userId'] ?></td>
                        <td class="admin-table-td"><strong><?= htmlspecialchars($item['fullName']) ?></strong><br><small><?= htmlspecialchars($item['email']) ?></small></td>
                        <td class="admin-table-td"><?= htmlspecialchars($item['role']) ?></td>
                        <td class="admin-table-td">
                            <span class="admin-status-badge admin-status-badge--<?= strtolower($item['status']) ?>">
                                <?= htmlspecialchars($item['status']) ?>
                            </span>
                        </td>
                        <td class="admin-table-td"><?= htmlspecialchars($item['lastActive']) ?></td>
                        <td class="admin-table-td"><?= htmlspecialchars($item['registration']) ?></td>
                        <td class="admin-table-td admin-table-td--action">
                            <div class="admin-table-actions">
                                <a
                                    href="/admin/user-management/view?id=<?= $item['userId'] ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="admin-button admin-button--ghost admin-button--icon-only"
                                    title="View full user details"
                                    aria-label="View full user details"
                                >
                                    <i data-lucide="expand" class="button-icon" stroke-width="1.75"></i>
                                </a>
                                <a
                                    href="/admin/user-management/edit?id=<?= $item['userId'] ?>"
                                    class="admin-button admin-button--ghost admin-button--icon-only"
                                    title="Edit user"
                                    aria-label="Edit user"
                                >
                                    <i data-lucide="pencil" class="button-icon" stroke-width="1.75"></i>
                                </a>
                                <?php if ($item['isBanned']): ?>
                                <button
                                    type="button"
                                    class="admin-button admin-button--secondary admin-button--icon-only"
                                    title="Unban user"
                                    aria-label="Unban user"
                                    onclick="unbanUser(<?= $item['userId'] ?>, <?= htmlspecialchars(json_encode($item['fullName']), ENT_QUOTES) ?>)"
                                >
                                    <i data-lucide="circle-check" class="button-icon" stroke-width="1.75"></i>
                                </button>
                                <?php else: ?>
                                <button
                                    type="button"
                                    class="admin-button admin-button--danger admin-button--icon-only"
                                    title="Ban user"
                                    aria-label="Ban user"
                                    onclick="banUser(<?= $item['userId'] ?>, <?= htmlspecialchars(json_encode($item['fullName']), ENT_QUOTES) ?>)"
                                >
                                    <i data-lucide="ban" class="button-icon" stroke-width="1.75"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="pagination-container">
                <?php
                    $basePath = '/admin/user-management';
                    $query = $filters;
                    require __DIR__ . '/../common/admin.pagination.php';
                ?>
            </div>
        </div>
    </section>
</main>

<script>
(function () {
    const params = new URLSearchParams(window.location.search);
    const type = params.get('alertType');
    const message = params.get('alertMessage');

    if (message && window.NewPathAlert) {
        if (type === 'success') NewPathAlert.success(message);
        else if (type === 'warning') NewPathAlert.warning(message);
        else if (type === 'error') NewPathAlert.error(message);
        else NewPathAlert.info(message);
    }
})();

function postAction(action, userId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = action;

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'userId';
    input.value = String(userId);

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

function banUser(userId, fullName) {
    if (!confirm('Ban "' + fullName + '"? They will not be able to log in until unbanned.')) return;
    postAction('/admin/user-management/delete', userId);
}

function unbanUser(userId, fullName) {
    if (!confirm('Unban "' + fullName + '"? They will be able to log in again.')) return;
    postAction('/admin/user-management/unban', userId);
}

document.addEventListener('DOMContentLoaded', function () {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>

<?php require_once __DIR__ . '/../common/admin.footer.php'; ?>
</body>
</html>
