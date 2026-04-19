<?php
$daysSober = $data['daysSober'];
$error = $data['error'];
$categories = ['Stress','Social','Emotional','Boredom','Physical','Environment','Celebration','Other'];
$outcomes = ['resisted' => 'Resisted', 'relapsed' => 'Relapsed', 'in_progress' => 'Still processing'];
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/../../common/user.html.head.php'; ?>
<body>
<main class="main-container">
    <?php $activePage = 'recovery'; require_once __DIR__ . '/../../common/user.sidebar.php'; ?>

    <section class="main-content">
        <img src="/assets/img/main-content-head.svg" alt="" class="main-header-bg-image" />

        <div class="main-content-header">
            <div class="main-content-header-text">
                <h2>Log an Urge</h2>
                <p>Track triggers to build awareness and resilience.</p>
            </div>
            <div class="card-container">
                <div class="card days-sober-card">
                    <div class="days-sober-content">
                        <p>DAYS SOBER</p>
                        <i data-lucide="heart" stroke-width="1" style="color:#335346"></i>
                    </div>
                    <h2><?= $daysSober ?></h2>
                </div>
            </div>
        </div>

        <div class="main-content-body">
            <div class="log-urge-container">

                <div class="back-navigation">
                    <a href="/user/recovery" class="back-btn" title="Back">
                        <i data-lucide="chevron-left" style="width:18px;height:18px;"></i>
                    </a>
                </div>

                <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" class="log-urge-form">

                    <div class="urge-section">
                        <h4 class="urge-section-title">
                            <i data-lucide="thermometer" style="width:16px;height:16px;"></i>
                            Urge Intensity <span class="intensity-display" id="intensityDisplay">5</span>
                        </h4>
                        <p class="urge-section-hint">How strong was the urge? (1 = mild, 10 = overwhelming)</p>
                        <div class="intensity-grid">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                            <label class="intensity-option">
                                <input type="radio" name="intensity" value="<?= $i ?>"
                                       <?= $i === 5 ? 'checked' : '' ?>
                                       onchange="document.getElementById('intensityDisplay').textContent='<?= $i ?>'">
                                <span class="intensity-btn <?= $i <= 3 ? 'low' : ($i <= 6 ? 'mid' : 'high') ?>">
                                    <?= $i ?>
                                </span>
                            </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="urge-section">
                        <h4 class="urge-section-title">
                            <i data-lucide="tag" style="width:16px;height:16px;"></i>
                            Trigger Category
                        </h4>
                        <div class="category-grid">
                            <?php foreach ($categories as $cat): ?>
                            <label class="category-option">
                                <input type="radio" name="trigger_category" value="<?= $cat ?>" required>
                                <span class="category-pill"><?= $cat ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="urge-section">
                        <h4 class="urge-section-title">
                            <i data-lucide="shield" style="width:16px;height:16px;"></i>
                            Coping Strategy Used <span style="font-weight:400;color:var(--color-text-muted);">(optional)</span>
                        </h4>
                        <input type="text" name="coping_strategy" class="urge-input"
                               placeholder="e.g. Deep breathing, called a friend, went for a walk…"
                               maxlength="255" />
                    </div>

                    <div class="urge-section">
                        <h4 class="urge-section-title">
                            <i data-lucide="check-circle" style="width:16px;height:16px;"></i>
                            Outcome
                        </h4>
                        <div class="outcome-row">
                            <?php foreach ($outcomes as $val => $label): ?>
                            <label class="outcome-option">
                                <input type="radio" name="outcome" value="<?= $val ?>" required>
                                <span class="outcome-pill outcome-<?= $val ?>"><?= $label ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="urge-section">
                        <h4 class="urge-section-title">
                            <i data-lucide="file-text" style="width:16px;height:16px;"></i>
                            Notes <span style="font-weight:400;color:var(--color-text-muted);">(optional)</span>
                        </h4>
                        <textarea name="notes" class="urge-textarea" rows="3"
                                  placeholder="What was happening? How did you feel before and after?"
                                  maxlength="500"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:var(--spacing-md);">
                        <i data-lucide="save" style="width:16px;height:16px;margin-right:6px;"></i>
                        Log Urge
                    </button>
                </form>
            </div>
        </div>
    </section>
</main>

<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
</body>
</html>