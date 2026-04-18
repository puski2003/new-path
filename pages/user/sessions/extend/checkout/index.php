<?php

/**
 * Route: /user/sessions/extend/checkout
 *
 * Shows the PayHere payment form for an accepted session extension.
 * Expects: ?extension_id=X
 */

require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../extend.model.php';
require_once __DIR__ . '/../../book/book.model.php';

$extensionId = (int)(Request::get('extension_id') ?? 0);
$userId      = (int)$user['id'];
$pageError   = null;

if ($extensionId <= 0) {
    Response::redirect('/user/sessions');
    exit;
}

$ext = ExtendModel::getAccepted($extensionId, $userId);
if (!$ext) {
    Response::redirect('/user/sessions?error=' . urlencode('Extension request not found or already processed.'));
    exit;
}

// Load counselor details for display
$counselor = BookingModel::getCounselorForBooking($ext['counselorId']);
if (!$counselor) {
    Response::redirect('/user/sessions?error=' . urlencode('Counselor not found.'));
    exit;
}

// Load user details for PayHere form
$userDisplayName = $user['name'] ?? 'User';
$userEmail       = '';
$userPhone       = '';
$userRs = Database::search(
    "SELECT email, phone_number,
            COALESCE(display_name, CONCAT(first_name,' ',last_name), username, 'User') AS display_name
     FROM users WHERE user_id = $userId LIMIT 1"
);
if ($userRs && ($userRow = $userRs->fetch_assoc())) {
    $userDisplayName = $userRow['display_name'];
    $userEmail       = $userRow['email'] ?? '';
    $userPhone       = $userRow['phone_number'] ?? '';
}

// PayHere order details
$orderId         = 'EXT-' . $extensionId;
$fee             = $ext['fee'];
$amountFormatted = number_format($fee, 2, '.', '');
$payhereHash     = BookingModel::generatePayHereHash($orderId, $amountFormatted);

$scheme    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
$returnUrl = $scheme . '://' . $host . '/user/sessions/extend/return?extension_id=' . $extensionId;
$cancelUrl = $scheme . '://' . $host . '/user/sessions?id=' . $ext['sessionId'];

$pageTitle = 'Extend Session — Checkout';
$pageStyle = ['user/sessions'];
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/../../../common/user.html.head.php'; ?>

<body>
    <main class="main-container">
        <?php $activePage = 'sessions';
        require_once __DIR__ . '/../../../common/user.sidebar.php'; ?>

        <section class="main-content">
            <img src="/assets/img/main-content-head.svg" alt="" class="main-header-bg-image" />

            <div class="main-content-header">
                <div class="main-content-header-text">
                    <h2>Extension Checkout</h2>
                    <p>Complete payment to extend your session</p>
                </div>
                <div style="width:25%"></div>
                <img src="/assets/img/session-confirm.svg" alt="Checkout" class="checkout-image" />
            </div>

            <div class="main-content-body">

                <div class="checkout-header">
                    <a class="back-btn" href="<?= htmlspecialchars($cancelUrl) ?>">
                        <i data-lucide="arrow-left" class="back-icon" stroke-width="1.8"></i>
                        <span>Back to Session</span>
                    </a>
                </div>

                <div class="checkout-container">

                    <!-- Summary -->
                    <div class="checkout-summary">
                        <div class="summary-card">
                            <h3>Extension Summary</h3>

                            <div class="counselor-info-section">
                                <div class="counselor-avatar">
                                    <img src="<?= htmlspecialchars($counselor['profilePic']) ?>"
                                         alt="<?= htmlspecialchars($counselor['name']) ?>" />
                                </div>
                                <div class="counselor-details">
                                    <h4><?= htmlspecialchars($counselor['name']) ?></h4>
                                    <p class="counselor-title"><?= htmlspecialchars($counselor['title']) ?></p>
                                    <p class="counselor-specialty"><?= htmlspecialchars($counselor['specialty']) ?></p>
                                </div>
                            </div>

                            <div class="session-details-section">
                                <h4>Extension Details</h4>
                                <div class="detail-row">
                                    <div class="detail-item">
                                        <i data-lucide="clock-arrow-up" stroke-width="1.5"></i>
                                        <div>
                                            <span class="detail-label">Extra Time</span>
                                            <span class="detail-value"><?= (int)$ext['durationMinutes'] ?> minutes</span>
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <i data-lucide="credit-card" stroke-width="1.5"></i>
                                        <div>
                                            <span class="detail-label">Extension Fee</span>
                                            <span class="detail-value">LKR <?= number_format($fee, 2) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment -->
                    <div class="checkout-payment">
                        <div class="payment-card">
                            <h3>Payment Summary</h3>

                            <div class="price-breakdown">
                                <div class="price-row">
                                    <span>Extension Fee (<?= (int)$ext['durationMinutes'] ?> min)</span>
                                    <span>LKR <?= number_format($fee, 2) ?></span>
                                </div>
                                <div class="price-divider"></div>
                                <div class="price-row total">
                                    <span>Total</span>
                                    <span>LKR <?= number_format($fee, 2) ?></span>
                                </div>
                            </div>

                            <div class="payment-form">
                                <button type="button" id="payhere-submit"
                                        class="btn btn-primary proceed-btn"
                                        onclick="startPayment()">
                                    <i data-lucide="credit-card" stroke-width="1.5"></i>
                                    Pay LKR <?= number_format($fee, 2) ?> with PayHere
                                </button>
                            </div>

                            <div class="secure-notice">
                                <i data-lucide="shield-check" stroke-width="1.5"></i>
                                <span>Secure payment powered by PayHere</span>
                            </div>
                        </div>
                    </div>

                </div><!-- /.checkout-container -->

            </div><!-- /.main-content-body -->
        </section>
    </main>

    <script type="text/javascript" src="https://www.payhere.lk/lib/payhere.js"></script>
    <script>
        function startPayment() {
            var btn = document.getElementById('payhere-submit');
            if (btn) btn.disabled = true;
            var hash = "<?= htmlspecialchars((string)($payhereHash ?? ''), ENT_QUOTES) ?>";
            if (!hash) {
                alert('Payment setup failed. Please refresh and try again.');
                if (btn) btn.disabled = false;
                return;
            }

            var payment = {
                sandbox:     true,
                merchant_id: "<?= htmlspecialchars(BookingModel::PAYHERE_MERCHANT_ID, ENT_QUOTES) ?>",
                return_url:  "<?= addslashes($returnUrl) ?>",
                cancel_url:  "<?= addslashes($cancelUrl) ?>",
                notify_url:  "",

                order_id:    "<?= htmlspecialchars($orderId, ENT_QUOTES) ?>",
                items:       "Session Extension – <?= addslashes($counselor['name']) ?> (<?= (int)$ext['durationMinutes'] ?> min)",
                amount:      "<?= $amountFormatted ?>",
                currency:    "LKR",
                hash:        hash,

                first_name:  "<?= addslashes(explode(' ', $userDisplayName)[0] ?? 'User') ?>",
                last_name:   "<?= addslashes(implode(' ', array_slice(explode(' ', $userDisplayName), 1)) ?: '-') ?>",
                email:       "<?= addslashes($userEmail) ?>",
                phone:       "<?= addslashes($userPhone) ?>",
                address:     "",
                city:        "Colombo",
                country:     "Sri Lanka",
            };

            payhere.onCompleted = function (orderId) {
                window.location.href = "<?= addslashes($returnUrl) ?>"
                    + "&order_id=" + encodeURIComponent(orderId || "<?= addslashes($orderId) ?>")
                    + "&status_code=2"
                    + "&payhere_amount=<?= rawurlencode($amountFormatted) ?>"
                    + "&payhere_currency=LKR";
            };
            payhere.onDismissed = function () {
                window.location.href = "<?= addslashes($cancelUrl) ?>";
            };
            payhere.onError = function (error) {
                alert('Payment error: ' + error);
                document.getElementById('payhere-submit').disabled = false;
            };

            payhere.startPayment(payment);
        }
    </script>
    <script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>

    <?php require_once __DIR__ . '/../../../common/user.footer.php'; ?>
</body>
</html>
