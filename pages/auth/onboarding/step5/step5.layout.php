<?php
$pageTitle = 'NewPath - Welcome Aboard!';
$authCss   = 'onboarding.css';
require_once __DIR__ . '/../../common/auth.head.php';
?>

<body class="theme-user">
    <div class="onboarding-container">
        <header>
            <div class="logo-container" style="padding: 10px;">
                <img src="/assets/img/logo.svg" alt="NewPath Logo" class="logo">
                <span class="logo-text">New<br>Path</span>
            </div>
        </header>

        <div class="onboarding-content" style="justify-content: center;">
            <div class="completion-container" style="max-width: 600px; margin: 0 auto; width: 100%;">
                <div class="success-icon">🎉</div>

                <h1 class="completion-title">Welcome to NewPath!</h1>
                <p class="completion-subtitle">Your recovery journey starts now. We've set up everything based on your profile.</p>

                <?php if ($activePlan): ?>
                    <div class="plan-summary" id="planSummary">
                        <h3>Your Recovery Plan Summary</h3>

                        <div class="plan-details">
                            <div class="plan-detail-item">
                                <span class="plan-detail-label">Plan Type</span>
                                <span class="plan-detail-value">
                                    <?= htmlspecialchars($activePlan['title']) ?>
                                </span>
                            </div>

                            <div class="plan-detail-item">
                                <span class="plan-detail-label">Start Date</span>
                                <span class="plan-detail-value">
                                    <?= htmlspecialchars($activePlan['start_date']) ?>
                                </span>
                            </div>

                            <div class="plan-detail-item">
                                <span class="plan-detail-label">Initial Assessment</span>
                                <span class="plan-detail-value" style="color: var(--color-success);">
                                    ✓ Completed
                                </span>
                            </div>
                        </div>

                        <div class="next-steps">
                            <h4>What happens next?</h4>
                            <ul class="next-steps-list">
                                <?php if ($activePlan['plan_type'] === 'counselor'): ?>
                                    <li>We will pair you with a suitable counselor shortly.</li>
                                    <li>Keep an eye on your messages to schedule your first session.</li>
                                <?php else: ?>
                                    <li>Explore the dashboard and start your first daily check-in.</li>
                                    <li>Browse the recovery tasks tailored to you.</li>
                                <?php endif; ?>
                                <li>Join the community forums and introduce yourself.</li>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="plan-summary" id="planSummary">
                        <h3>Your Recovery Profile is Ready!</h3>
                        <p>We're finishing up your custom plan configuration. You can proceed to your dashboard now.</p>
                    </div>
                <?php endif; ?>

                <div class="dashboard-actions">
                    <button class="primary-action" onclick="window.location.href='/user/dashboard'">
                        Enter Your Dashboard
                    </button>

                    <div class="secondary-actions" style="margin-top: 20px;">
                        <button class="btn btn-secondary" onclick="window.print()">
                            Download Plan Summary
                        </button>
                        <button class="btn btn-text" onclick="window.location.href='/auth/onboarding/step1'" style="text-decoration: underline;">
                            Review Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>