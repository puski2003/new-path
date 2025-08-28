<?php
/**
 * /user/recovery/journal/write — Write or edit a journal entry
 * GET  → form (new or edit by ?id=X)
 * POST → save and redirect
 */
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../../recovery.model.php';

$userId  = (int)$user['id'];
$entryId = (int)($_GET['id'] ?? $_POST['entry_id'] ?? 0);

/* ── Load categories ─────────────────────────────────────── */
$catsRs = Database::search(
    "SELECT category_id, name, color FROM journal_categories
     WHERE user_id = $userId OR is_default = 1
     ORDER BY is_default DESC, name ASC"
);
$categories = [];
if ($catsRs) {
    while ($row = $catsRs->fetch_assoc()) $categories[] = $row;
}

/* ── Load existing entry for edit ───────────────────────── */
$existing = null;
if ($entryId > 0) {
    $eRs = Database::search(
        "SELECT * FROM journal_entries WHERE entry_id = $entryId AND user_id = $userId LIMIT 1"
    );
    if ($eRs && $eRs->num_rows > 0) $existing = $eRs->fetch_assoc();
}

/* ── Handle POST ─────────────────────────────────────────── */
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title      = trim($_POST['title'] ?? '');
    $content    = trim($_POST['content'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $mood       = trim($_POST['mood'] ?? '');
    $isHighlight = isset($_POST['is_highlight']) ? 1 : 0;

    if (strlen($content) < 1) {
        $error = 'Entry content cannot be empty.';
    } else {
        Database::setUpConnection();
        $conn = Database::$connection;
        $safeTitle    = $conn->real_escape_string($title);
        $safeContent  = $conn->real_escape_string($content);
        $safeMood     = $conn->real_escape_string($mood);
        $catVal       = $categoryId > 0 ? $categoryId : 'NULL';

        $eid = (int)($_POST['entry_id'] ?? 0);
        if ($eid > 0) {
            Database::iud("UPDATE journal_entries
                SET title='$safeTitle', content='$safeContent', category_id=$catVal,
                    mood='$safeMood', is_highlight=$isHighlight
                WHERE entry_id=$eid AND user_id=$userId");
        } else {
            Database::iud("INSERT INTO journal_entries (user_id, title, content, category_id, mood, is_highlight)
                VALUES ($userId, '$safeTitle', '$safeContent', $catVal, '$safeMood', $isHighlight)");
        }
        RecoveryModel::checkAndAwardAchievements($userId);
        Response::redirect('/user/recovery/journal?saved=1');
    }
}

$pageTitle = $existing ? 'Edit Entry' : 'New Journal Entry';
$pageStyle = ['user/recovery', 'user/journal'];
$moods = [
    'Grateful'    => '🙏',
    'Hopeful'     => '🌱',
    'Calm'        => '😌',
    'Proud'       => '💪',
    'Motivated'   => '🔥',
    'Anxious'     => '😰',
    'Sad'         => '😔',
    'Overwhelmed' => '😓',
];
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/../../../common/user.html.head.php'; ?>
<body>
<main class="main-container">
    <?php $activePage = 'recovery'; require_once __DIR__ . '/../../../common/user.sidebar.php'; ?>

    <section class="main-content">
        <img src="/assets/img/main-content-head.svg" alt="" class="main-header-bg-image" />

        <div class="main-content-header">
            <div class="main-content-header-text">
                <h2><?= $existing ? 'Edit Entry' : 'New Entry' ?></h2>
                <p><?= date('l, F j, Y') ?></p>
            </div>
        </div>

        <div class="main-content-body">
            <div class="journal-write-container">

                <div class="journal-toolbar">
                    <a href="/user/recovery/journal" class="back-btn" title="Back">
                        <i data-lucide="chevron-left" style="width:18px;height:18px;"></i>
                    </a>
                </div>

                <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" class="journal-write-form">
                    <input type="hidden" name="entry_id" value="<?= $entryId ?>">

                    <!-- Title -->
                    <div class="journal-write-field">
                        <label class="journal-field-label">
                            <i data-lucide="pen-line" style="width:13px;height:13px;"></i>
                            Title
                        </label>
                        <input type="text" name="title" class="journal-title-input"
                               placeholder="Give your entry a title… (optional)"
                               value="<?= htmlspecialchars($existing['title'] ?? '') ?>"
                               maxlength="255" />
                    </div>

                    <!-- Mood chips -->
                    <div class="journal-write-field">
                        <label class="journal-field-label">
                            <i data-lucide="smile" style="width:13px;height:13px;"></i>
                            How are you feeling?
                        </label>
                        <input type="hidden" name="mood" id="moodInput" value="<?= htmlspecialchars($existing['mood'] ?? '') ?>">
                        <div class="journal-mood-chips">
                            <?php foreach ($moods as $label => $emoji): ?>
                            <button type="button"
                                    class="mood-chip <?= ($existing['mood'] ?? '') === $label ? 'selected' : '' ?>"
                                    data-mood="<?= $label ?>"
                                    title="<?= $label ?>">
                                <span class="mood-chip-emoji"><?= $emoji ?></span>
                                <span class="mood-chip-label"><?= $label ?></span>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Category -->
                    <?php if (!empty($categories)): ?>
                    <div class="journal-write-field">
                        <label class="journal-field-label">
                            <i data-lucide="folder" style="width:13px;height:13px;"></i>
                            Category
                        </label>
                        <select name="category_id" class="journal-select">
                            <option value="">No category</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>"
                                    <?= ($existing['category_id'] ?? 0) == $cat['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <!-- Content -->
                    <div class="journal-write-field">
                        <label class="journal-field-label">
                            <i data-lucide="file-text" style="width:13px;height:13px;"></i>
                            Your thoughts
                        </label>
                        <textarea name="content" class="journal-content-area" rows="12"
                                  placeholder="Write freely — this is your private space…"
                                  required><?= htmlspecialchars($existing['content'] ?? '') ?></textarea>
                    </div>

                    <!-- Highlight toggle -->
                    <label class="journal-highlight-toggle">
                        <input type="checkbox" name="is_highlight" value="1"
                               <?= ($existing['is_highlight'] ?? 0) ? 'checked' : '' ?>>
                        <i data-lucide="star" style="width:15px;height:15px;color:#f59e0b;"></i>
                        Mark as a highlight entry
                    </label>

                    <div class="journal-write-actions">
                        <a href="/user/recovery/journal" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="save" style="width:16px;height:16px;margin-right:4px;"></i>
                            <?= $existing ? 'Update Entry' : 'Save Entry' ?>
                        </button>
                    </div>
                </form>

                <script>
                    lucide.createIcons();
                    const chips = document.querySelectorAll('.mood-chip');
                    const moodInput = document.getElementById('moodInput');
                    chips.forEach(chip => {
                        chip.addEventListener('click', () => {
                            chips.forEach(c => c.classList.remove('selected'));
                            chip.classList.add('selected');
                            moodInput.value = chip.dataset.mood;
                        });
                    });
                </script>
            </div>
        </div>
    </section>
</main>

<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
</body>
</html>
