<?php
$pageTitle = 'NewPath - Set Your Password';
$authCss   = ['login.css', 'forgot-password.css'];
require_once __DIR__ . '/../../auth/common/auth.head.php';
?>

<body>
    <div class="login-container">

        <header>
            <div class="logo-container" style="padding: 10px;">
                <img src="/assets/img/logo.svg" alt="NewPath Logo" class="logo">
                <span class="logo-text">New<br>Path</span>
            </div>
        </header>

        <div class="login-title">
            <h1>Set your password</h1>
            <p>You're using a temporary password. Please set a new one to continue.</p>
        </div>

        <div class="fp-card-wrapper">
            <div class="fp-card">

                <?php if ($error !== ''): ?>
                    <div class="error-message"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="/counselor/set-password" id="setPasswordForm" novalidate>

                    <div class="form-group">
                        <label for="new_password">New password</label>
                        <div class="password-input-container">
                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                class="form-input"
                                placeholder="At least 8 characters"
                                required
                                minlength="8"
                                autofocus>
                            <button type="button" class="password-toggle" id="toggleNew">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        <div class="field-error" id="newPasswordError" style="color:#f44336;font-size:13px;margin-top:4px;display:none;"></div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm new password</label>
                        <div class="password-input-container">
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-input"
                                placeholder="Repeat your new password"
                                required>
                            <button type="button" class="password-toggle" id="toggleConfirm">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        <div class="field-error" id="confirmError" style="color:#f44336;font-size:13px;margin-top:4px;display:none;"></div>
                    </div>

                    <button type="submit" class="form-submit-btn">Set password &amp; continue</button>

                </form>

            </div>
        </div>

    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        [['toggleNew', 'new_password'], ['toggleConfirm', 'confirm_password']].forEach(function (pair) {
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

        var form     = document.getElementById('setPasswordForm');
        var pw       = document.getElementById('new_password');
        var pwConf   = document.getElementById('confirm_password');
        var pwErr    = document.getElementById('newPasswordError');
        var confErr  = document.getElementById('confirmError');

        function showErr(el, msg) { el.textContent = msg; el.style.display = 'block'; }
        function clearErr(el)     { el.textContent = ''; el.style.display = 'none'; }

        pw.addEventListener('input', function () {
            clearErr(pwErr);
            if (pwConf.value && pwConf.value !== pw.value) showErr(confErr, 'Passwords do not match.');
            else clearErr(confErr);
        });

        pwConf.addEventListener('input', function () {
            if (pw.value && pwConf.value !== pw.value) showErr(confErr, 'Passwords do not match.');
            else clearErr(confErr);
        });

        form.addEventListener('submit', function (e) {
            var valid = true;
            clearErr(pwErr); clearErr(confErr);

            if (!pw.value || pw.value.length < 8) {
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
    });
    </script>
</body>
</html>
