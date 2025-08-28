<?php
/**
 * /user/recovery/log-urge — Log an urge
 * GET  → show form
 * POST → save and redirect
 */
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/../recovery.model.php';

$userId    = (int)$user['id'];
$stats     = RecoveryModel::getProgressStats($userId);
$daysSober = (int)$stats['daysSober'];

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $intensity  = (int)($_POST['intensity'] ?? 0);
    $category   = trim($_POST['trigger_category'] ?? '');
    $strategy   = trim($_POST['coping_strategy'] ?? '');
    $outcome    = trim($_POST['outcome'] ?? '');
    $notes      = trim($_POST['notes'] ?? '');

    if ($intensity < 1 || $intensity > 10) {
        $error = 'Please select an intensity level (1–10).';
    } elseif (empty($category)) {
        $error = 'Please select a trigger category.';
    } elseif (empty($outcome)) {
        $error = 'Please select an outcome.';
    } else {
        Database::setUpConnection();
        $safeCategory = Database::$connection->real_escape_string($category);
        $safeStrategy = Database::$connection->real_escape_string($strategy);
        $safeOutcome  = Database::$connection->real_escape_string($outcome);
        $safeNotes    = Database::$connection->real_escape_string($notes);

        Database::iud("INSERT INTO urge_logs
            (user_id, intensity, trigger_category, coping_strategy_used, outcome, notes)
            VALUES ($userId, $intensity, '$safeCategory', '$safeStrategy', '$safeOutcome', '$safeNotes')");
        Response::redirect('/user/recovery?urgeDone=1');
    }
}

$categories = ['Stress','Social','Emotional','Boredom','Physical','Environment','Celebration','Other'];
$outcomes   = ['resisted' => 'Resisted', 'relapsed' => 'Relapsed', 'in_progress' => 'Still processing'];

$pageTitle = 'Log an Urge';
$pageStyle = ['user/recovery', 'user/log-urge'];
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

                    <!-- Intensity -->
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

                    <!-- Trigger category -->
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

                    <!-- Coping strategy -->
                    <div class="urge-section">
                        <h4 class="urge-section-title">
                            <i data-lucide="shield" style="width:16px;height:16px;"></i>
                            Coping Strategy Used <span style="font-weight:400;color:var(--color-text-muted);">(optional)</span>
                        </h4>
                        <input type="text" name="coping_strategy" class="urge-input"
                               placeholder="e.g. Deep breathing, called a friend, went for a walk…"
                               maxlength="255" />
                    </div>

                    <!-- Outcome -->
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

                    <!-- Notes -->
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
