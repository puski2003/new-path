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

$pageTitle = 'Order Details';
$pageStyle = ['user/dashboard', 'user/sessions'];
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/../../common/user.html.head.php'; ?>
<body>
    <main class="main-container">
        <?php $activePage = 'sessions'; require_once __DIR__ . '/../../common/user.sidebar.php'; ?>
        <section class="main-content">
            <img src="/assets/img/main-content-head.svg" alt="" class="main-header-bg-image" />
            <div class="main-content-header">
                <div class="main-content-header-text">
                    <h2>Order Details</h2>
                    <p>Payment details for this session booking.</p>
                </div>
            </div>

            <div class="main-content-body">
                <div class="session-detail-header">
                    <a class="back-btn" href="/user/sessions?id=<?= (int)$sessionData['sessionId'] ?>" aria-label="Back to session">
                        <i data-lucide="arrow-left" class="back-icon" stroke-width="1.8"></i>
                    </a>
                </div>

                <div class="session-detail-card">
                    <div class="session-info-grid">
                        <div class="session-info-item">
                            <span class="session-info-label">Order ID</span>
                            <span class="session-info-value"><?= htmlspecialchars($sessionData['payhereOrderId'] ?: $sessionData['bookingId']) ?></span>
                        </div>
                        <div class="session-info-item">
                            <span class="session-info-label">Transaction ID</span>
                            <span class="session-info-value"><?= htmlspecialchars($sessionData['transactionUuid'] ?: 'Not available') ?></span>
                        </div>
                        <div class="session-info-item">
                            <span class="session-info-label">Payment ID</span>
                            <span class="session-info-value"><?= htmlspecialchars($sessionData['payherePaymentId'] ?: 'Not available') ?></span>
                        </div>
                        <div class="session-info-item">
                            <span class="session-info-label">Amount</span>
                            <span class="session-info-value"><?= htmlspecialchars($sessionData['amountFormatted']) ?></span>
                        </div>
                        <div class="session-info-item">
                            <span class="session-info-label">Status</span>
                            <span class="session-info-value"><?= htmlspecialchars($sessionData['paymentStatus']) ?></span>
                        </div>
                        <div class="session-info-item">
                            <span class="session-info-label">Method</span>
                            <span class="session-info-value"><?= htmlspecialchars($sessionData['cardNumber']) ?><?= !empty($sessionData['cardExpiry']) ? ' · ' . htmlspecialchars($sessionData['cardExpiry']) : '' ?></span>
                        </div>
                        <div class="session-info-item">
                            <span class="session-info-label">Booked At</span>
                            <span class="session-info-value"><?= htmlspecialchars($sessionData['bookedAt']) ?></span>
                        </div>
                        <div class="session-info-item">
                            <span class="session-info-label">Payment Captured</span>
                            <span class="session-info-value"><?= htmlspecialchars($sessionData['paymentCaptured']) ?></span>
                        </div>
                        <div class="session-info-item">
                            <span class="session-info-label">Counselor</span>
                            <span class="session-info-value"><?= htmlspecialchars($sessionData['doctorName']) ?></span>
                        </div>
                    </div>

                    <div class="session-payment-section">
                        <div class="payment-header">
                            <h3 class="payment-title">Receipt</h3>
                            <a class="btn btn-bg-light-green download-receipt-btn" href="/user/sessions/receipt?id=<?= (int)$sessionData['sessionId'] ?>&print=1" target="_blank" rel="noopener">Download receipt (PDF)</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <?php require_once __DIR__ . '/../../common/user.footer.php'; ?>
</body>
</html>
