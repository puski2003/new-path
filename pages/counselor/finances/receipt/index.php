<?php
require_once __DIR__ . '/../../common/counselor.head.php';

$counselorId = (int) ($user['counselorId'] ?? 0);
$type        = Request::get('type') ?? 'receipt';   // 'receipt' | 'statement'
$autoPrint   = Request::get('print') === '1';

/* ── Single transaction receipt ── */
if ($type === 'receipt') {
    $txId = filter_var(Request::get('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($txId === false) {
        Response::redirect('/counselor/finances');
        exit;
    }

    $rs = Database::search(
        "SELECT t.transaction_id, t.transaction_uuid, t.amount, t.currency,
                t.payment_type, t.status, t.created_at,
                s.session_datetime, s.duration_minutes, s.session_type,
                COALESCE(u.display_name, u.username, u.email) AS client_name,
                u.email AS client_email
         FROM transactions t
         JOIN users u ON u.user_id = t.user_id
         LEFT JOIN sessions s ON s.session_id = t.session_id
         WHERE t.transaction_id = $txId AND t.counselor_id = $counselorId
         LIMIT 1"
    );

    if (!$rs || $rs->num_rows === 0) {
        Response::redirect('/counselor/finances');
        exit;
    }

    $tx = $rs->fetch_assoc();
    $receiptId  = $tx['transaction_uuid'] ?: ('TXN-' . $tx['transaction_id']);
    $amount     = 'LKR ' . number_format((float) $tx['amount'], 2);
    $sessionDate = !empty($tx['session_datetime'])
        ? date('F j, Y \a\t g:i A', strtotime($tx['session_datetime']))
        : '—';
    $paidOn     = !empty($tx['created_at']) ? date('F j, Y', strtotime($tx['created_at'])) : '—';
    $duration   = (int) $tx['duration_minutes'] > 0 ? $tx['duration_minutes'] . ' minutes' : '—';
    $sessionType = !empty($tx['session_type']) ? ucfirst($tx['session_type']) : 'Counseling Session';
    $status     = ucfirst($tx['status'] ?? 'completed');
    $clientName = $tx['client_name'] ?? 'Client';
    $clientEmail= $tx['client_email'] ?? '';
    $docTitle   = 'Payment Receipt – ' . $receiptId;
}

/* ── Earnings statement ── */
if ($type === 'statement') {
    $from = Request::get('from') ?? '';
    $to   = Request::get('to')   ?? '';

    $dateWhere = '';
    if ($from !== '' && strtotime($from)) {
        $safeFrom   = Database::$connection->real_escape_string($from);
        $dateWhere .= " AND t.created_at >= '$safeFrom 00:00:00'";
    }
    if ($to !== '' && strtotime($to)) {
        $safeTo     = Database::$connection->real_escape_string($to);
        $dateWhere .= " AND t.created_at <= '$safeTo 23:59:59'";
    }

    $rs = Database::search(
        "SELECT t.transaction_id, t.transaction_uuid, t.amount, t.currency,
                t.status, t.created_at,
                s.session_datetime, s.duration_minutes,
                COALESCE(u.display_name, u.username, u.email) AS client_name
         FROM transactions t
         JOIN users u ON u.user_id = t.user_id
         LEFT JOIN sessions s ON s.session_id = t.session_id
         WHERE t.counselor_id = $counselorId AND t.status = 'completed' $dateWhere
         ORDER BY t.created_at DESC"
    );

    $rows     = [];
    $total    = 0.0;
    while ($rs && ($row = $rs->fetch_assoc())) {
        $rows[]  = $row;
        $total  += (float) $row['amount'];
    }

    $periodLabel = ($from !== '' || $to !== '')
        ? (($from ?: 'All time') . ' — ' . ($to ?: 'Today'))
        : 'All time';
    $generatedOn = date('F j, Y');
    $counselorDisplayName = $user['name'] ?? 'Counselor';
    $docTitle   = 'Earnings Statement – ' . $periodLabel;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($docTitle) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            background: #f5f7f6;
            color: #111827;
            padding: 32px;
        }

        /* ── Document shell ── */
        .doc {
            max-width: 760px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
        }

        /* ── Header ── */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 24px;
            margin-bottom: 28px;
            gap: 24px;
        }

        .doc-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .doc-brand-name {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
        }

        .doc-brand-tag {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .doc-meta {
            text-align: right;
        }

        .doc-meta-id {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .doc-meta-value {
            font-size: 14px;
            font-weight: 700;
            font-family: monospace;
        }

        /* ── Title ── */
        .doc-title {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .doc-subtitle {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 28px;
        }

        /* ── Info grid ── */
        .doc-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }

        .doc-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px 16px;
        }

        .doc-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .doc-value {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
        }

        /* ── Divider ── */
        .doc-divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 24px 0;
        }

        /* ── Total row ── */
        .doc-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            font-size: 14px;
        }

        .doc-total-row--big {
            font-size: 18px;
            font-weight: 700;
            padding-top: 12px;
        }

        .doc-total-label { color: #6b7280; }

        /* ── Status badge ── */
        .doc-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .doc-badge--completed { background: #dcfce7; color: #16a34a; }
        .doc-badge--pending   { background: #fef9c3; color: #854d0e; }
        .doc-badge--failed    { background: #fee2e2; color: #991b1b; }

        /* ── Statement table ── */
        .stmt-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 24px;
        }

        .stmt-table thead tr {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
        }

        .stmt-table th {
            text-align: left;
            padding: 10px 12px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
        }

        .stmt-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f3f4f6;
            color: #111827;
        }

        .stmt-table tbody tr:last-child td { border-bottom: none; }

        .stmt-amount { font-weight: 600; text-align: right; }

        .stmt-total-row td {
            border-top: 2px solid #e5e7eb;
            font-weight: 700;
            padding-top: 14px;
        }

        /* ── Actions ── */
        .doc-actions {
            margin-top: 28px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .doc-btn {
            border: 0;
            border-radius: 999px;
            background: #dff5ee;
            color: #111827;
            padding: 11px 22px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            font-family: Arial, sans-serif;
        }

        .doc-btn--primary {
            background: #3da8e4;
            color: #ffffff;
        }

        /* ── Footer note ── */
        .doc-footer {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
            font-size: 11px;
            color: #9ca3af;
            text-align: center;
        }

        /* ── Print ── */
        @media print {
            body { background: #fff; padding: 0; }
            .doc {
                box-shadow: none;
                border-radius: 0;
                max-width: none;
                padding: 24px;
            }
            .doc-actions { display: none; }
        }
    </style>
</head>
<body>
<div class="doc">

    <!-- Brand header -->
    <div class="doc-header">
        <div class="doc-brand">
            <div>
                <div class="doc-brand-name">New Path</div>
                <div class="doc-brand-tag">Mental Wellness Platform</div>
            </div>
        </div>
        <div class="doc-meta">
            <?php if ($type === 'receipt'): ?>
                <div class="doc-meta-id">Receipt ID</div>
                <div class="doc-meta-value"><?= htmlspecialchars($receiptId) ?></div>
            <?php else: ?>
                <div class="doc-meta-id">Generated on</div>
                <div class="doc-meta-value"><?= htmlspecialchars($generatedOn) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($type === 'receipt'): ?>

        <!-- ── RECEIPT ── -->
        <div class="doc-title">Payment Receipt</div>
        <div class="doc-subtitle">Session payment confirmation for <?= htmlspecialchars($user['name']) ?></div>

        <div class="doc-grid">
            <div class="doc-card">
                <div class="doc-label">Client</div>
                <div class="doc-value"><?= htmlspecialchars($clientName) ?></div>
                <?php if ($clientEmail): ?>
                    <div style="font-size:12px;color:#6b7280;margin-top:2px;"><?= htmlspecialchars($clientEmail) ?></div>
                <?php endif; ?>
            </div>
            <div class="doc-card">
                <div class="doc-label">Counselor</div>
                <div class="doc-value"><?= htmlspecialchars($user['name']) ?></div>
                <div style="font-size:12px;color:#6b7280;margin-top:2px;"><?= htmlspecialchars($user['title'] ?? '') ?></div>
            </div>
            <div class="doc-card">
                <div class="doc-label">Session Date &amp; Time</div>
                <div class="doc-value"><?= htmlspecialchars($sessionDate) ?></div>
            </div>
            <div class="doc-card">
                <div class="doc-label">Session Type</div>
                <div class="doc-value"><?= htmlspecialchars($sessionType) ?></div>
            </div>
            <div class="doc-card">
                <div class="doc-label">Duration</div>
                <div class="doc-value"><?= htmlspecialchars($duration) ?></div>
            </div>
            <div class="doc-card">
                <div class="doc-label">Paid On</div>
                <div class="doc-value"><?= htmlspecialchars($paidOn) ?></div>
            </div>
        </div>

        <hr class="doc-divider">

        <div class="doc-total-row">
            <span class="doc-total-label">Payment Status</span>
            <span class="doc-badge doc-badge--<?= strtolower($tx['status'] ?? 'completed') ?>"><?= htmlspecialchars($status) ?></span>
        </div>
        <div class="doc-total-row doc-total-row--big">
            <span class="doc-total-label">Total Amount</span>
            <span><?= htmlspecialchars($amount) ?></span>
        </div>

        <div class="doc-actions">
            <a class="doc-btn" href="/counselor/finances">Back to Finances</a>
            <button class="doc-btn doc-btn--primary" type="button" onclick="window.print()">Print / Save PDF</button>
        </div>

    <?php elseif ($type === 'statement'): ?>

        <!-- ── EARNINGS STATEMENT ── -->
        <div class="doc-title">Earnings Statement</div>
        <div class="doc-subtitle">
            <?= htmlspecialchars($counselorDisplayName) ?> &nbsp;·&nbsp; Period: <?= htmlspecialchars($periodLabel) ?>
        </div>

        <div class="doc-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 24px;">
            <div class="doc-card">
                <div class="doc-label">Total Sessions</div>
                <div class="doc-value"><?= count($rows) ?></div>
            </div>
            <div class="doc-card">
                <div class="doc-label">Total Earned</div>
                <div class="doc-value">LKR <?= number_format($total, 2) ?></div>
            </div>
            <div class="doc-card">
                <div class="doc-label">Period</div>
                <div class="doc-value"><?= htmlspecialchars($periodLabel) ?></div>
            </div>
        </div>

        <?php if (!empty($rows)): ?>
            <table class="stmt-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Transaction ID</th>
                        <th>Client</th>
                        <th>Session Date</th>
                        <th class="stmt-amount">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= !empty($row['created_at']) ? date('M j, Y', strtotime($row['created_at'])) : '—' ?></td>
                            <td style="font-family:monospace;font-size:11px;color:#6b7280;">
                                <?= htmlspecialchars($row['transaction_uuid'] ?: ('TXN-' . $row['transaction_id'])) ?>
                            </td>
                            <td><?= htmlspecialchars($row['client_name'] ?? 'Client') ?></td>
                            <td><?= !empty($row['session_datetime']) ? date('M j, Y', strtotime($row['session_datetime'])) : '—' ?></td>
                            <td class="stmt-amount">LKR <?= number_format((float) $row['amount'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="stmt-total-row">
                        <td colspan="4">Total</td>
                        <td class="stmt-amount">LKR <?= number_format($total, 2) ?></td>
                    </tr>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color:#6b7280;text-align:center;padding:32px 0;">No completed payments found for this period.</p>
        <?php endif; ?>

        <div class="doc-actions">
            <a class="doc-btn" href="/counselor/finances">Back to Finances</a>
            <button class="doc-btn doc-btn--primary" type="button" onclick="window.print()">Print / Save PDF</button>
        </div>

    <?php endif; ?>

    <div class="doc-footer">
        This document was generated by New Path on <?= date('F j, Y \a\t g:i A') ?> &nbsp;·&nbsp; For support contact support@newpath.com
    </div>
</div>

<?php if ($autoPrint): ?>
<script>
    window.addEventListener('load', function () { window.print(); });
</script>
<?php endif; ?>
</body>
</html>
