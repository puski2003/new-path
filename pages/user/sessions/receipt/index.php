<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/../sessions.model.php';

$sessionId = filter_var(Request::get('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($sessionId === false) {
    Response::redirect('/user/sessions');
    exit;
}

$sessionData = SessionsModel::getSessionById((int)$user['id'], (int)$sessionId);
if (!$sessionData || !$sessionData['hasPayment']) {
    Response::redirect('/user/sessions?id=' . (int)$sessionId);
    exit;
}

$autoPrint = Request::get('print') === '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7f6;
            color: #111827;
            margin: 0;
            padding: 32px;
        }
        .receipt {
            max-width: 760px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
        }
        .receipt-header,
        .receipt-row {
            display: flex;
            justify-content: space-between;
            gap: 24px;
        }
        .receipt-header {
            align-items: flex-start;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 24px;
            margin-bottom: 24px;
        }
        .receipt-title {
            margin: 0 0 8px;
            font-size: 28px;
        }
        .receipt-subtitle,
        .receipt-label {
            color: #6b7280;
        }
        .receipt-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }
        .receipt-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
        }
        .receipt-value {
            margin-top: 8px;
            font-weight: 600;
        }
        .receipt-total {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #d1d5db;
        }
        .receipt-actions {
            margin-top: 24px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
        .receipt-btn {
            border: 0;
            border-radius: 999px;
            background: #dff5ee;
            color: #111827;
            padding: 12px 20px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .receipt {
                box-shadow: none;
                border-radius: 0;
                max-width: none;
            }
            .receipt-actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="receipt-header">
            <div>
                <h1 class="receipt-title">Session Receipt</h1>
                <div class="receipt-subtitle">New Path counseling session payment receipt</div>
            </div>
            <div>
                <div class="receipt-label">Receipt ID</div>
                <div class="receipt-value"><?= htmlspecialchars($sessionData['transactionUuid'] ?: $sessionData['bookingId']) ?></div>
            </div>
        </div>

        <div class="receipt-grid">
            <div class="receipt-card">
                <div class="receipt-label">Counselor</div>
                <div class="receipt-value"><?= htmlspecialchars($sessionData['doctorName']) ?></div>
            </div>
            <div class="receipt-card">
                <div class="receipt-label">Session Type</div>
                <div class="receipt-value"><?= htmlspecialchars($sessionData['sessionType']) ?></div>
            </div>
            <div class="receipt-card">
                <div class="receipt-label">Booked At</div>
                <div class="receipt-value"><?= htmlspecialchars($sessionData['bookedAt']) ?></div>
            </div>
            <div class="receipt-card">
                <div class="receipt-label">Payment Captured</div>
                <div class="receipt-value"><?= htmlspecialchars($sessionData['paymentCaptured']) ?></div>
            </div>
            <div class="receipt-card">
                <div class="receipt-label">Order ID</div>
                <div class="receipt-value"><?= htmlspecialchars($sessionData['payhereOrderId'] ?: $sessionData['bookingId']) ?></div>
            </div>
            <div class="receipt-card">
                <div class="receipt-label">Payment Method</div>
                <div class="receipt-value"><?= htmlspecialchars($sessionData['cardNumber']) ?><?= !empty($sessionData['cardExpiry']) ? ' · ' . htmlspecialchars($sessionData['cardExpiry']) : '' ?></div>
            </div>
        </div>

        <div class="receipt-total">
            <div class="receipt-row">
                <div class="receipt-label">Status</div>
                <div class="receipt-value"><?= htmlspecialchars($sessionData['paymentStatus']) ?></div>
            </div>
            <div class="receipt-row" style="margin-top: 12px;">
                <div class="receipt-label">Total</div>
                <div class="receipt-value"><?= htmlspecialchars($sessionData['amountFormatted']) ?></div>
            </div>
        </div>

        <div class="receipt-actions">
            <a class="receipt-btn" href="/user/sessions?id=<?= (int)$sessionData['sessionId'] ?>">Back to Session</a>
            <button class="receipt-btn" type="button" onclick="window.print()">Print / Save PDF</button>
        </div>
    </div>

    <?php if ($autoPrint): ?>
    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
    <?php endif; ?>
</body>
</html>
