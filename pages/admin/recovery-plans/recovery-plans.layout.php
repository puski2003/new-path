<?php
$pageTitle = 'Recovery Plans & Templates';
require_once __DIR__ . '/../common/admin.html.head.php';
?>
<main class="admin-main-container">
    <?php require_once __DIR__ . '/../common/admin.sidebar.php'; ?>

    <section class="admin-main-content">
        <h1>Recovery Plans &amp; Templates</h1>

        <div class="admin-tab-nav">
            <a class="admin-tab-nav__item<?= $activeTab === 'pre-built' ? ' admin-tab-nav__item--active' : '' ?>" href="/admin/recovery-plans?tab=pre-built">Pre-built Recovery Plans</a>
            <a class="admin-tab-nav__item<?= $activeTab === 'onboarding' ? ' admin-tab-nav__item--active' : '' ?>" href="/admin/recovery-plans?tab=onboarding">Onboarding Questionnaires</a>
        </div>

        <?php if ($activeTab === 'pre-built'): ?>
            <div class="admin-sub-container-2">
                <div class="admin-page-header-row">
                    <form method="GET" class="recovery-plans-actions">
                        <input type="hidden" name="tab" value="pre-built">
                        <input type="text" name="search" placeholder="Search plans..." value="<?= htmlspecialchars($filters['search']) ?>">
                        <input type="text" name="category" placeholder="Category" value="<?= htmlspecialchars($filters['category'] === 'all' ? '' : $filters['category']) ?>">
                        <button class="admin-button admin-button--secondary">Filter</button>
                    </form>
                    <a href="/admin/recovery-plans/create" class="admin-button admin-button--primary">+ Create Plan</a>
                </div>
                <table class="admin-table"><thead class="admin-table-header"><tr class="admin-table-row"><th class="admin-table-th">Plan Name</th><th class="admin-table-th">Category</th><th class="admin-table-th">Adoptions</th><th class="admin-table-th">Created By</th><th class="admin-table-th">Last Updated</th><th class="admin-table-th"></th></tr></thead><tbody class="admin-table-body"><?php if ($plans === []): ?><tr class="admin-table-row"><td class="admin-table-td" colspan="6">No plans created yet.</td></tr><?php endif; ?><?php foreach ($plans as $index => $plan): ?><tr class="admin-table-row <?= $index % 2 === 0 ? 'admin-table-row--even' : 'admin-table-row--odd' ?>"><td class="admin-table-td"><strong><?= htmlspecialchars($plan['planName']) ?></strong><br><small><?= htmlspecialchars($plan['description']) ?></small></td><td class="admin-table-td"><?= htmlspecialchars($plan['category']) ?></td><td class="admin-table-td"><?= $plan['adoptionCount'] ?></td><td class="admin-table-td"><?= htmlspecialchars($plan['createdBy']) ?></td><td class="admin-table-td"><?= htmlspecialchars($plan['lastUpdated']) ?></td><td class="admin-table-td"><a href="/admin/recovery-plans/view?planId=<?= (int)$plan['planId'] ?>" class="admin-button admin-button--ghost admin-button--sm">View</a></td></tr><?php endforeach; ?></tbody></table>

                <div class="admin-table-container">
                <table class="admin-table">
                    <thead class="admin-table-header">
                        <tr class="admin-table-row">
                            <th class="admin-table-th">Plan Name</th>
                            <th class="admin-table-th">Category</th>
                            <th class="admin-table-th">Adoption Rate</th>
                            <th class="admin-table-th">Created By</th>
                            <th class="admin-table-th">Last Updated</th>
                        </tr>
                    </thead>
                    <tbody class="admin-table-body">
                        <?php foreach ($plans as $index => $plan): ?>
                            <tr class="admin-table-row <?= $index % 2 === 0 ? 'admin-table-row--even' : 'admin-table-row--odd' ?>">
                                <td class="admin-table-td">
                                    <strong><?= htmlspecialchars($plan['planName']) ?></strong><br>
                                    <small><?= htmlspecialchars($plan['description']) ?></small>
                                </td>
                                <td class="admin-table-td"><?= htmlspecialchars($plan['category']) ?></td>
                                <td class="admin-table-td"><?= $plan['adoptionCount'] ?>%</td>
                                <td class="admin-table-td"><?= htmlspecialchars($plan['createdBy']) ?></td>
                                <td class="admin-table-td"><?= htmlspecialchars($plan['lastUpdated']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
</div>

                <div class="pagination-container">
                    <?php
                        $pagination = $plansPagination;
                        $basePath = '/admin/recovery-plans';
                        $query = array_merge($filters, ['tab' => $activeTab]);
                        require __DIR__ . '/../common/admin.pagination.php';
                    ?>
                </div>
            </div>
        <?php else: ?>
            <div class="admin-sub-container-2">
                <div class="admin-sub-container-1" style="justify-content: space-between; align-items: center;">
                    <h2>Step 2 Questions</h2>
                    <a href="/admin/recovery-plans/create/ob-question2" class="admin-button admin-button--primary">+ Create</a>
                </div>

                <form method="GET" action="/admin/recovery-plans" class="admin-sub-container-2" style="padding: var(--spacing-lg); border-radius: var(--radius-sm);">
                    <input type="hidden" name="tab" value="onboarding">
                    <div class="admin-sub-container-1" style="flex-wrap: wrap; gap: var(--spacing-md);">
                        <label>Scale Type:
                            <select name="scaleType" class="admin-dropdown">
                                <option value="all" <?= ($step2Filters['scaleType'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Scale Types</option>
                                <?php foreach ($scales as $scale): ?>
                                    <option value="<?= htmlspecialchars($scale['name']) ?>" <?= ($step2Filters['scaleType'] ?? '') === $scale['name'] ? 'selected' : '' ?>><?= htmlspecialchars($scale['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Path:
                            <select name="path" class="admin-dropdown">
                                <option value="all" <?= ($step2Filters['path'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Paths</option>
                                <option value="BOTH" <?= ($step2Filters['path'] ?? '') === 'BOTH' ? 'selected' : '' ?>>Both</option>
                                <option value="NEW_USER" <?= ($step2Filters['path'] ?? '') === 'NEW_USER' ? 'selected' : '' ?>>New User</option>
                                <option value="EXISTING_USER" <?= ($step2Filters['path'] ?? '') === 'EXISTING_USER' ? 'selected' : '' ?>>Existing User</option>
                            </select>
                        </label>
                        <label>Weight:
                            <input type="number" name="weight" step="0.1" min="0" placeholder="Exact value" value="<?= htmlspecialchars($step2Filters['weight'] ?? '') ?>" style="width: 100px;">
                        </label>
                        <label>Status:
                            <select name="status" class="admin-dropdown">
                                <option value="all" <?= ($step2Filters['status'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Status</option>
                                <option value="ACTIVE" <?= ($step2Filters['status'] ?? '') === 'ACTIVE' ? 'selected' : '' ?>>Active</option>
                                <option value="DISABLED" <?= ($step2Filters['status'] ?? '') === 'DISABLED' ? 'selected' : '' ?>>Disabled</option>
                            </select>
                        </label>
                    </div>
                    <div class="admin-sub-container-1" style="justify-content: flex-start; align-items: center; gap: var(--spacing-sm);">
                        <button type="submit" class="admin-button admin-button--primary">Apply Filters</button>
                        <a href="/admin/recovery-plans?tab=onboarding" class="admin-button admin-button--secondary">Reset</a>
                    </div>
                </form>

                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead class="admin-table-header">
                            <tr class="admin-table-row">
                                <th class="admin-table-th">ID</th>
                                <th class="admin-table-th">Question Text</th>
                                <th class="admin-table-th">Scale Type</th>
                                <th class="admin-table-th">Path</th>
                                <th class="admin-table-th">Weight</th>
                                <th class="admin-table-th">Status</th>
                                <th class="admin-table-th">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="admin-table-body">
                            <?php if ($step2Questions === []): ?>
                                <tr class="admin-table-row"><td class="admin-table-td" colspan="7">No questions found.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($step2Questions as $index => $question): ?>
                                <tr class="admin-table-row <?= $index % 2 === 0 ? 'admin-table-row--even' : 'admin-table-row--odd' ?>">
                                    <td class="admin-table-td"><strong><?= (int) $question['id'] ?></strong></td>
                                    <td class="admin-table-td"><?= htmlspecialchars($question['questionText']) ?></td>
                                    <td class="admin-table-td"><?= htmlspecialchars($question['scaleType']) ?></td>
                                    <td class="admin-table-td"><?= htmlspecialchars($question['path']) ?></td>
                                    <td class="admin-table-td"><?= htmlspecialchars((string) $question['weight']) ?></td>
                                    <td class="admin-table-td"><?= htmlspecialchars($question['status']) ?></td>
                                    <td class="admin-table-td admin-table-td--action">
                                        <div class="admin-table-actions">
                                            <a href="/admin/recovery-plans/edit/ob-question2?id=<?= (int) $question['id'] ?>" class="admin-button admin-button--ghost">Edit</a>
                                            <a href="/admin/recovery-plans/toggle/step2?id=<?= (int) $question['id'] ?>" class="admin-button admin-button--ghost"><?= $question['status'] === 'ACTIVE' ? 'Disable' : 'Enable' ?></a>
                                            <button type="button" class="admin-button admin-button--ghost admin-button--danger" data-delete-url="/admin/recovery-plans/delete/step2?id=<?= (int) $question['id'] ?>" data-delete-title="Delete Question">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination-container">
                    <?php
                        $pagination = $step2Pagination;
                        $basePath = '/admin/recovery-plans';
                        $query = array_merge($step2Filters, ['tab' => $activeTab]);
                        require __DIR__ . '/../common/admin.pagination.php';
                    ?>
                </div>
            </div>

            <div class="admin-sub-container-2">
                <div class="admin-sub-container-1" style="justify-content: space-between; align-items: center;">
                    <h2>Step 3 Questions</h2>
                    <a href="/admin/recovery-plans/create/ob-question3" class="admin-button admin-button--primary">+ Create</a>
                </div>

                <form method="GET" action="/admin/recovery-plans" class="admin-sub-container-2" style="padding: var(--spacing-lg); border-radius: var(--radius-sm);">
                    <input type="hidden" name="tab" value="onboarding">
                    <div class="admin-sub-container-1" style="flex-wrap: wrap; gap: var(--spacing-md);">
                        <label>Module:
                            <select name="module" class="admin-dropdown">
                                <option value="all" <?= ($step3Filters['module'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Modules</option>
                                <?php foreach ($modules as $module): ?>
                                    <option value="<?= htmlspecialchars($module['name']) ?>" <?= ($step3Filters['module'] ?? '') === $module['name'] ? 'selected' : '' ?>><?= htmlspecialchars($module['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Scale Type:
                            <select name="scaleType" class="admin-dropdown">
                                <option value="all" <?= ($step3Filters['scaleType'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Scale Types</option>
                                <?php foreach ($scales as $scale): ?>
                                    <option value="<?= htmlspecialchars($scale['name']) ?>" <?= ($step3Filters['scaleType'] ?? '') === $scale['name'] ? 'selected' : '' ?>><?= htmlspecialchars($scale['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Weight:
                            <input type="number" name="weight" step="0.1" min="0" placeholder="Exact value" value="<?= htmlspecialchars($step3Filters['weight'] ?? '') ?>" style="width: 100px;">
                        </label>
                        <label>Status:
                            <select name="status" class="admin-dropdown">
                                <option value="all" <?= ($step3Filters['status'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Status</option>
                                <option value="ACTIVE" <?= ($step3Filters['status'] ?? '') === 'ACTIVE' ? 'selected' : '' ?>>Active</option>
                                <option value="DISABLED" <?= ($step3Filters['status'] ?? '') === 'DISABLED' ? 'selected' : '' ?>>Disabled</option>
                            </select>
                        </label>
                    </div>
                    <div class="admin-sub-container-1" style="justify-content: flex-start; align-items: center; gap: var(--spacing-sm);">
                        <button type="submit" class="admin-button admin-button--primary">Apply Filters</button>
                        <a href="/admin/recovery-plans?tab=onboarding" class="admin-button admin-button--secondary">Reset</a>
                    </div>
                </form>

                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead class="admin-table-header">
                            <tr class="admin-table-row">
                                <th class="admin-table-th">ID</th>
                                <th class="admin-table-th">Module</th>
                                <th class="admin-table-th">Question Text</th>
                                <th class="admin-table-th">Scale Type</th>
                                <th class="admin-table-th">Weight</th>
                                <th class="admin-table-th">Status</th>
                                <th class="admin-table-th">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="admin-table-body">
                            <?php if ($step3Questions === []): ?>
                                <tr class="admin-table-row"><td class="admin-table-td" colspan="7">No questions found.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($step3Questions as $index => $question): ?>
                                <tr class="admin-table-row <?= $index % 2 === 0 ? 'admin-table-row--even' : 'admin-table-row--odd' ?>">
                                    <td class="admin-table-td"><strong><?= (int) $question['id'] ?></strong></td>
                                    <td class="admin-table-td"><?= htmlspecialchars($question['module']) ?></td>
                                    <td class="admin-table-td"><?= htmlspecialchars($question['questionText']) ?></td>
                                    <td class="admin-table-td"><?= htmlspecialchars($question['scaleType']) ?></td>
                                    <td class="admin-table-td"><?= htmlspecialchars((string) $question['weight']) ?></td>
                                    <td class="admin-table-td"><?= htmlspecialchars($question['status']) ?></td>
                                    <td class="admin-table-td admin-table-td--action">
                                        <div class="admin-table-actions">
                                            <a href="/admin/recovery-plans/edit/ob-question3?id=<?= (int) $question['id'] ?>" class="admin-button admin-button--ghost">Edit</a>
                                            <a href="/admin/recovery-plans/toggle/step3?id=<?= (int) $question['id'] ?>" class="admin-button admin-button--ghost"><?= $question['status'] === 'ACTIVE' ? 'Disable' : 'Enable' ?></a>
                                            <button type="button" class="admin-button admin-button--ghost admin-button--danger" data-delete-url="/admin/recovery-plans/delete/step3?id=<?= (int) $question['id'] ?>" data-delete-title="Delete Question">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination-container">
                    <?php
                        $pagination = $step3Pagination;
                        $basePath = '/admin/recovery-plans';
                        $query = array_merge($step3Filters, ['tab' => $activeTab]);
                        require __DIR__ . '/../common/admin.pagination.php';
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="modal-overlay" id="deleteConfirmationModal" style="display: none;">
            <div class="confirmation-modal">
                <div class="confirmation-modal-header">
                    <i data-lucide="alert-triangle" class="warning-icon"></i>
                    <h3 id="deleteModalTitle">Delete Question</h3>
                </div>

                <div class="confirmation-modal-body">
                    <p>Are you sure you want to delete this question?</p>
                    <p class="warning-text">This action cannot be undone.</p>
                </div>

                <div class="confirmation-modal-actions">
                    <button type="button" class="btn btn-secondary" id="cancelDelete">
                        <i data-lucide="x" class="btn-icon"></i>
                        Cancel
                    </button>
                    <a href="#" class="btn btn-danger" id="confirmDelete">
                        <i data-lucide="trash-2" class="btn-icon"></i>
                        Delete Question
                    </a>
                </div>
            </div>
        </div>

        <style>
            .modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1001;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }
            .modal-overlay.show {
                opacity: 1;
                visibility: visible;
            }
            .confirmation-modal {
                background: white;
                border-radius: 8px;
                box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
                max-width: 450px;
                width: 90%;
                animation: modalSlideIn 0.3s ease-out;
                overflow: hidden;
            }
            @keyframes modalSlideIn {
                from { transform: translateY(-20px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
            .confirmation-modal-header {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 16px;
                border-bottom: 1px solid #e5e7eb;
                background: #fef2f2;
            }
            .warning-icon {
                width: 24px;
                height: 24px;
                color: #dc2626;
                flex-shrink: 0;
            }
            .confirmation-modal-header h3 {
                margin: 0;
                font-size: 18px;
                font-weight: 600;
                color: #111827;
            }
            .confirmation-modal-body {
                padding: 16px;
            }
            .confirmation-modal-body p {
                margin: 0 0 8px 0;
                color: #111827;
                line-height: 1.5;
            }
            .confirmation-modal-body p:last-child {
                margin-bottom: 0;
            }
            .warning-text {
                font-size: 14px;
                color: #6b7280;
                font-style: italic;
            }
            .confirmation-modal-actions {
                display: flex;
                gap: 8px;
                padding: 16px;
                border-top: 1px solid #e5e7eb;
                background: #f9fafb;
            }
            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                padding: 8px 16px;
                font-size: 14px;
                font-weight: 500;
                border-radius: 6px;
                border: none;
                cursor: pointer;
                text-decoration: none;
                transition: all 0.2s ease;
            }
            .btn-secondary {
                background: #f3f4f6;
                color: #374151;
            }
            .btn-secondary:hover {
                background: #e5e7eb;
            }
            .btn-danger {
                background: #dc2626;
                color: white;
            }
            .btn-danger:hover {
                background: #b91c1c;
            }
            .btn-icon {
                width: 16px;
                height: 16px;
            }
            .admin-button--danger {
                color: #dc2626;
            }
            .admin-button--danger:hover {
                background: #fef2f2;
                color: #b91c1c;
            }
        </style>

        <script>
            (function() {
                const modal = document.getElementById('deleteConfirmationModal');
                const cancelBtn = document.getElementById('cancelDelete');
                const confirmBtn = document.getElementById('confirmDelete');
                let pendingDeleteUrl = null;

                function showModal(deleteUrl) {
                    pendingDeleteUrl = deleteUrl;
                    if (modal) {
                        modal.style.display = 'flex';
                        modal.offsetHeight;
                        modal.classList.add('show');
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                        setTimeout(function() {
                            if (cancelBtn) cancelBtn.focus();
                        }, 100);
                    }
                }

                function hideModal() {
                    if (modal) {
                        modal.classList.remove('show');
                        setTimeout(function() {
                            modal.style.display = 'none';
                        }, 300);
                    }
                    pendingDeleteUrl = null;
                }

                if (cancelBtn) {
                    cancelBtn.addEventListener('click', hideModal);
                }

                if (confirmBtn) {
                    confirmBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (pendingDeleteUrl) {
                            window.location.href = pendingDeleteUrl;
                        }
                    });
                }

                if (modal) {
                    modal.addEventListener('click', function(e) {
                        if (e.target === modal) {
                            hideModal();
                        }
                    });
                }

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && modal && modal.style.display !== 'none') {
                        hideModal();
                    }
                });

                document.querySelectorAll('[data-delete-url]').forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        showModal(btn.dataset.deleteUrl);
                    });
                });
            })();
        </script>
    </section>
</main>

<?php require_once __DIR__ . '/../common/admin.footer.php'; ?>
</body>
</html>
