<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/../../../common/user.html.head.php'; ?>

<body>
    <main class="main-container">
        <?php $activePage = 'sessions';
        require_once __DIR__ . '/../../../common/user.sidebar.php'; ?>

        <section class="main-content">
            <img src="/assets/img/main-content-head.svg"
                alt="Main Content Head background"
                class="main-header-bg-image" />

            <div class="main-content-header">
                <div class="main-content-header-text">
                    <h2>Booking Confirmed</h2>
                    <p>Your session has been successfully scheduled</p>
                </div>
                <div style="width: 25%"></div>
                <img src="/assets/img/session-confirm.svg"
                    alt="Booking Confirmed"
                    class="checkout-image" />
            </div>

            <div class="main-content-body">

                <!-- Success Hero -->
                <div class="booking-success-hero">
                    <div class="success-checkmark-ring">
                        <div class="success-checkmark-circle">
                            <i data-lucide="check" class="success-check-icon"></i>
                        </div>
                    </div>
                    <h2 class="success-headline">Payment Successful!</h2>
                    <p class="success-subline">Your counseling session has been booked and confirmed.</p>
                </div>

              <div class="booking-success-content">
                  <!-- Booking Details Card -->
                <div class="booking-success-card card">

                    <!-- Counselor Info -->
                    <?php if ($counselorData): ?>
                    <div class="success-counselor-row">
                        <div class="success-counselor-avatar">
                            <img src="<?= htmlspecialchars($counselorData['profilePic'] ?? '/assets/img/avatar.png') ?>"
                                 alt="<?= htmlspecialchars($counselorData['name'] ?? 'Counselor') ?>" />
                        </div>
                        <div class="success-counselor-info">
                            <p class="success-counselor-label"><?= htmlspecialchars($counselorData['title'] ?? 'Counselor') ?></p>
                            <h3 class="success-counselor-name"><?= htmlspecialchars($counselorData['name'] ?? '') ?></h3>
                            <p class="success-counselor-specialty"><?= htmlspecialchars($counselorData['specialty'] ?? '') ?></p>
                        </div>
                    </div>
                    <div class="success-divider"></div>
                    <?php endif; ?>

                    <!-- Session Details Grid -->
                    <?php
                        $rawDt    = $sessionData['sessionDateTime'] ?? 'now';
                        $dt       = new DateTime($rawDt);
                        $dateStr  = $dt->format('F j, Y');
                        $timeStr  = $dt->format('g:i A');
                    ?>
                    <div class="success-details-grid">

                        <div class="success-detail-item">
                            <span class="success-detail-icon"><i data-lucide="calendar" stroke-width="1.8"></i></span>
                            <div>
                                <p class="success-detail-label">Date</p>
                                <p class="success-detail-value"><?= htmlspecialchars($dateStr) ?></p>
                            </div>
                        </div>

                        <div class="success-detail-item">
                            <span class="success-detail-icon"><i data-lucide="clock" stroke-width="1.8"></i></span>
                            <div>
                                <p class="success-detail-label">Time</p>
                                <p class="success-detail-value"><?= htmlspecialchars($timeStr) ?></p>
                            </div>
                        </div>

                        <div class="success-detail-item">
                            <span class="success-detail-icon"><i data-lucide="timer" stroke-width="1.8"></i></span>
                            <div>
                                <p class="success-detail-label">Duration</p>
                                <p class="success-detail-value"><?= $durationMinutes ?> min</p>
                            </div>
                        </div>

                        <div class="success-detail-item">
                            <span class="success-detail-icon"><i data-lucide="video" stroke-width="1.8"></i></span>
                            <div>
                                <p class="success-detail-label">Type</p>
                                <p class="success-detail-value"><?= htmlspecialchars($sessionData['sessionType'] ?? 'Video') ?></p>
                            </div>
                        </div>

                    </div>

                    <!-- Google Meet Link -->
                    <?php if (!empty($sessionData['meetingLink'])): ?>
                    <div class="success-meet-banner">
                        <div class="success-meet-left">
                            <span class="success-meet-icon">
                                <!-- Google Meet logo (inline SVG, no external dependency) -->
                                <svg viewBox="0 0 48 48" width="30" height="30" xmlns="http://www.w3.org/2000/svg">
                                    <path fill="#00832d" d="M27.5 22.5 32 18v12l-4.5-4.5z"/>
                                    <path fill="#0066da" d="M20 14h8a2 2 0 0 1 2 2v4l-4.5 4.5L20 18.5V14z" opacity=".8"/>
                                    <rect width="16" height="20" x="8" y="14" fill="#00ac47" rx="2"/>
                                    <path fill="#00832d" d="M20 34h8l4-4v-6l-4.5 4.5L20 24.5V34z"/>
                                    <path fill="#0066da" d="M20 34h-8a2 2 0 0 1-2-2v-4l6.5-4.5L20 28.5V34z" opacity=".8"/>
                                    <path fill="#2684fc" d="M8 18v-2a2 2 0 0 1 2-2h10v5.5L8 18z" opacity=".5"/>
                                    <path fill="#00ac47" d="M32 28v4l-4 4h-8v-5.5l12-2.5z" opacity=".5"/>
                                </svg>
                            </span>
                            <div>
                                <p class="success-detail-label">Google Meet Link</p>
                                <a href="<?= htmlspecialchars($sessionData['meetingLink']) ?>"
                                   class="success-meet-url"
                                   target="_blank" rel="noopener">
                                    <?= htmlspecialchars($sessionData['meetingLink']) ?>
                                </a>
                            </div>
                        </div>
                        <a href="<?= htmlspecialchars($sessionData['meetingLink']) ?>"
                           class="btn btn-primary success-join-btn"
                           target="_blank" rel="noopener">
                            <i data-lucide="video" stroke-width="1.8"></i>
                            Join Meeting
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="success-meet-pending">
                        <i data-lucide="info" stroke-width="1.8"></i>
                        <span>Your Google Meet link will appear in your session details once it's ready.</span>
                    </div>
                    <?php endif; ?>

                    <div class="success-divider"></div>

                    <!-- Transaction Info -->
                    <?php if ($transaction): ?>
                    <div class="success-transaction-row">
                        <div class="success-tx-item">
                            <p class="success-detail-label">Transaction ID</p>
                            <span class="success-tx-id"><?= htmlspecialchars(strtoupper(substr($transaction['transaction_uuid'] ?? 'N/A', 0, 16))) ?></span>
                        </div>
                        <?php if (!empty($transaction['payhere_payment_id'])): ?>
                        <div class="success-tx-item">
                            <p class="success-detail-label">PayHere Ref</p>
                            <span class="success-tx-value"><?= htmlspecialchars($transaction['payhere_payment_id']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($transaction['amount'])): ?>
                        <div class="success-tx-item">
                            <p class="success-detail-label">Amount Paid</p>
                            <span class="success-tx-value success-amount">LKR <?= number_format((float)$transaction['amount'], 2) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                </div><!-- /.booking-success-card -->

                <!-- Action Buttons -->
                <div class="success-actions">
                    <a href="/user/sessions?id=<?= $sessionId ?>" class="btn btn-primary success-action-btn">
                        <i data-lucide="calendar-check" stroke-width="1.8"></i>
                        View Session
                    </a>
                    <a href="/user/dashboard" class="btn btn-secondary success-action-btn">
                        <i data-lucide="layout-dashboard" stroke-width="1.8"></i>
                        Go to Dashboard
                    </a>
                    <a href="/user/sessions" class="btn btn-bg-light-green success-action-btn">
                        <i data-lucide="list" stroke-width="1.8"></i>
                        All Sessions
                    </a>
                </div>
              </div>

            </div><!-- /.main-content-body -->
        </section>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
