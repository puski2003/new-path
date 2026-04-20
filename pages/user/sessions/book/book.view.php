<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/../../common/user.html.head.php'; ?>

<body>
    <main class="main-container">
        <?php $activePage = 'sessions';
        require_once __DIR__ . '/../../common/user.sidebar.php'; ?>

        <section class="main-content">
            <img src="/assets/img/main-content-head.svg"
                alt="Main Content Head background"
                class="main-header-bg-image" />

            <div class="main-content-header">
                <div class="main-content-header-text">
                    <h2>Checkout</h2>
                    <p>Review and confirm your booking</p>
                </div>
                <div style="width: 25%"></div>
                <img src="/assets/img/session-confirm.svg"
                    alt="Checkout"
                    class="checkout-image" />
            </div>

            <div class="main-content-body">

                <?php if ($bookingError): ?>
                    <div class="error-message">
                        <?= htmlspecialchars($bookingError) ?>
                        &nbsp;<a class="form-link" href="/user/counselors">Browse counselors</a>
                    </div>

                <?php else: ?>

                    <!-- Back Navigation -->
                    <div class="checkout-header">
                        <a class="back-btn" href="<?= htmlspecialchars($cancelUrl) ?>">
                            <i data-lucide="arrow-left" class="back-icon" stroke-width="1.8"></i>
                            <span>Back to Counselor</span>
                        </a>
                    </div>

                    <div class="checkout-container">

                        <!-- Left Column - Booking Summary -->
                        <div class="checkout-summary">
                            <div class="summary-card">
                                <h3>Booking Summary</h3>

                                <!-- Counselor Info -->
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

                                <!-- Session Details -->
                                <div class="session-details-section">
                                    <h4>Session Details</h4>
                                    <div class="detail-row">
                                        <div class="detail-item">
                                            <i data-lucide="calendar" stroke-width="1.5"></i>
                                            <div>
                                                <span class="detail-label">Date</span>
                                                <span class="detail-value">
                                                    <?= htmlspecialchars(date('D, d M Y', strtotime($slotDatetime ?? ''))) ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <i data-lucide="clock" stroke-width="1.5"></i>
                                            <div>
                                                <span class="detail-label">Time</span>
                                                <span class="detail-value">
                                                    <?= htmlspecialchars(date('g:i A', strtotime($slotDatetime ?? ''))) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="detail-row">
                                        <div class="detail-item">
                                            <i data-lucide="video" stroke-width="1.5"></i>
                                            <div>
                                                <span class="detail-label">Session Type</span>
                                                <span class="detail-value">Video Call (<?= $durationMinutes ?> min)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Slot hold countdown -->
                                <div class="session-timeline-section">
                                    <h4 class="timeline-title">Slot reservation</h4>
                                    <div class="timeline">
                                        <div class="timeline-item">
                                            <div class="timeline-dot"></div>
                                            <div class="timeline-content">
                                                <h4 class="timeline-event">Time remaining to complete payment</h4>
                                                <p class="timeline-time" id="holdTimerDisplay">15:00</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div><!-- /.summary-card -->
                        </div><!-- /.checkout-summary -->

                        <!-- Right Column - Payment Summary -->
                        <div class="checkout-payment">
                            <div class="payment-card">
                                <h3>Payment Summary</h3>

                                <div class="price-breakdown">
                                    <div class="price-row">
                                        <span>Session Fee</span>
                                        <?php if ($freeCredit): ?>
                                            <span style="text-decoration:line-through;color:var(--color-text-muted);">LKR <?= number_format($sessionFee, 2) ?></span>
                                        <?php else: ?>
                                            <span>LKR <?= number_format($sessionFee, 2) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="price-row">
                                        <span>Platform Fee (10%)</span>
                                        <?php if ($freeCredit): ?>
                                            <span style="text-decoration:line-through;color:var(--color-text-muted);">LKR <?= number_format(round($sessionFee * 0.10, 2), 2) ?></span>
                                        <?php else: ?>
                                            <span>LKR <?= number_format($platformFee, 2) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($freeCredit): ?>
                                    <div class="price-row" style="color:var(--color-primary);font-weight:600;">
                                        <span>Reschedule Credit</span>
                                        <span>- LKR <?= number_format($sessionFee + round($sessionFee * 0.10, 2), 2) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="price-divider"></div>
                                    <div class="price-row total">
                                        <span>Total</span>
                                        <span><?= $freeCredit ? 'Free' : 'LKR ' . number_format($amount, 2) ?></span>
                                    </div>
                                </div>

                                <?php if ($freeCredit): ?>
                                <!-- Free rebook — no payment needed -->
                                <div class="free-rebook-notice">
                                    <i data-lucide="circle-check" stroke-width="1.5"></i>
                                    <span>Your counselor approved your reschedule request. This session is free of charge.</span>
                                </div>
                                <form method="post" action="/user/sessions/book/free" id="freeRebookForm">
                                    <input type="hidden" name="hold_id"    value="<?= (int)$holdId ?>">
                                    <input type="hidden" name="credit_id"  value="<?= (int)$freeCredit['requestId'] ?>">
                                    <input type="hidden" name="session_type" value="<?= htmlspecialchars($sessionType) ?>">
                                    <button type="submit" class="btn btn-primary proceed-btn" id="freeRebookBtn">
                                        <i data-lucide="calendar-check" stroke-width="1.5"></i>
                                        Confirm Free Rebook
                                    </button>
                                </form>

                                <?php else: ?>
                                <!-- Pay Button -->
                                <div class="payment-form">
                                    <button type="button" id="payhere-submit"
                                            class="btn btn-primary proceed-btn"
                                            onclick="startPayment()">
                                        <i data-lucide="credit-card" stroke-width="1.5"></i>
                                        Pay LKR <?= number_format($amount, 2) ?> with PayHere
                                    </button>
                                </div>

                                <!-- Secure Payment Notice -->
                                <div class="secure-notice">
                                    <i data-lucide="shield-check" stroke-width="1.5"></i>
                                    <span>Secure payment powered by PayHere</span>
                                </div>
                                <?php endif; ?>

                                <!-- Booking Policies -->
                                <div class="booking-policies">
                                    <h5>Booking Policies</h5>
                                    <ul>
                                        <li><i data-lucide="check" stroke-width="2"></i> Reschedule request required for paid sessions</li>
                                        <li><i data-lucide="check" stroke-width="2"></i> Full refund if counselor doesn't show up</li>
                                    </ul>
                                </div>
                            </div><!-- /.payment-card -->
                        </div><!-- /.checkout-payment -->

                    </div><!-- /.checkout-container -->

                <?php endif; ?>

            </div><!-- /.main-content-body -->
        </section>
    </main>

    <!-- PayHere Sandbox JS SDK -->
    <?php if (!$bookingError): ?>
    <script type="text/javascript" src="https://www.payhere.lk/lib/payhere.js"></script>
    <script>
        (function () {
            var cancelUrl = "<?= addslashes($cancelUrl) ?>";
            var isLeavingViaCancel = false;

            window.history.pushState({ checkoutHold: true }, "", window.location.href);
            window.addEventListener("popstate", function () {
                if (isLeavingViaCancel) {
                    return;
                }

                isLeavingViaCancel = true;
                window.location.replace(cancelUrl);
            });

            var backLink = document.querySelector(".checkout-header .back-btn");
            if (backLink) {
                backLink.addEventListener("click", function () {
                    isLeavingViaCancel = true;
                });
            }
        })();

        // Hold countdown — 15 min from page load
        (function () {
            var remaining = 15 * 60;
            var display   = document.getElementById('holdTimerDisplay');
            var btn       = document.getElementById('payhere-submit');

            var timer = setInterval(function () {
                remaining--;
                if (remaining <= 0) {
                    clearInterval(timer);
                    if (display) display.textContent = '00:00 — reservation expired';
                    if (btn) btn.disabled = true;
                    return;
                }
                var m = String(Math.floor(remaining / 60)).padStart(2, '0');
                var s = String(remaining % 60).padStart(2, '0');
                if (display) display.textContent = m + ':' + s;
            }, 1000);
        })();

        function startPayment() {
            var btn = document.getElementById('payhere-submit');
            if (btn) btn.disabled = true;
            var hash = "<?= htmlspecialchars((string) ($payhereHash ?? ''), ENT_QUOTES) ?>";
            if (!hash) {
                alert('Payment setup failed. Please refresh the page and try again.');
                if (btn) btn.disabled = false;
                return;
            }

            doPayHereCheckout(hash);
        }

        function doPayHereCheckout(hash) {
            var payment = {
                sandbox:     true,
                merchant_id: "<?= htmlspecialchars(BookingModel::PAYHERE_MERCHANT_ID, ENT_QUOTES) ?>",
                return_url:  "<?= addslashes($returnUrl) ?>",
                cancel_url:  "<?= addslashes($cancelUrl) ?>",
                notify_url:  "",

                order_id:    "<?= htmlspecialchars($payhereOrderId, ENT_QUOTES) ?>",
                items:       "Counseling Session – <?= addslashes($counselor['name']) ?>",
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
                var successUrl = "<?= addslashes($returnUrl) ?>"
                    + "&order_id=" + encodeURIComponent(orderId || "<?= addslashes($payhereOrderId) ?>")
                    + "&status_code=2"
                    + "&payhere_amount=<?= rawurlencode($amountFormatted) ?>"
                    + "&payhere_currency=LKR";

                window.location.href = successUrl;
            };
            payhere.onDismissed = function () {
                window.location.href = "<?= addslashes($cancelUrl) ?>";
            };
            payhere.onError = function (error) {
                alert("Payment error: " + error);
                document.getElementById('payhere-submit').disabled = false;
            };

            payhere.startPayment(payment);
        }
    </script>
    <?php endif; ?>

    <script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
</body>
</html>
