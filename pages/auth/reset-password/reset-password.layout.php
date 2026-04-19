<?php
$pageTitle = 'NewPath - Reset Password';
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
            <h1>Reset password</h1>
            <p>Choose a new password for your account.</p>
        </div>

        <div class="fp-card-wrapper">
            <div class="fp-card">

                <?php if ($done): ?>

                    <!-- Success state -->
                    <div class="success-message">
                        Your password has been reset successfully.
                    </div>
                    <div class="fp-back-link" style="margin-top: 20px;">
                        <a href="/auth/login" class="form-submit-btn" style="display:block; text-align:center; text-decoration:none;">
                            Back to login
                        </a>
                    </div>

                <?php elseif ($tokenUser === null): ?>

                    <!-- Invalid / expired token -->
                    <div class="error-message">
                        This reset link is invalid or has expired.
                    </div>
                    <div class="fp-back-link">
                        <a href="/auth/forgot-password" class="form-link">Request a new reset link</a>
                    </div>

                <?php else: ?>

                    <!-- Reset form -->
                    <?php if ($error !== null): ?>
                        <div class="error-message"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="/auth/reset-password?token=<?= urlencode($token) ?>" id="resetPasswordForm" novalidate>

                        <div class="form-group">
                            <label for="password">New password</label>
                            <div class="password-input-container">
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-input"
                                    placeholder="At least 8 characters"
                                    required
                                    minlength="8"
                                    autofocus>
                                <button type="button" class="password-toggle" id="passwordToggle">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </button>
                            </div>
                            <div class="field-error" id="passwordError" style="color:#f44336;font-size:13px;margin-top:4px;display:none;"></div>
                        </div>

                        <div class="form-group">
                            <label for="password_confirm">Confirm new password</label>
                            <div class="password-input-container">
                                <input
                                    type="password"
                                    id="password_confirm"
                                    name="password_confirm"
                                    class="form-input"
                                    placeholder="Repeat your new password"
                                    required>
                                <button type="button" class="password-toggle" id="passwordToggle2">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </button>
                            </div>
                            <div class="field-error" id="confirmError" style="color:#f44336;font-size:13px;margin-top:4px;display:none;"></div>
                        </div>

                        <button type="submit" class="form-submit-btn">Set new password</button>

                        <div class="fp-back-link">
                            <a href="/auth/login" class="form-link">&larr; Back to login</a>
                        </div>

                    </form>

                <?php endif; ?>

            </div>
        </div>

    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        [['passwordToggle', 'password'], ['passwordToggle2', 'password_confirm']].forEach(function (pair) {
            var btn   = document.getElementById(pair[0]);
            var input = document.getElementById(pair[1]);
            if (!btn || !input) return;
            btn.addEventListener('click', function () {
                var next = input.type === 'password' ? 'text' : 'password';
                input.type = next;
                var svg = btn.querySelector('svg');
                if (svg) {
                    svg.innerHTML = next === 'text'
                        ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>'
                        : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
                }
            });
        });

        var form    = document.getElementById('resetPasswordForm');
        var pw      = document.getElementById('password');
        var pwConf  = document.getElementById('password_confirm');
        var pwErr   = document.getElementById('passwordError');
        var confErr = document.getElementById('confirmError');

        function showErr(el, msg) { el.textContent = msg; el.style.display = 'block'; }
        function clearErr(el) { el.textContent = ''; el.style.display = 'none'; }

        if (pw) {
            pw.addEventListener('input', function () {
                clearErr(pwErr);
                if (pwConf.value && pwConf.value !== pw.value) {
                    showErr(confErr, 'Passwords do not match.');
                } else {
                    clearErr(confErr);
                }
            });
        }

        if (pwConf) {
            pwConf.addEventListener('input', function () {
                if (pw.value && pwConf.value !== pw.value) {
                    showErr(confErr, 'Passwords do not match.');
                } else {
                    clearErr(confErr);
                }
            });
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                var valid = true;
                clearErr(pwErr);
                clearErr(confErr);

                if (!pw.value) {
                    showErr(pwErr, 'Password is required.');
                    valid = false;
                } else if (pw.value.length < 8) {
                    showErr(pwErr, 'Password must be at least 8 characters.');
                    valid = false;
                }

                if (!pwConf.value) {
                    showErr(confErr, 'Please confirm your password.');
                    valid = false;
                } else if (pw.value !== pwConf.value) {
                    showErr(confErr, 'Passwords do not match.');
                    valid = false;
                }

                if (!valid) e.preventDefault();
            });
        }
    });
    </script>
</body>

</html>
