<?php
$pageTitle = 'NewPath - Assessment';
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
                    <div class="progress-text">Step 3 of 5</div>
                    <div class="progress-track" aria-label="Progress">
                        <div class="progress-fill" style="width: 60%;"></div>
                    </div>
                </div>
            </div>

            <div class="onboarding-form-container">
                <h1 class="onboarding-title">Severity Assessment</h1>
                <p style="text-align: center; color: var(--color-text-secondary); margin-bottom: 30px;">
                    Please rate the following out of 5 to help us personalize your plan. You can also skip this for now.
                </p>

                <?php if (isset($error) && $error !== null): ?>
                    <div class="error-message" style="color: red; margin-bottom: 15px; padding: 10px; border: 1px solid red; background-color: #ffe6e6;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form class="onboarding-form" id="severityForm" method="POST" action="/auth/onboarding/step3">
                    <input type="hidden" name="action" id="formAction" value="submit">

                    <div class="question-group">
                        <div class="question-item">
                            <label class="question-label">1. How often do you experience strong urges?</label>
                            <p class="question-text">From 1 (Rarely) to 5 (Constantly)</p>
                            <div class="radio-group" style="grid-template-columns: repeat(5, 1fr);">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <label class="radio-option">
                                        <input type="radio" name="q1" value="<?= $i ?>" required>
                                        <span class="radio-text"><?= $i ?></span>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="question-item">
                            <label class="question-label">2. How much does it affect your work/school?</label>
                            <p class="question-text">From 1 (Not at all) to 5 (Severe disruption)</p>
                            <div class="radio-group" style="grid-template-columns: repeat(5, 1fr);">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <label class="radio-option">
                                        <input type="radio" name="q2" value="<?= $i ?>" required>
                                        <span class="radio-text"><?= $i ?></span>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="question-item">
                            <label class="question-label">3. How much does it impact your relationships?</label>
                            <p class="question-text">From 1 (No impact) to 5 (Major conflicts/isolation)</p>
                            <div class="radio-group" style="grid-template-columns: repeat(5, 1fr);">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <label class="radio-option">
                                        <input type="radio" name="q3" value="<?= $i ?>" required>
                                        <span class="radio-text"><?= $i ?></span>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="question-item">
                            <label class="question-label">4. How is your physical health affected?</label>
                            <p class="question-text">From 1 (Healthy) to 5 (Severe health issues)</p>
                            <div class="radio-group" style="grid-template-columns: repeat(5, 1fr);">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <label class="radio-option">
                                        <input type="radio" name="q4" value="<?= $i ?>" required>
                                        <span class="radio-text"><?= $i ?></span>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="question-item">
                            <label class="question-label">5. How confident are you in your ability to quit right now?</label>
                            <p class="question-text">From 1 (Not confident) to 5 (Very confident)</p>
                            <div class="radio-group" style="grid-template-columns: repeat(5, 1fr);">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <label class="radio-option">
                                        <input type="radio" name="q5" value="<?= $i ?>" required>
                                        <span class="radio-text"><?= $i ?></span>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                    <div style="text-align: center; margin-top: 15px;">
                        <button type="button" class="btn btn-text" id="skipBtn" style="color: var(--color-text-secondary); text-decoration: underline; background: none; border: none; cursor: pointer;">
                            Skip Assessment for Now
                        </button>
                    </div>

                    <div class="form-actions multi-button" style="margin-top: 30px;">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='/auth/onboarding/step2'">Back</button>
                        <button type="submit" class="form-submit-btn">Next: Recovery Options</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('skipBtn').addEventListener('click', function() {
                // Change action and remove required attributes to allow submission
                document.getElementById('formAction').value = 'skip';

                const radios = document.querySelectorAll('input[type="radio"]');
                radios.forEach(radio => radio.removeAttribute('required'));

                document.getElementById('severityForm').submit();
            });
        });
    </script>
</body>

</html>