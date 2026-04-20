<?php
$pageTitle = 'NewPath - Profile Setup';
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

        <div class="onboarding-content">
            <div class="progress-container">
                <div class="progress-bar">
                    <div class="progress-text">Step 1 of 5</div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width: 20%;"></div>
                    </div>
                </div>
            </div>

            <div class="onboarding-form-container">
                <h1 class="onboarding-title">Profile basics</h1>

                <?php if ($error !== null): ?>
                    <div class="error-message" style="color: red; margin-bottom: 15px; padding: 10px; border: 1px solid red; background-color: #ffe6e6;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form class="onboarding-form" id="profileForm" method="POST" action="/auth/onboarding/step1">
                    <div class="onboarding-form-inner">
                        <div class="left-form">
                            <div class="form-group">
                                <label for="displayName">Display name</label>
                                <input type="text" id="displayName" class="form-input" name="displayName"
                                    placeholder="Enter your display name" required
                                    value="<?= htmlspecialchars(Request::post('displayName') ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label for="age">Age</label>
                                <input type="number" id="age" class="form-input" name="age"
                                    placeholder="Enter your age" min="13" max="120" required
                                    value="<?= htmlspecialchars(Request::post('age') ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <?php $selectedGender = Request::post('gender') ?? ''; ?>
                                <select id="gender" class="form-input" name="gender" required>
                                    <option value="" <?= $selectedGender === '' ? 'selected' : '' ?>>Select your gender</option>
                                    <option value="male" <?= $selectedGender === 'male' ? 'selected' : '' ?>>Male</option>
                                    <option value="female" <?= $selectedGender === 'female' ? 'selected' : '' ?>>Female</option>
                                    <option value="other" <?= $selectedGender === 'other' ? 'selected' : '' ?>>Other</option>
                                    <option value="prefer_not_to_say" <?= $selectedGender === 'prefer_not_to_say' ? 'selected' : '' ?>>Prefer not to say</option>
                                </select>
                            </div>
                        </div>

                        <div class="right-form">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" class="form-input" name="email"
                                    placeholder="Enter your email" required
                                    value="<?= htmlspecialchars(Request::post('email') ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label for="password">Password</label>
                                <div class="password-input-container">
                                    <input type="password" id="password" class="form-input" name="password"
                                        placeholder="Enter your password" minlength="6" required>
                                    <button type="button" class="password-toggle" id="passwordToggle">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="rePassword">Re-Password</label>
                                <div class="password-input-container">
                                    <input type="password" id="rePassword" class="form-input" name="rePassword"
                                        placeholder="Re-enter your password" minlength="6" required>
                                    <button type="button" class="password-toggle" id="rePasswordToggle">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </button>
                                </div>
                                <div id="passwordMatchError" style="color:#f44336;font-size:13px;margin-top:4px;display:none;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="form-submit-btn">Next: Lifestyle Info</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordToggles = document.querySelectorAll('.password-toggle');

            passwordToggles.forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const input = this.previousElementSibling;
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);

                    const svg = this.querySelector('svg');
                    if (type === 'text') {
                        svg.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
                    } else {
                        svg.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
                    }
                });
            });

            const pwInput   = document.getElementById('password');
            const rePwInput = document.getElementById('rePassword');
            const matchErr  = document.getElementById('passwordMatchError');

            function showMatchError(msg) {
                if (matchErr) { matchErr.textContent = msg; matchErr.style.display = 'block'; }
                if (rePwInput) rePwInput.style.borderColor = '#f44336';
            }
            function clearMatchError() {
                if (matchErr) { matchErr.textContent = ''; matchErr.style.display = 'none'; }
                if (rePwInput) rePwInput.style.borderColor = '';
            }

            if (rePwInput) {
                rePwInput.addEventListener('input', function () {
                    if (pwInput.value && rePwInput.value && pwInput.value !== rePwInput.value) {
                        showMatchError('Passwords do not match.');
                    } else {
                        clearMatchError();
                    }
                });
            }

            document.getElementById('profileForm').addEventListener('submit', function(e) {
                const password = pwInput.value;
                const rePassword = rePwInput.value;
                clearMatchError();

                if (password.length < 6) {
                    e.preventDefault();
                    pwInput.style.borderColor = '#f44336';
                    pwInput.focus();
                    return false;
                }

                if (password !== rePassword) {
                    e.preventDefault();
                    showMatchError('Passwords do not match.');
                    return false;
                }

                return true;
            });
        });
    </script>
</body>

</html>