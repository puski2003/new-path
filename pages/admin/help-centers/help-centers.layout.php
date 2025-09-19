<?php
$pageTitle = 'Help Centers & Hotlines';
require_once __DIR__ . '/../common/admin.html.head.php';
?>
<main class="admin-main-container">
    <?php require_once __DIR__ . '/../common/admin.sidebar.php'; ?>

    <section class="admin-main-content">
        <h1>Help Centers &amp; Hotlines</h1>

        <div class="admin-sub-container-2">
            <div class="admin-sub-container-1" style="justify-content: space-between; align-items: center;">
                <h2>Help Centers &amp; Hotlines</h2>
                <a href="/admin/help-centers/add" class="admin-button admin-button--primary"><span class="button-text">Add Help Center</span></a>
            </div>

            <form method="GET" action="/admin/help-centers" class="admin-filter-bar">
                <select name="centerStatus" class="admin-dropdown">
                    <option value="all">All Status</option>
                    <option value="active" <?= $filters['centerStatus'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $filters['centerStatus'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
                <input type="text" name="type" value="<?= htmlspecialchars($filters['type'] === 'all' ? '' : $filters['type']) ?>" placeholder="Type">
                <input type="text" name="centerCategory" value="<?= htmlspecialchars($filters['centerCategory'] === 'all' ? '' : $filters['centerCategory']) ?>" placeholder="Category">
                <button type="submit" class="admin-button admin-button--secondary">Filter</button>
            </form>

            <div class="admin-table-container">
            <table class="admin-table">
                <thead class="admin-table-header">
                    <tr class="admin-table-row">
                        <th class="admin-table-th">Name</th>
                        <th class="admin-table-th">Organization</th>
                        <th class="admin-table-th">Type</th>
                        <th class="admin-table-th">Category</th>
                        <th class="admin-table-th">Phone Number</th>
                        <th class="admin-table-th">Availability</th>
                        <th class="admin-table-th">Status</th>
                        <th class="admin-table-th">Actions</th>
                    </tr>
                </thead>
                <tbody class="admin-table-body">
                    <?php if ($helpCenters === []): ?>
                        <tr class="admin-table-row">
                            <td class="admin-table-td" colspan="8">No help centers found.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($helpCenters as $index => $center): ?>
                        <tr class="admin-table-row <?= $index % 2 === 0 ? 'admin-table-row--even' : 'admin-table-row--odd' ?>">
                            <td class="admin-table-td"><?= htmlspecialchars($center['name']) ?></td>
                            <td class="admin-table-td"><?= htmlspecialchars($center['organization']) ?></td>
                            <td class="admin-table-td"><?= htmlspecialchars($center['type']) ?></td>
                            <td class="admin-table-td"><?= htmlspecialchars($center['category']) ?></td>
                            <td class="admin-table-td"><?= htmlspecialchars($center['phoneNumber']) ?></td>
                            <td class="admin-table-td"><?= htmlspecialchars($center['availability']) ?></td>
                            <td class="admin-table-td"><?= $center['active'] ? 'Active' : 'Inactive' ?></td>
                            <td class="admin-table-td admin-table-td--action">
                                <div class="admin-table-actions">
                                    <a href="/admin/help-centers/edit?id=<?= $center['helpCenterId'] ?>" class="admin-button admin-button--ghost">Edit</a>
                                    <button type="button" class="admin-button admin-button--danger" onclick="deleteHelpCenter(<?= $center['helpCenterId'] ?>, <?= json_encode($center['name']) ?>)">Delete</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
</div>

            <div class="pagination-container">
            <?php
            $pagination = $helpCentersPagination;
            $basePath = '/admin/help-centers';
            $query = $filters;
            $forceShow = true;
            require __DIR__ . '/../common/admin.pagination.php';
            ?>
</div>
        </div>
    </section>
</main>

<script>
    function deleteHelpCenter(helpCenterId, title) {
        if (!confirm('Delete "' + title + '"?')) return;
        fetch('/admin/help-centers/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ helpCenterId })
        }).then(() => window.location.reload());
    }
</script>

<?php require_once __DIR__ . '/../common/admin.footer.php'; ?>
</body>
</html>
