<?php
// receives: $data, $user — no DB calls, no logic
$existing = $data['existing'];
$categories = $data['categories'];
$error = $data['error'];
$moods = $data['moods'];
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
                    <input type="hidden" name="entry_id" value="<?= (int)($existing['entry_id'] ?? 0) ?>">

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

                    <div class="journal-write-field">
                        <label class="journal-field-label">
                            <i data-lucide="file-text" style="width:13px;height:13px;"></i>
                            Your thoughts
                        </label>
                        <textarea name="content" class="journal-content-area" rows="12"
                                  placeholder="Write freely — this is your private space…"
                                  required><?= htmlspecialchars($existing['content'] ?? '') ?></textarea>
                    </div>

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