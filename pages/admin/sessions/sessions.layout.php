<?php
$pageTitle = 'Sessions';
require_once __DIR__ . '/../common/admin.html.head.php';

$statusColors = [
    'scheduled'   => '#3b82f6',
    'confirmed'   => '#10b981',
    'in_progress' => '#f59e0b',
    'completed'   => '#6b7280',
    'cancelled'   => '#ef4444',
    'no_show'     => '#7c3aed',
];

$disputeStatusColors = [
    'pending'   => '#f59e0b',
    'reviewed'  => '#3b82f6',
    'resolved'  => '#10b981',
    'dismissed' => '#6b7280',
];
?>
<main class="admin-main-container">
    <?php require_once __DIR__ . '/../common/admin.sidebar.php'; ?>
    <section class="admin-main-content">
        <h1>Sessions</h1>

        <!-- Tab switcher -->
        <div style="display:flex;gap:8px;margin-bottom:16px;">
            <a href="/admin/sessions?tab=sessions"
               style="padding:8px 20px;border-radius:8px;text-decoration:none;font-weight:600;font-size:.9rem;
                      background:<?= $activeTab === 'sessions' ? '#4CAF50' : '#f0f0f0' ?>;
                      color:<?= $activeTab === 'sessions' ? '#fff' : '#333' ?>;">
                All Sessions
            </a>
            <a href="/admin/sessions?tab=no_show"
               style="padding:8px 20px;border-radius:8px;text-decoration:none;font-weight:600;font-size:.9rem;
                      background:<?= $activeTab === 'no_show' ? '#7c3aed' : '#f0f0f0' ?>;
                      color:<?= $activeTab === 'no_show' ? '#fff' : '#333' ?>;">
                No-Show Reports
            </a>
        </div>

        <?php if ($activeTab === 'sessions'): ?>
        <!-- ══ TAB: ALL SESSIONS ══ -->
        <div class="admin-sub-container-2">
            <form method="GET" class="admin-sub-container-1" style="justify-content:space-between;align-items:center;">
                <h2>All Sessions (<?= $totalCount ?>)</h2>
                <input type="hidden" name="tab" value="sessions">
                <div class="admin-sub-container-1">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search user, counselor, status">
                    <button class="admin-button admin-button--secondary">Search</button>
                    <?php if ($search !== ''): ?>
                        <a href="/admin/sessions" class="admin-button admin-button--secondary">Clear</a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Table -->
            <table class="admin-table">
                <thead class="admin-table-header">
                    <tr class="admin-table-row">
                        <th class="admin-table-th">#</th>
                        <th class="admin-table-th">Date &amp; Time</th>
                        <th class="admin-table-th">User</th>
                        <th class="admin-table-th">Counselor</th>
                        <th class="admin-table-th">Duration</th>
                        <th class="admin-table-th">Status</th>
                        <th class="admin-table-th">Meet Link</th>
                        <th class="admin-table-th">Meeting Details</th>
                    </tr>
                </thead>
                <tbody class="admin-table-body">
                    <?php if (empty($sessions)): ?>
                        <tr class="admin-table-row">
                            <td class="admin-table-td" colspan="8" style="text-align:center;color:#999;">No sessions found.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($sessions as $i => $s): ?>
                        <tr class="admin-table-row <?= $i % 2 === 0 ? 'admin-table-row--even' : 'admin-table-row--odd' ?>">
                            <td class="admin-table-td"><?= (int)$s['session_id'] ?></td>
                            <td class="admin-table-td"><?= htmlspecialchars(date('M j, Y g:i A', strtotime($s['session_datetime']))) ?></td>
                            <td class="admin-table-td">
                                <?= htmlspecialchars($s['user_name']) ?><br>
                                <small style="color:#999;"><?= htmlspecialchars($s['user_email']) ?></small>
                            </td>
                            <td class="admin-table-td">
                                <?= htmlspecialchars($s['counselor_name']) ?><br>
                                <small style="color:#999;"><?= htmlspecialchars($s['counselor_email']) ?></small>
                            </td>
                            <td class="admin-table-td"><?= (int)$s['duration_minutes'] ?> min</td>
                            <td class="admin-table-td">
                                <span style="
                                    display:inline-block;padding:2px 10px;border-radius:12px;font-size:.8rem;font-weight:600;
                                    background:<?= $statusColors[$s['status']] ?? '#6b7280' ?>22;
                                    color:<?= $statusColors[$s['status']] ?? '#6b7280' ?>;
                                ">
                                    <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $s['status']))) ?>
                                </span>
                            </td>
                            <td class="admin-table-td">
                                <?php if (!empty($s['meeting_link'])): ?>
                                    <a href="<?= htmlspecialchars($s['meeting_link']) ?>" target="_blank" style="color:#4CAF50;font-size:.85rem;">
                                        Join Meeting
                                    </a>
                                <?php else: ?>
                                    <span style="color:#bbb;font-size:.85rem;">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="admin-table-td">
                                <?php if (!empty($s['meet_space_name'])): ?>
                                    <button
                                        class="admin-button admin-button--secondary"
                                        style="font-size:.8rem;padding:4px 10px;"
                                        onclick="loadMeetingDetails(<?= (int)$s['session_id'] ?>)"
                                    >
                                        View Details
                                    </button>
                                <?php else: ?>
                                    <span style="color:#bbb;font-size:.85rem;">No space</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div style="display:flex;gap:8px;margin-top:16px;align-items:center;">
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <a
                            href="?tab=sessions&page=<?= $p ?>&search=<?= urlencode($search) ?>"
                            style="padding:4px 12px;border-radius:6px;text-decoration:none;
                                   background:<?= $p === $page ? '#4CAF50' : '#f0f0f0' ?>;
                                   color:<?= $p === $page ? '#fff' : '#333' ?>;"
                        ><?= $p ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php else: ?>
        <!-- ══ TAB: NO-SHOW REPORTS ══ -->
        <div class="admin-sub-container-2">
            <div class="admin-sub-container-1" style="justify-content:space-between;align-items:center;margin-bottom:4px;">
                <h2>No-Show Reports</h2>
            </div>

            <table class="admin-table" id="noShowTable">
                <thead class="admin-table-header">
                    <tr class="admin-table-row">
                        <th class="admin-table-th">Report #</th>
                        <th class="admin-table-th">Reported</th>
                        <th class="admin-table-th">User</th>
                        <th class="admin-table-th">Counselor</th>
                        <th class="admin-table-th">Session Date</th>
                        <th class="admin-table-th">Amount</th>
                        <th class="admin-table-th">Status</th>
                        <th class="admin-table-th">Meeting Details</th>
                        <th class="admin-table-th">Actions</th>
                    </tr>
                </thead>
                <tbody class="admin-table-body" id="noShowTableBody">
                    <tr><td colspan="9" style="text-align:center;color:#999;padding:24px;">Loading…</td></tr>
                </tbody>
            </table>

            <div id="noShowPagination" style="display:flex;gap:8px;margin-top:16px;align-items:center;"></div>
        </div>
        <?php endif; ?>

    </section>
</main>

<!-- Meeting Details Modal -->
<div id="meetModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;padding:32px;max-width:620px;width:90%;max-height:85vh;overflow-y:auto;position:relative;">
        <button onclick="closeMeetModal()" style="position:absolute;top:12px;right:16px;background:none;border:none;font-size:1.4rem;cursor:pointer;color:#999;">&times;</button>
        <h2 style="margin-top:0;">Meeting Details</h2>
        <div id="meetModalBody">
            <p style="color:#999;">Loading...</p>
        </div>
    </div>
</div>

<!-- No-Show Action Modal -->
<div id="noShowActionModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;padding:32px;max-width:480px;width:90%;position:relative;">
        <button onclick="closeNoShowActionModal()" style="position:absolute;top:12px;right:16px;background:none;border:none;font-size:1.4rem;cursor:pointer;color:#999;">&times;</button>
        <h2 style="margin-top:0;" id="noShowActionTitle">Review Report</h2>
        <p id="noShowActionMeta" style="color:#6b7280;font-size:.9rem;margin-bottom:16px;"></p>
        <div style="margin-bottom:16px;">
            <label style="display:block;margin-bottom:6px;font-weight:600;font-size:.9rem;">Admin note <span style="color:#999;font-weight:400;">(optional)</span></label>
            <textarea id="noShowActionNote" rows="3" maxlength="500"
                style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;font-size:.9rem;resize:vertical;"
                placeholder="Add a note for the user…"></textarea>
        </div>
        <p id="noShowActionError" style="color:#dc2626;font-size:.85rem;display:none;margin-bottom:8px;"></p>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <button onclick="closeNoShowActionModal()" class="admin-button admin-button--secondary">Cancel</button>
            <button id="noShowRejectBtn" class="admin-button" style="background:#ef4444;color:#fff;" onclick="submitNoShowAction('reject')">Reject Report</button>
            <button id="noShowApproveBtn" class="admin-button" style="background:#10b981;color:#fff;" onclick="submitNoShowAction('approve')">Mark Refunded</button>
        </div>
    </div>
</div>

<script>
var currentDisputeId = null;
var noShowCurrentPage = 1;
var currentMeetSessionId = null;

// ── Meeting Details ──────────────────────────────────────────────────────
function refreshMeetDetails() {
    if (currentMeetSessionId) loadMeetingDetails(currentMeetSessionId);
}

function loadMeetingDetails(sessionId) {
    currentMeetSessionId = sessionId;
    var modal = document.getElementById('meetModal');
    var body  = document.getElementById('meetModalBody');
    modal.style.display = 'flex';
    body.innerHTML = '<p style="color:#999;">Loading...</p>';

    fetch('/admin/sessions?action=meeting_details&session_id=' + sessionId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.error) {
            body.innerHTML = '<p style="color:#e53e3e;">' + data.error + '</p>';
            return;
        }
        renderMeetingDetails(body, data);
    })
    .catch(function() {
        body.innerHTML = '<p style="color:#e53e3e;">Failed to load meeting details.</p>';
    });
}

function renderMeetingDetails(el, data) {
    var s    = data.session            || {};
    var sp   = data.space              || {};
    var cfg  = sp.config               || {};
    var recs = data.conferenceRecords  || [];

    var statusMap = {
        OPEN: 'Open (anyone can join)',
        TRUSTED: 'Trusted (org + contacts)',
        RESTRICTED: 'Restricted (invited only)',
    };

    var html = '<div style="display:grid;gap:8px;margin-bottom:20px;">'
        + row('Session', '#' + s.session_id + ' — ' + s.user_name + ' with ' + s.counselor_name)
        + row('Date', s.session_datetime)
        + row('Duration', s.duration_minutes + ' min')
        + row('Status', s.status)
        + '</div>';

    if (!sp || !sp.meetingUri) {
        html += '<p style="color:#999;font-size:.9rem;margin-bottom:16px;">No Google Meet space linked to this session — meeting attendance cannot be verified via the API.</p>';
    } else {
        html += '<div style="display:grid;gap:8px;margin-bottom:20px;">'
            + '<h3 style="margin:0 0 8px;">Meet Space</h3>'
            + row('Link', '<a href="' + sp.meetingUri + '" target="_blank" style="color:#4CAF50;">' + sp.meetingUri + '</a>')
            + row('Space Name', sp.name || '—')
            + row('Access Type', statusMap[cfg.accessType] || cfg.accessType || '—')
            + row('Code', sp.meetingCode || '—')
            + '</div>';
    }

    if (recs.length === 0) {
        if (sp && sp.meetingUri) {
            html += '<div style="background:#fefce8;border:1px solid #fde68a;border-radius:8px;padding:14px 16px;margin-top:8px;">'
                + '<p style="margin:0 0 6px;font-size:.9rem;color:#92400e;font-weight:600;">No conference records found</p>'
                + '<p style="margin:0 0 10px;font-size:.85rem;color:#78350f;">The meeting may be active right now, or Google\'s API is still processing (usually takes 2–5 minutes after joining).</p>'
                + '<button onclick="refreshMeetDetails()" style="background:#f59e0b;color:#fff;border:none;border-radius:6px;padding:6px 16px;font-size:.85rem;font-weight:600;cursor:pointer;">Refresh</button>'
                + '</div>';
        }
    } else {
        html += '<h3 style="margin-bottom:8px;">Conference Records (' + recs.length + ')</h3>'
            + '<div style="display:grid;gap:10px;">';
        recs.forEach(function(r) {
            var started = r.startTime ? new Date(r.startTime).toLocaleString() : '—';
            var ended   = r.endTime   ? new Date(r.endTime).toLocaleString()   : '<strong style="color:#f59e0b;">In progress</strong>';
            html += '<div style="border:1px solid #e5e7eb;border-radius:8px;padding:12px;">'
                + row('Started', started)
                + row('Ended', ended)
                + '</div>';
        });
        html += '</div>';
    }

    el.innerHTML = html;
}

function row(label, value) {
    return '<div style="display:flex;gap:12px;font-size:.9rem;">'
        + '<span style="color:#6b7280;width:110px;flex-shrink:0;">' + label + '</span>'
        + '<span>' + value + '</span>'
        + '</div>';
}

function closeMeetModal() {
    document.getElementById('meetModal').style.display = 'none';
}

document.getElementById('meetModal').addEventListener('click', function(e) {
    if (e.target === this) closeMeetModal();
});

// ── No-Show Reports tab ──────────────────────────────────────────────────
<?php if ($activeTab === 'no_show'): ?>
(function() {
    loadNoShowDisputes(1);
})();

function loadNoShowDisputes(page) {
    noShowCurrentPage = page;
    var tbody = document.getElementById('noShowTableBody');
    tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:#999;padding:24px;">Loading…</td></tr>';

    fetch('/admin/sessions?action=get_no_show_disputes&page=' + page, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) {
            tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:#e53e3e;padding:24px;">Failed to load reports.</td></tr>';
            return;
        }
        renderNoShowTable(data.rows, data.total, data.totalPages, page);
    })
    .catch(function() {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:#e53e3e;padding:24px;">Network error.</td></tr>';
    });
}

var disputeStatusColors = <?= json_encode($disputeStatusColors) ?>;

function renderNoShowTable(rows, total, totalPages, page) {
    var tbody = document.getElementById('noShowTableBody');
    if (!rows || rows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:#999;padding:24px;">No no-show reports found.</td></tr>';
        document.getElementById('noShowPagination').innerHTML = '';
        return;
    }

    var html = '';
    rows.forEach(function(d, i) {
        var statusColor = disputeStatusColors[d.dispute_status] || '#6b7280';
        var sessionDate = d.session_datetime ? new Date(d.session_datetime).toLocaleString() : '—';
        var reportedAt  = d.reported_at ? new Date(d.reported_at).toLocaleString() : '—';
        var amount      = d.amount ? parseFloat(d.amount).toFixed(2) + ' ' + (d.currency || 'LKR') : '—';
        var canAct      = d.dispute_status === 'pending' || d.dispute_status === 'reviewed';

        var meetBtn = '<button class="admin-button admin-button--secondary" style="font-size:.75rem;padding:3px 8px;" onclick="loadMeetingDetails(' + d.session_id + ')">'
            + (d.meet_space_name ? 'View Meet' : 'View Details')
            + '</button>';

        var actionBtn = canAct
            ? '<button class="admin-button" style="font-size:.75rem;padding:3px 10px;background:#7c3aed;color:#fff;" onclick="openNoShowAction(' + d.dispute_id + ', \'' + escHtml(d.user_name) + '\', \'' + escHtml(d.counselor_name) + '\', \'' + sessionDate + '\')">Review</button>'
            : '<span style="color:#6b7280;font-size:.85rem;">' + ucFirst(d.dispute_status) + '</span>';

        var rowClass = i % 2 === 0 ? 'admin-table-row--even' : 'admin-table-row--odd';
        html += '<tr class="admin-table-row ' + rowClass + '">'
            + '<td class="admin-table-td">#' + d.dispute_id + '</td>'
            + '<td class="admin-table-td" style="font-size:.82rem;">' + reportedAt + '</td>'
            + '<td class="admin-table-td">' + escHtml(d.user_name) + '<br><small style="color:#999;">' + escHtml(d.user_email) + '</small></td>'
            + '<td class="admin-table-td">' + escHtml(d.counselor_name) + '<br><small style="color:#999;">' + escHtml(d.counselor_email) + '</small></td>'
            + '<td class="admin-table-td" style="font-size:.82rem;">' + sessionDate + '</td>'
            + '<td class="admin-table-td">' + amount + '</td>'
            + '<td class="admin-table-td"><span style="display:inline-block;padding:2px 10px;border-radius:12px;font-size:.78rem;font-weight:600;background:' + statusColor + '22;color:' + statusColor + ';">' + ucFirst(d.dispute_status) + '</span></td>'
            + '<td class="admin-table-td">' + meetBtn + '</td>'
            + '<td class="admin-table-td">' + actionBtn + '</td>'
            + '</tr>';

        // Description row if present
        if (d.description) {
            html += '<tr class="' + rowClass + '" style="background:#fafafa;">'
                + '<td colspan="9" class="admin-table-td" style="font-size:.82rem;color:#555;padding:6px 16px 10px;font-style:italic;">'
                + '&ldquo;' + escHtml(d.description) + '&rdquo;'
                + (d.admin_note ? ' — <strong>Admin note:</strong> ' + escHtml(d.admin_note) : '')
                + '</td></tr>';
        }
    });
    tbody.innerHTML = html;

    // Pagination
    var pag = document.getElementById('noShowPagination');
    pag.innerHTML = '';
    for (var p = 1; p <= totalPages; p++) {
        var a = document.createElement('a');
        a.href = 'javascript:void(0)';
        a.textContent = p;
        a.style.cssText = 'padding:4px 12px;border-radius:6px;text-decoration:none;background:' + (p === page ? '#7c3aed' : '#f0f0f0') + ';color:' + (p === page ? '#fff' : '#333') + ';cursor:pointer;';
        (function(pg) { a.addEventListener('click', function() { loadNoShowDisputes(pg); }); })(p);
        pag.appendChild(a);
    }
}

function openNoShowAction(disputeId, userName, counselorName, sessionDate) {
    currentDisputeId = disputeId;
    document.getElementById('noShowActionTitle').textContent = 'Review Absence Report #' + disputeId;
    document.getElementById('noShowActionMeta').textContent  = userName + ' reported that ' + counselorName + ' did not attend the session on ' + sessionDate + '.';
    document.getElementById('noShowActionNote').value = '';
    document.getElementById('noShowActionError').style.display = 'none';
    document.getElementById('noShowActionModal').style.display = 'flex';
}

function closeNoShowActionModal() {
    document.getElementById('noShowActionModal').style.display = 'none';
    currentDisputeId = null;
}

document.getElementById('noShowActionModal').addEventListener('click', function(e) {
    if (e.target === this) closeNoShowActionModal();
});

function submitNoShowAction(type) {
    if (!currentDisputeId) return;
    var note    = document.getElementById('noShowActionNote').value.trim();
    var errEl   = document.getElementById('noShowActionError');
    var action  = type === 'approve' ? 'mark_refunded' : 'reject_dispute';

    errEl.style.display = 'none';
    document.getElementById('noShowApproveBtn').disabled = true;
    document.getElementById('noShowRejectBtn').disabled  = true;

    var fd = new FormData();
    fd.append('dispute_id', currentDisputeId);
    fd.append('note', note);

    fetch('/admin/sessions?action=' + action, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd,
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            closeNoShowActionModal();
            loadNoShowDisputes(noShowCurrentPage);
        } else {
            errEl.textContent = data.error || 'Action failed.';
            errEl.style.display = 'block';
            document.getElementById('noShowApproveBtn').disabled = false;
            document.getElementById('noShowRejectBtn').disabled  = false;
        }
    })
    .catch(function() {
        errEl.textContent = 'Network error. Please try again.';
        errEl.style.display = 'block';
        document.getElementById('noShowApproveBtn').disabled = false;
        document.getElementById('noShowRejectBtn').disabled  = false;
    });
}

function escHtml(str) {
    return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function ucFirst(str) {
    str = String(str || '');
    return str.charAt(0).toUpperCase() + str.slice(1);
}
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../common/admin.footer.php'; ?>
</body>
</html>
