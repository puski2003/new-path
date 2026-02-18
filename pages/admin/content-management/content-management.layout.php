<?php
$pageTitle = 'Content Management';
require_once __DIR__ . '/../common/admin.html.head.php';

$statusColors = [
    'pending' => '#f59e0b',
    'reviewed' => '#3b82f6',
    'resolved' => '#10b981',
    'dismissed' => '#6b7280',
];
?>
<main class="admin-main-container">
    <?php require_once __DIR__ . '/../common/admin.sidebar.php'; ?>

    <section class="admin-main-content">
        <h1>Content Management</h1>

        <div class="admin-sub-container-1">
            <div class="admin-summary-card">
                <div class="admin-summary-card-content">
                    <p class="admin-summary-card-title">Reports Today</p>
                    <p class="admin-summary-card-info"><?= $totalReportsToday ?></p>
                </div>
            </div>
            <div class="admin-summary-card">
                <div class="admin-summary-card-content">
                    <p class="admin-summary-card-title">Pending Reports</p>
                    <p class="admin-summary-card-info"><?= $pendingReports ?></p>
                </div>
            </div>
            <div class="admin-summary-card">
                <div class="admin-summary-card-content">
                    <p class="admin-summary-card-title">Actions This Week</p>
                    <p class="admin-summary-card-info"><?= $actionsThisWeek ?></p>
                </div>
            </div>
            <div class="admin-summary-card">
                <div class="admin-summary-card-content">
                    <p class="admin-summary-card-title">Total Reports</p>
                    <p class="admin-summary-card-info"><?= $totalCount ?></p>
                </div>
            </div>
        </div>

        <div class="admin-sub-container-2">
            <form method="GET" action="/admin/content-management" class="content-management-filters">
                <div class="content-management-filters__dropdowns">
                    <select name="type" class="admin-dropdown">
                        <option value="all" <?= $filters['type'] === 'all' ? 'selected' : '' ?>>All Types</option>
                        <option value="post" <?= $filters['type'] === 'post' ? 'selected' : '' ?>>Post</option>
                        <option value="comment" <?= $filters['type'] === 'comment' ? 'selected' : '' ?>>Comment</option>
                    </select>
                    <select name="reason" class="admin-dropdown">
                        <option value="all" <?= $filters['reason'] === 'all' ? 'selected' : '' ?>>All Reasons</option>
                        <option value="spam" <?= $filters['reason'] === 'spam' ? 'selected' : '' ?>>Spam</option>
                        <option value="harassment" <?= $filters['reason'] === 'harassment' ? 'selected' : '' ?>>Harassment</option>
                        <option value="misinformation" <?= $filters['reason'] === 'misinformation' ? 'selected' : '' ?>>Misinformation</option>
                        <option value="inappropriate" <?= $filters['reason'] === 'inappropriate' ? 'selected' : '' ?>>Inappropriate</option>
                        <option value="other" <?= $filters['reason'] === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                    <select name="status" class="admin-dropdown">
                        <option value="all" <?= $filters['status'] === 'all' ? 'selected' : '' ?>>All Statuses</option>
                        <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="reviewed" <?= $filters['status'] === 'reviewed' ? 'selected' : '' ?>>Reviewed</option>
                        <option value="resolved" <?= $filters['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                        <option value="dismissed" <?= $filters['status'] === 'dismissed' ? 'selected' : '' ?>>Dismissed</option>
                    </select>
                </div>

                <div class="content-management-filters__actions">
                    <button type="submit" class="admin-button admin-button--primary">Apply Filters</button>
                    <?php if ($filters['type'] !== 'all' || $filters['reason'] !== 'all' || $filters['status'] !== 'all'): ?>
                        <a href="/admin/content-management" class="admin-button admin-button--secondary">Reset</a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="admin-sub-container-1" style="justify-content:space-between;align-items:center;">
                <h2>Content Reports</h2>
                <p style="margin:0;color:#64748b;font-size:0.9rem;"><?= $totalCount ?> total report<?= $totalCount === 1 ? '' : 's' ?></p>
            </div>

            <table class="admin-table">
                <thead class="admin-table-header">
                    <tr class="admin-table-row">
                        <th class="admin-table-th">Content Preview</th>
                        <th class="admin-table-th">Author</th>
                        <th class="admin-table-th">Type</th>
                        <th class="admin-table-th">Reason</th>
                        <th class="admin-table-th">Reported By</th>
                        <th class="admin-table-th">Date</th>
                        <th class="admin-table-th">Status</th>
                        <th class="admin-table-th">Actions</th>
                    </tr>
                </thead>
                <tbody class="admin-table-body">
                    <?php if ($reportedContent === []): ?>
                        <tr class="admin-table-row">
                            <td class="admin-table-td" colspan="8" style="text-align:center;color:#999;">No reports found for the selected filters.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($reportedContent as $index => $item): ?>
                        <?php
                        $previewText = trim((string) ($item['content_preview'] ?? ''));
                        if ($previewText === '') {
                            $previewText = '-';
                        } elseif (function_exists('mb_strimwidth')) {
                            $previewText = mb_strimwidth($previewText, 0, 60, '...');
                        } elseif (strlen($previewText) > 60) {
                            $previewText = substr($previewText, 0, 57) . '...';
                        }

                        $status = (string) ($item['status'] ?? 'pending');
                        $statusColor = $statusColors[$status] ?? '#6b7280';
                        $isActionable = in_array($status, ['pending', 'reviewed'], true);
                        $isRemoved = isset($item['post_active']) && (int) $item['post_active'] === 0;
                        ?>
                        <tr class="admin-table-row <?= $index % 2 === 0 ? 'admin-table-row--even' : 'admin-table-row--odd' ?>">
                            <td class="admin-table-td" style="max-width:260px;">
                                <span class="content-preview" title="<?= htmlspecialchars($previewText) ?>"><?= htmlspecialchars($previewText) ?></span>
                                <?php if ($isRemoved): ?>
                                    <div style="margin-top:4px;font-size:0.75rem;color:#ef4444;">Removed from community</div>
                                <?php endif; ?>
                            </td>
                            <td class="admin-table-td">
                                <?= !empty($item['is_anonymous']) ? '<em style="color:#888;">Anonymous</em>' : htmlspecialchars($item['author_name'] ?? '-') ?>
                            </td>
                            <td class="admin-table-td"><?= htmlspecialchars($item['content_type'] ?? '-') ?></td>
                            <td class="admin-table-td"><?= htmlspecialchars(ucfirst((string) ($item['reason'] ?? '-'))) ?></td>
                            <td class="admin-table-td"><?= htmlspecialchars($item['reporter_name'] ?? '-') ?></td>
                            <td class="admin-table-td" style="white-space:nowrap;">
                                <?= !empty($item['created_at']) ? htmlspecialchars(date('M j, Y', strtotime($item['created_at']))) : '-' ?>
                            </td>
                            <td class="admin-table-td">
                                <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:0.78rem;font-weight:600;background:<?= htmlspecialchars($statusColor) ?>22;color:<?= htmlspecialchars($statusColor) ?>;">
                                    <?= htmlspecialchars(ucfirst($status)) ?>
                                </span>
                            </td>
                            <td class="admin-table-td">
                                <button
                                    type="button"
                                    class="admin-button admin-button--ghost"
                                    onclick="openReviewModal(<?= (int) $item['report_id'] ?>, <?= $isActionable ? 'true' : 'false' ?>)"
                                >
                                    <?= $isActionable ? 'Review' : 'View' ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php
            $pagination = $reportedContentPagination;
            $basePath = '/admin/content-management';
            $query = $filters;
            require __DIR__ . '/../common/admin.pagination.php';
            ?>
        </div>
    </section>
</main>

<div id="reviewModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;overflow-y:auto;">
    <div style="background:#fff;max-width:620px;margin:40px auto;border-radius:12px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,0.2);">
        <div style="background:#2c3e50;padding:18px 24px;display:flex;justify-content:space-between;align-items:center;">
            <h3 style="color:#fff;margin:0;font-size:1.1rem;">Report Review</h3>
            <button type="button" onclick="closeReviewModal()" style="background:none;border:none;color:#fff;font-size:1.4rem;cursor:pointer;line-height:1;">&times;</button>
        </div>

        <div id="reviewModalBody" style="padding:24px;">
            <p style="color:#888;">Loading...</p>
        </div>

        <div id="reviewModalFooter" style="padding:16px 24px;border-top:1px solid #eee;display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap;"></div>
    </div>
</div>

<div id="actionModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:10000;">
    <div style="background:#fff;max-width:420px;margin:120px auto;border-radius:10px;padding:24px;box-shadow:0 4px 20px rgba(0,0,0,0.2);">
        <h4 id="actionModalTitle" style="margin:0 0 12px;color:#2c3e50;"></h4>
        <p id="actionModalDesc" style="color:#555;margin-bottom:16px;font-size:0.9rem;"></p>
        <textarea
            id="actionNote"
            placeholder="Add a note (optional)"
            rows="3"
            style="width:100%;box-sizing:border-box;padding:8px 10px;border:1px solid #ddd;border-radius:6px;font-size:0.9rem;resize:vertical;margin-bottom:14px;"
        ></textarea>
        <div id="actionErrorMsg" style="color:#ef4444;font-size:0.85rem;margin-bottom:10px;display:none;"></div>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <button type="button" onclick="closeActionModal()" class="admin-button admin-button--ghost">Cancel</button>
            <button type="button" id="actionConfirmBtn" class="admin-button admin-button--primary">Confirm</button>
        </div>
    </div>
</div>

<script>
var currentReportId = null;
var pendingAction = null;

function openReviewModal(reportId, isActionable) {
    currentReportId = reportId;
    document.getElementById('reviewModal').style.display = 'block';
    document.getElementById('reviewModalBody').innerHTML = '<p style="color:#888;padding:8px 0;">Loading...</p>';
    document.getElementById('reviewModalFooter').innerHTML = '';
    loadReportDetails(reportId, isActionable);
}

function closeReviewModal() {
    document.getElementById('reviewModal').style.display = 'none';
    currentReportId = null;
}

function loadReportDetails(reportId, isActionable) {
    fetch('/admin/content-management?action=get_report&report_id=' + reportId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function (response) { return response.json(); })
    .then(function (data) {
        if (!data.success || !data.report) {
            document.getElementById('reviewModalBody').innerHTML = '<p style="color:#ef4444;">Failed to load report details.</p>';
            return;
        }

        renderReport(data.report, isActionable);
    })
    .catch(function () {
        document.getElementById('reviewModalBody').innerHTML = '<p style="color:#ef4444;">Network error loading report.</p>';
    });
}

function renderReport(report, isActionable) {
    var statusColors = {
        pending: '#f59e0b',
        reviewed: '#3b82f6',
        resolved: '#10b981',
        dismissed: '#6b7280'
    };
    var statusColor = statusColors[report.status] || '#6b7280';
    var postRemoved = String(report.post_active) === '0';
    var authorDisplay = String(report.is_anonymous) === '1'
        ? '<em style="color:#888;">Anonymous</em>'
        : esc(report.author_name || '-');
    var imageHtml = '';

    if (report.image_url) {
        imageHtml = '<div style="margin:10px 0;">'
            + '<img src="' + esc(report.image_url) + '" alt="Post image" style="max-width:100%;max-height:240px;border-radius:6px;object-fit:cover;">'
            + '</div>';
    }

    var html = '<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">'
        + '<span style="padding:4px 12px;border-radius:12px;font-size:0.78rem;font-weight:600;background:' + statusColor + '22;color:' + statusColor + ';">' + cap(report.status) + '</span>'
        + '<span style="padding:4px 12px;border-radius:12px;font-size:0.78rem;background:#e2e8f0;color:#475569;">' + esc(report.content_type || 'Post') + '</span>'
        + (postRemoved ? '<span style="padding:4px 12px;border-radius:12px;font-size:0.78rem;background:#fee2e2;color:#ef4444;">Post Removed</span>' : '')
        + '</div>';

    html += '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin-bottom:16px;">'
        + '<p style="font-size:0.75rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin:0 0 6px;">Post Content</p>';
    if (report.title) {
        html += '<p style="font-weight:600;color:#1e293b;margin:0 0 6px;">' + esc(report.title) + '</p>';
    }
    html += '<p style="color:#334155;white-space:pre-wrap;word-break:break-word;margin:0;">' + esc(report.full_content || '-') + '</p>'
        + imageHtml
        + '<p style="margin:8px 0 0;font-size:0.8rem;color:#94a3b8;">By: ' + authorDisplay + '</p>'
        + '</div>';

    html += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">'
        + '<div><p style="font-size:0.75rem;color:#94a3b8;margin:0 0 2px;">Reported by</p><p style="margin:0;color:#334155;">' + esc(report.reporter_name || '-') + '</p></div>'
        + '<div><p style="font-size:0.75rem;color:#94a3b8;margin:0 0 2px;">Reason</p><p style="margin:0;color:#334155;">' + cap(report.reason) + '</p></div>'
        + '<div><p style="font-size:0.75rem;color:#94a3b8;margin:0 0 2px;">Reported on</p><p style="margin:0;color:#334155;">' + fmtDate(report.created_at) + '</p></div>'
        + '</div>';

    if (report.description) {
        html += '<div style="background:#fefce8;border:1px solid #fde68a;border-radius:6px;padding:12px;margin-bottom:16px;">'
            + '<p style="font-size:0.75rem;color:#92400e;margin:0 0 4px;font-weight:600;">Reporter note</p>'
            + '<p style="margin:0;color:#78350f;font-size:0.9rem;">' + esc(report.description) + '</p>'
            + '</div>';
    }

    if (report.action_taken) {
        html += '<p style="color:#64748b;font-size:0.85rem;"><strong>Action taken:</strong> ' + esc(report.action_taken) + '</p>';
    }

    document.getElementById('reviewModalBody').innerHTML = html;

    var footer = '';
    if (isActionable && !postRemoved) {
        footer += '<button type="button" class="admin-button" style="background:#ef4444;color:#fff;border-color:#ef4444;" onclick="startAction(\'remove_post\')">Remove Post</button>';
    }
    if (isActionable) {
        footer += '<button type="button" class="admin-button admin-button--ghost" onclick="startAction(\'dismiss_report\')">Dismiss Report</button>';
    }
    footer += '<button type="button" class="admin-button admin-button--secondary" onclick="closeReviewModal()">Close</button>';
    document.getElementById('reviewModalFooter').innerHTML = footer;
}

function startAction(action) {
    pendingAction = action;

    if (action === 'remove_post') {
        document.getElementById('actionModalTitle').textContent = 'Remove Post';
        document.getElementById('actionModalDesc').textContent = 'This will hide the post from the community and notify the author. All pending reports for this post will be resolved.';
    } else {
        document.getElementById('actionModalTitle').textContent = 'Dismiss Report';
        document.getElementById('actionModalDesc').textContent = 'The report will be dismissed. No action will be taken on the post.';
    }

    document.getElementById('actionNote').value = '';
    document.getElementById('actionErrorMsg').style.display = 'none';
    document.getElementById('actionModal').style.display = 'block';
}

function closeActionModal() {
    document.getElementById('actionModal').style.display = 'none';
    pendingAction = null;
}

document.getElementById('actionConfirmBtn').addEventListener('click', function () {
    if (!pendingAction || !currentReportId) return;

    var button = this;
    var errorElement = document.getElementById('actionErrorMsg');
    var formData = new FormData();
    formData.append('report_id', currentReportId);
    formData.append('note', document.getElementById('actionNote').value.trim());

    errorElement.style.display = 'none';
    button.disabled = true;
    button.textContent = 'Processing...';

    fetch('/admin/content-management?action=' + pendingAction, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(function (response) { return response.json(); })
    .then(function (data) {
        button.disabled = false;
        button.textContent = 'Confirm';

        if (data.success) {
            closeActionModal();
            closeReviewModal();
            location.reload();
            return;
        }

        errorElement.textContent = data.error || 'Something went wrong.';
        errorElement.style.display = 'block';
    })
    .catch(function () {
        button.disabled = false;
        button.textContent = 'Confirm';
        errorElement.textContent = 'Network error. Please try again.';
        errorElement.style.display = 'block';
    });
});

document.getElementById('reviewModal').addEventListener('click', function (event) {
    if (event.target === this) {
        closeReviewModal();
    }
});

document.getElementById('actionModal').addEventListener('click', function (event) {
    if (event.target === this) {
        closeActionModal();
    }
});

function esc(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function cap(value) {
    value = String(value || '');
    return value ? value.charAt(0).toUpperCase() + value.slice(1) : '';
}

function fmtDate(value) {
    if (!value) return '-';
    var date = new Date(value);
    return isNaN(date) ? value : date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}
</script>

<?php require_once __DIR__ . '/../common/admin.footer.php'; ?>
</body>
</html>
