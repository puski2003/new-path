<?php
$pageTitle = 'NewPath - Lifestyle Information';
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
                    <div class="progress-text">Step 2 of 5</div>
                    <div class="progress-track" aria-label="Progress">
                        <div class="progress-fill" style="width: 40%;"></div>
                    </div>
                </div>
            </div>

            <div class="onboarding-form-container">
                <h1 class="onboarding-title">Tell us about your lifestyle</h1>

                <?php if ($error !== null): ?>
                    <div class="error-message" style="color: red; margin-bottom: 15px; padding: 10px; border: 1px solid red; background-color: #ffe6e6;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form class="onboarding-form" id="substanceForm" method="POST" action="/auth/onboarding/step2">
                    <div class="onboarding-form-inner">
                        <div class="left-form">
                            <div class="question-group">
                                <div class="question-item">
                                    <label class="question-label">Do you currently use alcohol or drugs?</label>
                                    <div class="radio-group">
                                        <label class="radio-option">
                                            <input type="radio" name="usesSubstances" value="yes" required>
                                            <span class="radio-text">Yes</span>
                                        </label>
                                        <label class="radio-option">
                                            <input type="radio" name="usesSubstances" value="no">
                                            <span class="radio-text">No</span>
                                        </label>
                                    </div>
                                </div>

                                <div id="substanceDetailsContainer" style="display: none; margin-top: 20px;">
                                    <div class="form-group">
                                        <label for="primarySubstance">Primary substance</label>
                                        <select id="primarySubstance" class="form-input" name="primarySubstance">
                                            <option value="">Select primary substance</option>
                                            <option value="Alcohol">Alcohol</option>
                                            <option value="Marijuana">Marijuana</option>
                                            <option value="Opioids">Opioids/Heroin</option>
                                            <option value="Stimulants">Stimulants (Cocaine/Meth)</option>
                                            <option value="Prescription">Prescription Drugs</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="right-form" id="additionalDetailsContainer" style="display: none;">
                            <div class="form-group">
                                <label for="frequency">How often?</label>
                                <select id="frequency" class="form-input" name="frequency">
                                    <option value="">Select frequency</option>
                                    <option value="Daily">Daily</option>
                                    <option value="Weekly">Weekly</option>
                                    <option value="Monthly">Monthly</option>
                                    <option value="Occasionally">Occasionally</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="lastUsed">When was the last time?</label>
                                <select id="lastUsed" class="form-input" name="lastUsed">
                                    <option value="">Select timeframe</option>
                                    <option value="Today">Today</option>
                                    <option value="Past week">Past week</option>
                                    <option value="Past month">Past month</option>
                                    <option value="More than a month ago">More than a month ago</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="quitAttempts">Previous quit attempts</label>
                                <div class="progress-slider">
                                    <input type="range" id="quitAttempts" name="quitAttempts"
                                        class="slider" min="0" max="10" value="0">
                                    <span class="slider-value" id="quitAttemptsValue">0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Motivation — always visible, not conditional on substance use -->
                    <div class="question-group" style="margin-top: 28px;">
                        <div class="question-item">
                            <label class="question-label">What best describes your situation right now?</label>
                            <div class="radio-group" style="grid-template-columns: repeat(3, 1fr);">
                                <label class="radio-option">
                                    <input type="radio" name="motivation" value="exploring" required
                                        <?= (Request::post('motivation') === 'exploring') ? 'checked' : '' ?>>
                                    <span class="radio-text">Just exploring</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="motivation" value="motivated"
                                        <?= (Request::post('motivation') === 'motivated') ? 'checked' : '' ?>>
                                    <span class="radio-text">Motivated but struggling</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="motivation" value="desperate"
                                        <?= (Request::post('motivation') === 'desperate') ? 'checked' : '' ?>>
                                    <span class="radio-text">It's affecting my daily life</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions multi-button">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='/auth/onboarding/step1'">Back</button>
                        <button type="submit" class="form-submit-btn">Next: Assessment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const substanceRadios = document.querySelectorAll('input[name="usesSubstances"]');
            const detailsContainers = [
                document.getElementById('substanceDetailsContainer'),
                document.getElementById('additionalDetailsContainer')
            ];
            const selectInputs = document.querySelectorAll('select[name="primarySubstance"], select[name="frequency"], select[name="lastUsed"]');

            substanceRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    const showDetails = this.value === 'yes';
                    detailsContainers.forEach(container => {
                        container.style.display = showDetails ? 'block' : 'none';
                        // Add animation effect
                        if (showDetails) {
                            container.style.opacity = '0';
                            container.style.transform = 'translateY(-10px)';
                            setTimeout(() => {
                                container.style.transition = 'all 0.3s ease';
                                container.style.opacity = '1';
                                container.style.transform = 'translateY(0)';
                            }, 10);
                        }
                    });

                    // Mark selects as required or not
                    selectInputs.forEach(select => {
                        if (showDetails) {
                            select.setAttribute('required', 'required');
                        } else {
                            select.removeAttribute('required');
                            // Also clear their values so validation passes when not required
                            select.value = '';
                        }
                    });
                });
            });

            // Slider update
            const slider = document.getElementById('quitAttempts');
            const output = document.getElementById('quitAttemptsValue');

            if (slider && output) {
                slider.oninput = function() {
                    output.innerHTML = this.value + (this.value == 10 ? '+' : '');

                    // Update slider background to mimic fill
                    const percentage = (this.value - this.min) / (this.max - this.min) * 100;
                    this.style.background = `linear-gradient(to right, var(--color-primary) 0%, var(--color-primary) ${percentage}%, var(--color-progress-bg) ${percentage}%, var(--color-progress-bg) 100%)`;
                }
            }
        });
    </script>
</body>

</html>