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

                <?php
                    $usesSubstancesVal = '';
                    $primarySubstanceVal = '';
                    $frequencyVal = '';
                    $lastUsedVal = '';
                    $quitAttemptsVal = 0;

                    if ($userProfile) {
                        $primarySubstanceVal = $userProfile['recovery_type'] ?? '';
                        $frequencyVal = $userProfile['substance_frequency'] ?? '';
                        $lastUsedVal = $userProfile['last_used_timeframe'] ?? '';
                        $quitAttemptsVal = (int) ($userProfile['quit_attempts'] ?? 0);
                        $usesSubstancesVal = ($primarySubstanceVal && $primarySubstanceVal !== 'None') ? 'yes' : 'no';
                    }

                    $postUsesSubstances = Request::post('usesSubstances');
                    if ($postUsesSubstances) {
                        $usesSubstancesVal = $postUsesSubstances;
                    }
                    if (Request::post('primarySubstance')) {
                        $primarySubstanceVal = Request::post('primarySubstance');
                    }
                    if (Request::post('frequency')) {
                        $frequencyVal = Request::post('frequency');
                    }
                    if (Request::post('lastUsed')) {
                        $lastUsedVal = Request::post('lastUsed');
                    }
                    if (Request::post('quitAttempts') !== '') {
                        $quitAttemptsVal = (int) Request::post('quitAttempts');
                    }
                    ?>

                    <form class="onboarding-form" id="substanceForm" method="POST" action="/auth/onboarding/step2">
                    <div class="onboarding-form-inner">
                        <div class="left-form">
                            <div class="question-group">
                                <div class="question-item">
                                    <label class="question-label">Do you currently use alcohol or drugs?</label>
                                    <div class="radio-group">
                                        <label class="radio-option">
                                            <input type="radio" name="usesSubstances" value="yes" required <?= $usesSubstancesVal === 'yes' ? 'checked' : '' ?>>
                                            <span class="radio-text">Yes</span>
                                        </label>
                                        <label class="radio-option">
                                            <input type="radio" name="usesSubstances" value="no" <?= $usesSubstancesVal === 'no' ? 'checked' : '' ?>>
                                            <span class="radio-text">No</span>
                                        </label>
                                    </div>
                                </div>

                                <div id="substanceDetailsContainer" style="display: <?= $usesSubstancesVal === 'yes' ? 'block' : 'none' ?>; margin-top: 20px;">
                                    <div class="form-group">
                                        <label for="primarySubstance">Primary substance</label>
                                        <select id="primarySubstance" class="form-input" name="primarySubstance">
                                            <option value="">Select primary substance</option>
                                            <option value="Alcohol" <?= $primarySubstanceVal === 'Alcohol' ? 'selected' : '' ?>>Alcohol</option>
                                            <option value="Marijuana" <?= $primarySubstanceVal === 'Marijuana' ? 'selected' : '' ?>>Marijuana</option>
                                            <option value="Opioids" <?= $primarySubstanceVal === 'Opioids' ? 'selected' : '' ?>>Opioids/Heroin</option>
                                            <option value="Stimulants" <?= $primarySubstanceVal === 'Stimulants' ? 'selected' : '' ?>>Stimulants (Cocaine/Meth)</option>
                                            <option value="Prescription" <?= $primarySubstanceVal === 'Prescription' ? 'selected' : '' ?>>Prescription Drugs</option>
                                            <option value="Other" <?= $primarySubstanceVal === 'Other' ? 'selected' : '' ?>>Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="right-form" id="additionalDetailsContainer" style="display: <?= $usesSubstancesVal === 'yes' ? 'block' : 'none' ?>;">
                            <div class="form-group">
                                <label for="frequency">How often?</label>
                                <select id="frequency" class="form-input" name="frequency">
                                    <option value="">Select frequency</option>
                                    <option value="Daily" <?= $frequencyVal === 'Daily' ? 'selected' : '' ?>>Daily</option>
                                    <option value="Weekly" <?= $frequencyVal === 'Weekly' ? 'selected' : '' ?>>Weekly</option>
                                    <option value="Monthly" <?= $frequencyVal === 'Monthly' ? 'selected' : '' ?>>Monthly</option>
                                    <option value="Occasionally" <?= $frequencyVal === 'Occasionally' ? 'selected' : '' ?>>Occasionally</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="lastUsed">When was the last time?</label>
                                <select id="lastUsed" class="form-input" name="lastUsed">
                                    <option value="">Select timeframe</option>
                                    <option value="Today" <?= $lastUsedVal === 'Today' ? 'selected' : '' ?>>Today</option>
                                    <option value="Past week" <?= $lastUsedVal === 'Past week' ? 'selected' : '' ?>>Past week</option>
                                    <option value="Past month" <?= $lastUsedVal === 'Past month' ? 'selected' : '' ?>>Past month</option>
                                    <option value="More than a month ago" <?= $lastUsedVal === 'More than a month ago' ? 'selected' : '' ?>>More than a month ago</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="quitAttempts">Previous quit attempts</label>
                                <div class="progress-slider">
                                    <input type="range" id="quitAttempts" name="quitAttempts"
                                        class="slider" min="0" max="10" value="<?= $quitAttemptsVal ?>">
                                    <span class="slider-value" id="quitAttemptsValue"><?= $quitAttemptsVal . ($quitAttemptsVal == 10 ? '+' : '') ?></span>
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

                    <?php
                    $selectedAddictions = [];
                    if ($evaluation && !empty($evaluation['addictions'])) {
                        $selectedAddictions = json_decode($evaluation['addictions'], true) ?? [];
                    }
                    $currentMotivation = Request::post('motivation') ?? ($userProfile['motivation_level'] ?? '');
                    $showAddictionCheckbox = ($currentMotivation !== 'exploring' && $currentMotivation !== '');
                    ?>

                    <div id="addictionChecklist" class="question-group" style="margin-top: 28px; display: <?= $showAddictionCheckbox ? 'block' : 'none' ?>;">
                        <div class="question-item">
                            <label class="question-label">Select all that apply:</label>
                            <div class="checkbox-group" style="display: flex; flex-wrap: wrap; gap: 12px; margin-top: 10px;">
                                <?php foreach ($addictionModules as $mod): ?>
                                    <label class="checkbox-option" style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                        <input type="checkbox" name="addictions[]" value="<?= htmlspecialchars($mod['module_key']) ?>"
                                            <?= in_array($mod['module_key'], $selectedAddictions) ? 'checked' : '' ?>>
                                        <span class="checkbox-text"><?= htmlspecialchars($mod['display_name']) ?></span>
                                    </label>
                                <?php endforeach; ?>
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
            const additionalDetailsContainer = document.getElementById('additionalDetailsContainer');

            // Initialize correct display on page load
            if (additionalDetailsContainer) {
                const usesYes = document.querySelector('input[name="usesSubstances"]:checked');
                if (usesYes && usesYes.value === 'yes') {
                    additionalDetailsContainer.style.display = 'block';
                }
            }
            const selectInputs = document.querySelectorAll('select[name="primarySubstance"], select[name="frequency"], select[name="lastUsed"]');

            substanceRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    const showDetails = this.value === 'yes';
                    detailsContainers.forEach(container => {
                        container.style.display = showDetails ? 'block' : 'none';
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

                    selectInputs.forEach(select => {
                        if (showDetails) {
                            select.setAttribute('required', 'required');
                        } else {
                            select.removeAttribute('required');
                            select.value = '';
                        }
                    });
                });
            });

            // Motivation change - show/hide addiction checklist
            const motivationRadios = document.querySelectorAll('input[name="motivation"]');
            const addictionChecklist = document.getElementById('addictionChecklist');

            motivationRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    const showChecklist = this.value !== 'exploring';
                    if (addictionChecklist) {
                        addictionChecklist.style.display = showChecklist ? 'block' : 'none';
                    }
                });
            });

            // Slider update
            const slider = document.getElementById('quitAttempts');
            const output = document.getElementById('quitAttemptsValue');

            if (slider && output) {
                slider.oninput = function() {
                    output.innerHTML = this.value + (this.value == 10 ? '+' : '');
                    const percentage = (this.value - this.min) / (this.max - this.min) * 100;
                    this.style.background = `linear-gradient(to right, var(--color-primary) 0%, var(--color-primary) ${percentage}%, var(--color-progress-bg) ${percentage}%, var(--color-progress-bg) 100%)`;
                }
            }
        });
    </script>
</body>

</html>