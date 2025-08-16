<?php
$pageTitle = 'NewPath - Login';
$authCss   = 'login.css';
require_once __DIR__ . '/../../common/auth.head.php';
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
            <h1>Welcome back</h1>
            <p>Log in to your account.</p>
        </div>

        <!-- Error flash -->
        <?php if ($error !== null): ?>
            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Form + Illustration -->
        <div style="display: flex; justify-content: center;">
            <div class="form-container">

                <div class="login-left">
                    <div class="login-form-section">
                        <form class="login-form" id="loginForm" method="POST" action="">

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input
                                    type="email"
                                    class="form-input"
                                    id="email"
                                    name="email"
                                    placeholder="Enter your email"
                                    required
                                    value="<?= htmlspecialchars(Request::post('email') ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label for="password">Password</label>
                                <div class="password-input-container">
                                    <input
                                        type="password"
                                        id="password"
                                        class="form-input"
                                        name="password"
                                        placeholder="Enter your password"
                                        required>
                                    <button type="button" class="password-toggle" id="passwordToggle">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="form-options">
                                <p>Keep me signed in on this device</p>
                                <label class="checkbox-container">
                                    <input type="checkbox" id="keepSignedIn" name="rememberMe">
                                    <span class="checkmark"></span>
                                </label>
                            </div>

                            <button type="submit" class="form-submit-btn">Log in</button>

                            <div class="form-links">
                                <a href="/auth/forgot-password" class="forgot-password form-link">Forgot password?</a>
                                <div class="signup-link form-link">
                                    <span>New here? </span>
                                    <a href="/auth/onboarding/step1" class="create-account form-link">Create account</a>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>

                <div class="login-right">
                    <div class="image-container">
                        <img src="/assets/img/login-illustration.png" alt="Login Illustration" class="login-image">
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script src="/assets/js/auth/login.js" defer></script>
</body>

</html>