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
                    <h2>My Sessions</h2>
                    <p>Your scheduled guidance, all in one place.</p>
                </div>

                <div style="width: 25%"></div>
                <img src="/assets/img/session-header.svg"
                    alt="Session"
                    class="session-image" />
            </div>

            <div class="main-content-body">
                <?php if (!empty($justBooked)): ?>
                <div class="success-message">
                    <strong>Booking confirmed!</strong>&nbsp;Your session has been scheduled and payment processed successfully.
                </div>
                <?php endif; ?>

                <div class="session-detail-header">
                    <a class="back-btn" href="/user/sessions" aria-label="Back to sessions">
                        <i data-lucide="arrow-left" class="back-icon" stroke-width="1.8"></i>
                    </a>
                </div>

                <div class="session-detail-card">
                    <div class="session-detail-info">
                        <div class="doctor-avatar">
                            <img src="<?= htmlspecialchars($sessionData['profilePicture']) ?>" alt="<?= htmlspecialchars($sessionData['doctorName']) ?>" />
                        </div>
                        <div class="doctor-details">
                            <h2 class="doctor-name"><?= htmlspecialchars($sessionData['doctorName']) ?></h2>
                            <p class="doctor-title"><?= htmlspecialchars($sessionData['doctorTitle']) ?></p>
                            <p class="doctor-specialization"><?= htmlspecialchars($sessionData['specialization']) ?></p>
                        </div>
                        <div class="session-detail-actions">
                            <?php if ($isUpcomingSession): ?>
                                <?php if (!empty($sessionData['meetingLink'])): ?>
                                    <a class="btn btn-primary session-join-btn" href="<?= htmlspecialchars($sessionData['meetingLink']) ?>" target="_blank" rel="noopener">Join session</a>
                                <?php else: ?>
                                    <button class="btn btn-primary session-join-btn" type="button" disabled>Join session</button>
                                <?php endif; ?>
                                <button class="btn btn-secondary" type="button" id="openRescheduleModal">Request Reschedule</button>
                                <?php if ($pendingExtension): ?>
                                <button class="btn btn-primary" type="button" id="openExtendModalBtn">Review Extension Request</button>
                                <?php endif; ?>
                            <?php else: ?>
                                <a class="btn btn-primary" href="/user/counselors?id=<?= (int)$sessionData['counselorId'] ?>">Rebook</a>
                                <?php if ($sessionData['hasReview']): ?>
                                    <button class="btn btn-secondary" type="button" disabled title="You've already reviewed this session">Reviewed ✓</button>
                                <?php elseif ($sessionData['status'] === 'completed'): ?>
                                    <button class="btn btn-secondary" type="button" id="openReviewModal">Leave Review</button>
                                <?php endif; ?>
                                <?php if ($sessionData['hasDispute']): ?>
                                    <button class="btn btn-secondary" type="button" disabled>Counselor Reported</button>
                                <?php else: ?>
                                    <button class="btn btn-secondary" type="button" id="openNoShowModal">Report Counselor Absence</button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="session-info-grid">
                        <div class="session-info-item">
                            <span class="session-info-label">Session type</span>
                            <span class="session-info-value"><?= htmlspecialchars($sessionData['sessionType']) ?></span>
                        </div>
                        <div class="session-info-item">
                            <span class="session-info-label">Location</span>
                            <span class="session-info-value"><?= htmlspecialchars($sessionData['location']) ?></span>
                        </div>
                        <div class="session-info-item">
                            <span class="session-info-label">Booking ID</span>
                            <span class="session-info-value" id="booking-id" style="cursor: pointer;"><?= htmlspecialchars($sessionData['bookingId']) ?> (Copy)</span>
                        </div>
                    </div>

                    <div class="session-timeline-section">
                        <h3 class="timeline-title">Timeline</h3>
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <h4 class="timeline-event">Booked at</h4>
                                    <p class="timeline-time"><?= htmlspecialchars($sessionData['bookedAt']) ?></p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <h4 class="timeline-event">Payment captured</h4>
                                    <p class="timeline-time"><?= htmlspecialchars($sessionData['paymentCaptured']) ?></p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <h4 class="timeline-event">Join window opens</h4>
                                    <p class="timeline-time"><?= htmlspecialchars($sessionData['joinWindow']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="session-notes-section">
                        <h3 class="notes-title">Notes</h3>
                        <p class="notes-content"><?= htmlspecialchars($sessionData['notes']) ?></p>
                    </div>

                    <div class="session-payment-section">
                        <div class="payment-header">
                            <h3 class="payment-title">Payment</h3>
                            <?php if (!empty($sessionData['orderUrl'])): ?>
                                <a class="btn btn-bg-light-green view-order-btn" href="<?= htmlspecialchars($sessionData['orderUrl']) ?>">View order</a>
                            <?php else: ?>
                                <button class="btn btn-bg-light-green view-order-btn" type="button" disabled>View order</button>
                            <?php endif; ?>
                        </div>
                        <div class="payment-details">
                            
                            <?php if (!empty($sessionData['receiptUrl'])): ?>
                                <a class="btn btn-bg-light-green download-receipt-btn" href="<?= htmlspecialchars($sessionData['receiptUrl']) ?>" target="_blank" rel="noopener">Download receipt (PDF)</a>
                            <?php else: ?>
                                <button class="btn btn-bg-light-green download-receipt-btn" type="button" disabled>Download receipt (PDF)</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Reschedule request modal - POST form -->
    <?php if ($isUpcomingSession): ?>
    <div class="session-modal-overlay" id="rescheduleModalOverlay" style="display:none;">
        <div class="session-modal">
            <div class="session-modal-header">
                <h3>Request Reschedule</h3>
                <button type="button" class="session-modal-close" id="closeRescheduleModal">&times;</button>
            </div>
            <div class="session-modal-body">
                <p style="color:var(--color-text-secondary);margin-bottom:var(--spacing-lg);">Your counselor will review your request and either approve or decline it. If approved, you will need to rebook.</p>
                <form method="POST" action="/user/sessions/action/reschedule">
                    <input type="hidden" name="session_id" value="<?= (int)$sessionData['sessionId'] ?>">
                    <div class="form-group">
                        <label for="rescheduleReason">Reason <span class="optional">(optional)</span></label>
                        <textarea class="form-input" id="rescheduleReason" name="reason" rows="3" maxlength="500"
                            placeholder="Let your counselor know why you need to reschedule…"></textarea>
                    </div>
                    <div class="session-modal-actions">
                        <button type="button" class="btn btn-secondary" id="closeRescheduleModal2">Cancel</button>
                        <button type="submit" class="btn btn-primary">Send Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Review modal - POST form -->
    <?php if (!$isUpcomingSession && !$sessionData['hasReview'] && $sessionData['status'] === 'completed'): ?>
    <div class="session-modal-overlay" id="reviewModalOverlay" style="display:none;">
        <div class="session-modal">
            <div class="session-modal-header">
                <h3>Leave a Review</h3>
                <button type="button" class="session-modal-close" id="closeReviewModal">&times;</button>
            </div>
            <div class="session-modal-body">
                <form method="POST" action="/user/sessions/action/review" id="reviewForm">
                    <input type="hidden" name="session_id" value="<?= (int)$sessionData['sessionId'] ?>">
                    <input type="hidden" name="rating" id="selectedRating" value="0">
                    <div class="form-group">
                        <label>Rating</label>
                        <div class="star-rating" id="starRating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <button type="button" class="star-btn" data-value="<?= $i ?>" aria-label="<?= $i ?> star<?= $i > 1 ? 's' : '' ?>">★</button>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reviewText">Review <span class="optional">(optional)</span></label>
                        <textarea class="form-input" id="reviewText" name="review" rows="4" maxlength="1000"
                            placeholder="Share your experience with this counselor…"></textarea>
                    </div>
                    <div class="session-modal-actions">
                        <button type="button" class="btn btn-secondary" id="closeReviewModal2">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- No-show report modal - POST form -->
    <?php if (!$isUpcomingSession && !$sessionData['hasDispute']): ?>
    <div class="session-modal-overlay" id="noShowModalOverlay" style="display:none;">
        <div class="session-modal">
            <div class="session-modal-header">
                <h3>Report Counselor Absence</h3>
                <button type="button" class="session-modal-close" id="closeNoShowModal">&times;</button>
            </div>
            <div class="session-modal-body">
                <p style="color:var(--color-text-secondary);margin-bottom:var(--spacing-lg);">Let us know if your counselor did not show up for this session. Our team will review your report and may issue a refund.</p>
                <form method="POST" action="/user/sessions/action/noshow">
                    <input type="hidden" name="session_id" value="<?= (int)$sessionData['sessionId'] ?>">
                    <div class="form-group">
                        <label for="noShowDescription">Details <span class="optional">(optional)</span></label>
                        <textarea class="form-input" id="noShowDescription" name="description" rows="3" maxlength="1000"
                            placeholder="Briefly describe what happened…"></textarea>
                    </div>
                    <div class="session-modal-actions">
                        <button type="button" class="btn btn-secondary" id="closeNoShowModal2">Cancel</button>
                        <button type="submit" class="btn btn-danger">Submit Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Extension request modal - stays AJAX for payment -->
    <?php if ($pendingExtension): ?>
    <div class="session-modal-overlay" id="extendRequestOverlay" style="display:none;">
        <div class="session-modal" style="max-width:480px;">
            <div class="session-modal-header">
                <h3>Session Extension Request</h3>
                <button type="button" class="session-modal-close" id="closeExtendUserModal">&times;</button>
            </div>
            <div class="session-modal-body">
                <p style="color:var(--color-text-secondary);margin-bottom:var(--spacing-lg);">
                    Your counselor would like to extend your session. Select an option below to continue.
                </p>
                <?php $extOpt = $pendingExtension['options'][0]; ?>
                <form method="POST" action="/user/sessions/view?id=<?= (int)$sessionData['sessionId'] ?>&ajax=accept_extension">
                    <input type="hidden" name="extension_id" value="<?= (int)$pendingExtension['extensionId'] ?>">
                    <input type="hidden" name="duration_minutes" value="<?= (int)$extOpt['duration_minutes'] ?>">
                    <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;border:1px solid var(--color-border);border-radius:var(--radius-md);margin-bottom:var(--spacing-lg);">
                        <span style="flex:1;font-weight:500;"><?= (int)$extOpt['duration_minutes'] ?> minutes</span>
                        <span style="color:var(--color-primary);font-weight:600;">LKR <?= number_format((float)$extOpt['fee'], 2) ?></span>
                    </div>
                    <p style="font-size:var(--font-size-sm);color:var(--color-text-muted);margin-bottom:var(--spacing-md);text-align:center;">
                        Expires at <?= date('H:i', strtotime($pendingExtension['expiresAt'])) ?>
                    </p>
                    <div class="session-modal-actions">
                        <button type="button" class="btn btn-secondary" id="declineExtendBtn">Decline</button>
                        <button type="submit" class="btn btn-primary">Accept &amp; Pay</button>
                    </div>
                </form>
                <form id="declineExtendForm" method="POST" action="/user/sessions/view?id=<?= (int)$sessionData['sessionId'] ?>&ajax=decline_extension" style="display:none;">
                    <input type="hidden" name="extension_id" value="<?= (int)$pendingExtension['extensionId'] ?>">
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="/assets/js/user/sessions/sessions-view-more.js"></script>
    <script>
        if (typeof lucide !== 'undefined') lucide.createIcons();

        document.addEventListener('DOMContentLoaded', function () {
            // Booking ID copy
            var bookingId = document.getElementById('booking-id');
            if (bookingId) {
                bookingId.addEventListener('click', function () {
                    var text = bookingId.textContent.replace(' (Copy)', '').trim();
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(text);
                    }
                });
            }

            // ── Reschedule modal ────────────────────────────────────────
            var rescheduleOverlay = document.getElementById('rescheduleModalOverlay');
            var openRescheduleBtn = document.getElementById('openRescheduleModal');
            var closeRescheduleBtns = [
                document.getElementById('closeRescheduleModal'),
                document.getElementById('closeRescheduleModal2'),
            ];

            if (openRescheduleBtn && rescheduleOverlay) {
                openRescheduleBtn.addEventListener('click', function () {
                    rescheduleOverlay.style.display = 'flex';
                    rescheduleOverlay.offsetHeight;
                    rescheduleOverlay.classList.add('show');
                });
                closeRescheduleBtns.forEach(function (btn) {
                    if (btn) btn.addEventListener('click', closeReschedule);
                });
                rescheduleOverlay.addEventListener('click', function (e) {
                    if (e.target === rescheduleOverlay) closeReschedule();
                });
            }
            function closeReschedule() {
                if (!rescheduleOverlay) return;
                rescheduleOverlay.classList.remove('show');
                setTimeout(function () { rescheduleOverlay.style.display = 'none'; }, 300);
            }

            // ── Review modal ────────────────────────────────────────
            var reviewOverlay  = document.getElementById('reviewModalOverlay');
            var openReviewBtn  = document.getElementById('openReviewModal');
            var closeReviewBtns = [
                document.getElementById('closeReviewModal'),
                document.getElementById('closeReviewModal2'),
            ];
            var autoOpen = <?= $autoOpenReview ? 'true' : 'false' ?>;

            if (reviewOverlay) {
                if (openReviewBtn) {
                    openReviewBtn.addEventListener('click', openReview);
                }
                closeReviewBtns.forEach(function (btn) {
                    if (btn) btn.addEventListener('click', closeReview);
                });
                reviewOverlay.addEventListener('click', function (e) {
                    if (e.target === reviewOverlay) closeReview();
                });
                if (autoOpen) openReview();
            }

            function openReview() {
                reviewOverlay.style.display = 'flex';
                reviewOverlay.offsetHeight;
                reviewOverlay.classList.add('show');
            }
            function closeReview() {
                reviewOverlay.classList.remove('show');
                setTimeout(function () { reviewOverlay.style.display = 'none'; }, 300);
            }

            // Star rating interaction
            var starBtns = document.querySelectorAll('.star-btn');
            var ratingInput = document.getElementById('selectedRating');
            var selectedRating = 0;

            starBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    selectedRating = parseInt(this.getAttribute('data-value'), 10);
                    if (ratingInput) ratingInput.value = selectedRating;
                    starBtns.forEach(function (s) {
                        s.classList.toggle('active', parseInt(s.getAttribute('data-value'), 10) <= selectedRating);
                    });
                });
                btn.addEventListener('mouseenter', function () {
                    var hov = parseInt(this.getAttribute('data-value'), 10);
                    starBtns.forEach(function (s) {
                        s.classList.toggle('hovered', parseInt(s.getAttribute('data-value'), 10) <= hov);
                    });
                });
                btn.addEventListener('mouseleave', function () {
                    starBtns.forEach(function (s) { s.classList.remove('hovered'); });
                });
            });

            // Review form validation on submit
            var reviewForm = document.getElementById('reviewForm');
            if (reviewForm) {
                reviewForm.addEventListener('submit', function (e) {
                    var ratingVal = parseInt(document.getElementById('selectedRating').value, 10);
                    if (ratingVal < 1) {
                        e.preventDefault();
                        alert('Please select a star rating.');
                        return false;
                    }
                });
            }

            // ── No-show modal ────────────────────────────────────────
            var noShowOverlay   = document.getElementById('noShowModalOverlay');
            var openNoShowBtn   = document.getElementById('openNoShowModal');
            var closeNoShowBtns = [
                document.getElementById('closeNoShowModal'),
                document.getElementById('closeNoShowModal2'),
            ];
            var autoOpenNoShow = <?= $autoOpenNoShow ? 'true' : 'false' ?>;

            if (openNoShowBtn && noShowOverlay) {
                openNoShowBtn.addEventListener('click', function () {
                    noShowOverlay.style.display = 'flex';
                    noShowOverlay.offsetHeight;
                    noShowOverlay.classList.add('show');
                });
                closeNoShowBtns.forEach(function (btn) {
                    if (btn) btn.addEventListener('click', closeNoShow);
                });
                noShowOverlay.addEventListener('click', function (e) {
                    if (e.target === noShowOverlay) closeNoShow();
                });
                if (autoOpenNoShow) {
                    openNoShowBtn.click();
                }
            }
            function closeNoShow() {
                if (!noShowOverlay) return;
                noShowOverlay.classList.remove('show');
                setTimeout(function () { noShowOverlay.style.display = 'none'; }, 300);
            }

        });
    </script>

    <?php if ($pendingExtension): ?>
    <script>
    (function () {
        var overlay    = document.getElementById('extendRequestOverlay');
        var openBtn    = document.getElementById('openExtendModalBtn');
        var closeBtn   = document.getElementById('closeExtendUserModal');
        var declineBtn = document.getElementById('declineExtendBtn');
        function open()  { overlay.style.display = 'flex'; overlay.offsetHeight; overlay.classList.add('show'); }
        function close() { overlay.classList.remove('show'); setTimeout(function(){ overlay.style.display='none'; }, 300); }
        if (openBtn)    openBtn.addEventListener('click', open);
        if (closeBtn)   closeBtn.addEventListener('click', close);
        if (overlay)    overlay.addEventListener('click', function(e){ if (e.target === overlay) close(); });
        if (declineBtn) declineBtn.addEventListener('click', function(){ document.getElementById('declineExtendForm').submit(); });
    })();
    </script>
    <?php endif; ?>
</body>

</html>