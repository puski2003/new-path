<?php
/**
 * /user/recovery/checkin — Daily check-in
 * GET  → show form (or "already done today" state)
 * POST → save and redirect
 */
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/../recovery.model.php';

$userId  = (int)$user['id'];
$today   = date('Y-m-d');

/* ── Check if already checked in today ──────────────────────── */
$existingRs = Database::search(
    "SELECT checkin_id, mood_rating, mood_label, energy_level, sleep_quality, stress_level, notes, is_sober
     FROM daily_checkins WHERE user_id = $userId AND checkin_date = '$today' LIMIT 1"
);
$existing = ($existingRs && $existingRs->num_rows > 0) ? $existingRs->fetch_assoc() : null;

/* ── Stats for sidebar header card ──────────────────────────── */
$stats    = RecoveryModel::getProgressStats($userId);
$daysSober = (int)$stats['daysSober'];

/* ── Handle POST ─────────────────────────────────────────────── */
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mood     = (int)($_POST['mood_rating'] ?? 0);
    $energy   = (int)($_POST['energy_level'] ?? 0);
    $sleep    = (int)($_POST['sleep_quality'] ?? 0);
    $stress   = (int)($_POST['stress_level'] ?? 0);
    $isSober  = isset($_POST['is_sober']) ? 1 : 0;
    $notes    = trim($_POST['notes'] ?? '');

    $moodLabels = [1=>'Terrible',2=>'Bad',3=>'Okay',4=>'Good',5=>'Great'];
    $moodLabel  = $moodLabels[$mood] ?? '';

    if ($mood < 1 || $mood > 5) {
        $error = 'Please select a mood rating.';
    } elseif ($energy < 1 || $energy > 5) {
        $error = 'Please select your energy level.';
    } elseif ($sleep < 1 || $sleep > 5) {
        $error = 'Please rate your sleep quality.';
    } elseif ($stress < 1 || $stress > 5) {
        $error = 'Please rate your stress level.';
    } else {
        Database::setUpConnection();
        $safeNotes = Database::$connection->real_escape_string($notes);
        $safeMoodLabel = Database::$connection->real_escape_string($moodLabel);

        if ($existing) {
            Database::iud("UPDATE daily_checkins
                SET mood_rating=$mood, mood_label='$safeMoodLabel',
                    energy_level=$energy, sleep_quality=$sleep,
                    stress_level=$stress, is_sober=$isSober, notes='$safeNotes'
                WHERE checkin_id={$existing['checkin_id']}");
        } else {
            Database::iud("INSERT INTO daily_checkins
                (user_id, checkin_date, mood_rating, mood_label, energy_level, sleep_quality, stress_level, is_sober, notes)
                VALUES ($userId, '$today', $mood, '$safeMoodLabel', $energy, $sleep, $stress, $isSober, '$safeNotes')");
        }
        RecoveryModel::checkAndAwardAchievements($userId);
        Response::redirect('/user/recovery?checkinDone=1');
    }
}

$pageTitle = 'Daily Check-in';
$pageStyle = ['user/recovery', 'user/checkin'];
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
                <h2>Daily Check-in</h2>
                <p><?= date('l, F j, Y') ?></p>
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
            <div class="checkin-container">

                <div class="back-navigation">
                    <a href="/user/recovery" class="back-btn" title="Back">
                        <i data-lucide="chevron-left" style="width:18px;height:18px;"></i>
                    </a>
                </div>

                <?php if ($existing && !$error): ?>
                <div class="checkin-done-banner">
                    <i data-lucide="check-circle-2" style="width:20px;height:20px;color:#15803d;"></i>
                    <span>You've already checked in today. You can update your entry below.</span>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" class="checkin-form">

                    <!-- Sobriety toggle -->
                    <div class="checkin-section">
                        <h4 class="checkin-section-title">
                            <i data-lucide="shield-check" style="width:16px;height:16px;"></i>
                            Sobriety
                        </h4>
                        <label class="sober-toggle">
                            <input type="checkbox" name="is_sober" value="1"
                                   <?= ($existing['is_sober'] ?? 1) ? 'checked' : '' ?>>
                            <span class="sober-toggle-track">
                                <span class="sober-toggle-thumb"></span>
                            </span>
                            <span class="sober-toggle-label">I stayed sober today</span>
                        </label>
                    </div>

                    <!-- Mood rating -->
                    <div class="checkin-section">
                        <h4 class="checkin-section-title">
                            <i data-lucide="smile" style="width:16px;height:16px;"></i>
                            Mood
                        </h4>
                        <div class="rating-row">
                            <?php
                            $moodEmojis = [1=>'😞',2=>'😕',3=>'😐',4=>'😊',5=>'😄'];
                            $moodNames  = [1=>'Terrible',2=>'Bad',3=>'Okay',4=>'Good',5=>'Great'];
                            for ($i = 1; $i <= 5; $i++):
                                $checked = ($existing['mood_rating'] ?? 0) == $i ? 'checked' : '';
                            ?>
                            <label class="rating-option">
                                <input type="radio" name="mood_rating" value="<?= $i ?>" <?= $checked ?> required>
                                <span class="rating-emoji"><?= $moodEmojis[$i] ?></span>
                                <span class="rating-label"><?= $moodNames[$i] ?></span>
                            </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Energy level -->
                    <div class="checkin-section">
                        <h4 class="checkin-section-title">
                            <i data-lucide="zap" style="width:16px;height:16px;"></i>
                            Energy Level
                        </h4>
                        <div class="slider-row">
                            <span class="slider-label-left">Low</span>
                            <input type="range" name="energy_level" min="1" max="5" step="1"
                                   value="<?= $existing['energy_level'] ?? 3 ?>"
                                   class="checkin-slider" id="energySlider"
                                   oninput="document.getElementById('energyVal').textContent=this.value">
                            <span class="slider-label-right">High</span>
                            <span class="slider-value" id="energyVal"><?= $existing['energy_level'] ?? 3 ?></span>
                        </div>
                    </div>

                    <!-- Sleep quality -->
                    <div class="checkin-section">
                        <h4 class="checkin-section-title">
                            <i data-lucide="moon" style="width:16px;height:16px;"></i>
                            Sleep Quality
                        </h4>
                        <div class="slider-row">
                            <span class="slider-label-left">Poor</span>
                            <input type="range" name="sleep_quality" min="1" max="5" step="1"
                                   value="<?= $existing['sleep_quality'] ?? 3 ?>"
                                   class="checkin-slider" id="sleepSlider"
                                   oninput="document.getElementById('sleepVal').textContent=this.value">
                            <span class="slider-label-right">Great</span>
                            <span class="slider-value" id="sleepVal"><?= $existing['sleep_quality'] ?? 3 ?></span>
                        </div>
                    </div>

                    <!-- Stress level -->
                    <div class="checkin-section">
                        <h4 class="checkin-section-title">
                            <i data-lucide="activity" style="width:16px;height:16px;"></i>
                            Stress Level
                        </h4>
                        <div class="slider-row">
                            <span class="slider-label-left">Calm</span>
                            <input type="range" name="stress_level" min="1" max="5" step="1"
                                   value="<?= $existing['stress_level'] ?? 2 ?>"
                                   class="checkin-slider" id="stressSlider"
                                   oninput="document.getElementById('stressVal').textContent=this.value">
                            <span class="slider-label-right">High</span>
                            <span class="slider-value" id="stressVal"><?= $existing['stress_level'] ?? 2 ?></span>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="checkin-section">
                        <h4 class="checkin-section-title">
                            <i data-lucide="file-text" style="width:16px;height:16px;"></i>
                            Notes <span style="font-weight:400;color:var(--color-text-muted);">(optional)</span>
                        </h4>
                        <textarea name="notes" class="checkin-notes" rows="3"
                                  placeholder="How are you feeling? Any thoughts for today…"
                                  maxlength="500"><?= htmlspecialchars($existing['notes'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:var(--spacing-md);">
                        <?= $existing ? 'Update Check-in' : 'Save Check-in' ?>
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
