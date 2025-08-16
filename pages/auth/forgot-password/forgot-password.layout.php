<?php
$pageTitle = 'NewPath - Forgot Password';
$authCss   = ['login.css', 'forgot-password.css'];
require_once __DIR__ . '/../common/auth.head.php';
?>

<body>
    <div class="login-container">

        <!-- Logo -->
        <header>
            <div class="logo-container" style="padding: 10px;">
                <img src="/assets/img/logo.svg" alt="NewPath Logo" class="logo">
                <span class="logo-text">New<br>Path</span>
            </div>
        </header>

        <!-- Page title -->
        <div class="login-title">
            <h1>Forgot password?</h1>
            <p>Enter your email and we'll send you a reset link.</p>
        </div>

        <div class="fp-card-wrapper">
            <div class="fp-card">

                <?php if ($error !== null): ?>
                    <div class="error-message"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>

                    <div class="success-message">
                        If that email is registered, a reset link has been sent. Check your inbox.
                    </div>

<div class="fp-back-link">
                        <a href="/auth/login" class="form-link">&larr; Back to login</a>
                    </div>

                <?php else: ?>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="email">Email address</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-input"
                                placeholder="Enter your registered email"
                                value="<?= htmlspecialchars(Request::post('email') ?? '') ?>"
                                required
                                autofocus>
                        </div>

                        <button type="submit" class="form-submit-btn">Send reset link</button>

                        <div class="fp-back-link">
                            <a href="/auth/login" class="form-link">&larr; Back to login</a>
                        </div>
                    </form>

                <?php endif; ?>

            </div>
        </div>

    </div>
</body>

</html>
