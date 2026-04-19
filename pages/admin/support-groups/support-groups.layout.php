<?php
$pageTitle = 'Support Groups';
require_once __DIR__ . '/../common/admin.html.head.php';
?>
<main class="admin-main-container">
    <?php require_once __DIR__ . '/../common/admin.sidebar.php'; ?>

    <section class="admin-main-content">
        <h1>Support Groups</h1>

        <form method="GET" class="admin-sub-container-1" style="justify-content: space-between; align-items: center;">
            <div class="admin-sub-container-1">
                <a href="/admin/support-groups/create" class="admin-button admin-button--primary">Create Group</a>
                <a href="/admin/support-groups/schedule" class="admin-button admin-button--ghost">Schedule Session</a>
            </div>
            <div class="admin-sub-container-1">
                <input type="text" name="search" value="<?= htmlspecialchars($filters['search']) ?>" placeholder="Search groups...">
                <select name="status" class="admin-dropdown">
                    <option value="all">All Status</option>
                    <option value="Active" <?= $filters['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                    <option value="Archived" <?= $filters['status'] === 'Archived' ? 'selected' : '' ?>>Archived</option>
                </select>
                <button class="admin-button admin-button--secondary">Filter</button>
            </div>
        </form>

            <div class="admin-table-container">
            <table class="admin-table">
                <thead class="admin-table-header">
                    <tr class="admin-table-row">
                        <th class="admin-table-th">Group Name</th>
                        <th class="admin-table-th">Members</th>
                        <th class="admin-table-th">Next Session</th>
                        <th class="admin-table-th">Created By</th>
                        <th class="admin-table-th">Status</th>
                    </tr>
                </thead>
                <tbody class="admin-table-body">
                    <?php foreach ($groups as $index => $group): ?>
                        <tr class="admin-table-row <?= $index % 2 === 0 ? 'admin-table-row--even' : 'admin-table-row--odd' ?>">
                            <td class="admin-table-td">
                                <strong><?= htmlspecialchars($group['groupName']) ?></strong><br>
                                <small><?= htmlspecialchars($group['description']) ?></small>
                            </td>
                            <td class="admin-table-td"><?= $group['members'] ?></td>
                            <td class="admin-table-td"><?= htmlspecialchars($group['nextSession']) ?></td>
                            <td class="admin-table-td"><?= htmlspecialchars($group['createdBy']) ?></td>
                            <td class="admin-table-td"><?= htmlspecialchars($group['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="pagination-container">
                <?php
                $pagination = $groupsPagination;
                $basePath = '/admin/support-groups';
                $query = $filters;
                require __DIR__ . '/../common/admin.pagination.php';
                ?>
            </div>
        </div>


        <div class="admin-sub-container-2">
            <div class="admin-data-card">
                <h3>Upcoming Sessions Calendar</h3>
                <?php foreach ($appointments as $item): ?>
                    <p><strong><?= htmlspecialchars($item['day']) ?></strong> <?= htmlspecialchars($item['time']) ?> - <?= htmlspecialchars($item['title']) ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../common/admin.footer.php'; ?>
</body>
</html>
