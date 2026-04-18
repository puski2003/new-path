<?php
$pageTitle = 'NewPath - Assessment';
$authCss   = 'onboarding.css';
require_once __DIR__ . '/../../common/auth.head.php';

function getScaleLabels(int $scaleId): array {
    return match($scaleId) {
        1 => ['1' => 'Never/Rarely', '2' => 'Sometimes', '3' => 'Often', '4' => 'Very Often', '5' => 'Always'],
        2 => ['1' => 'Not at all', '2' => 'Slightly', '3' => 'Moderately', '4' => 'Very much', '5' => 'Extremely'],
        3 => ['1' => 'No impact', '2' => 'Minor', '3' => 'Moderate', '4' => 'Significant', '5' => 'Major'],
        default => ['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5'],
    };
}
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

                    <?php
                    $questionNumber = 1;
                    ?>
                    <?php if (!empty($genericQuestions)): ?>
                    <div class="question-group" style="margin-bottom: 30px;">
                        <h3 style="margin-bottom: 20px; color: var(--color-text);">General Assessment</h3>
                        <?php foreach ($genericQuestions as $idx => $q): ?>
                        <div class="question-item" style="margin-bottom: 25px;">
                            <label class="question-label"><span style="font-weight: bold;"><?= $questionNumber ?>. </span><?= htmlspecialchars($q['question_text']) ?></label>
                            <?php
                            $scaleLabels = getScaleLabels((int)$q['scale_id']);
                            ?>
                            <div class="radio-group" style="grid-template-columns: repeat(5, 1fr);">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <label class="radio-option">
                                        <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $i ?>" required>
                                        <span class="radio-text"><?= htmlspecialchars($scaleLabels[(string)$i]) ?></span>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <?php $questionNumber++; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($moduleQuestions)): ?>
                    <?php foreach ($moduleQuestions as $moduleKey => $questions): ?>
                    <?php
                        $moduleLabel = match($moduleKey) {
                            'MOD_GAMING' => 'Gaming Related',
                            'MOD_SOCIAL' => 'Social Media Related',
                            'MOD_PORN' => 'Adult Content Related',
                            'MOD_SHOPPING' => 'Shopping Related',
                            'MOD_GAMBLING' => 'Gambling Related',
                            'MOD_STREAMING' => 'Streaming Related',
                            default => htmlspecialchars($moduleKey),
                        };
                        ?>
                    <div class="question-group" style="margin-bottom: 30px;">
                        <h3 style="margin-bottom: 20px; color: var(--color-text);"><?= $moduleLabel ?> Assessment</h3>
                        <?php foreach ($questions as $q): ?>
                        <div class="question-item" style="margin-bottom: 25px;">
                            <label class="question-label"><span style="font-weight: bold;"><?= $questionNumber ?>. </span><?= htmlspecialchars($q['question_text']) ?></label>
                            <?php
                            $scaleLabels = getScaleLabels((int)$q['scale_id']);
                            ?>
                            <div class="radio-group" style="grid-template-columns: repeat(5, 1fr);">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <label class="radio-option">
                                        <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $i ?>" required>
                                        <span class="radio-text"><?= htmlspecialchars($scaleLabels[(string)$i]) ?></span>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <?php $questionNumber++; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (empty($genericQuestions) && empty($moduleQuestions)): ?>
                    <p style="text-align: center; color: var(--color-text-secondary);">
                        No assessment questions available. You can skip this step.
                    </p>
                    <?php endif; ?>

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
            const skipBtn = document.getElementById('skipBtn');
            if (skipBtn) {
                skipBtn.addEventListener('click', function() {
                    document.getElementById('formAction').value = 'skip';

                    const radios = document.querySelectorAll('input[type="radio"]');
                    radios.forEach(radio => radio.removeAttribute('required'));

                    document.getElementById('severityForm').submit();
                });
            }
        });
    </script>
</body>

</html>