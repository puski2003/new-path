<?php
/**
 * /user/recovery/journal — Journal entries list
 */
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/../../recovery/recovery.model.php';

$userId    = (int)$user['id'];
$stats     = RecoveryModel::getProgressStats($userId);
$daysSober = (int)$stats['daysSober'];

$page    = max(1, (int)($_GET['page'] ?? 1));
$limit   = 10;
$offset  = ($page - 1) * $limit;

$countRs = Database::search("SELECT COUNT(*) AS total FROM journal_entries WHERE user_id = $userId");
$total   = (int)($countRs->fetch_assoc()['total'] ?? 0);
$totalPages = max(1, (int)ceil($total / $limit));

$entriesRs = Database::search("
    SELECT je.entry_id, je.title, je.content, je.mood, je.is_highlight, je.created_at,
           jc.name AS category_name, jc.color AS category_color
    FROM journal_entries je
    LEFT JOIN journal_categories jc ON jc.category_id = je.category_id
    WHERE je.user_id = $userId
    ORDER BY je.created_at DESC
    LIMIT $limit OFFSET $offset
");
$entries = [];
if ($entriesRs) {
    while ($row = $entriesRs->fetch_assoc()) {
        $entries[] = $row;
    }
}

$pageTitle = 'Journal';
$pageStyle = ['user/recovery', 'user/journal'];
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
                <h2>Journal</h2>
                <p>Reflect on your recovery journey.</p>
            </div>
            <div class="card-container">
                <div class="card days-sober-card">
                    <div class="days-sober-content">
                        <p>ENTRIES</p>
                        <i data-lucide="book-open" stroke-width="1" style="color:#335346"></i>
                    </div>
                    <h2><?= $total ?></h2>
                </div>
            </div>
        </div>

        <div class="main-content-body">
            <div class="journal-container">

                <div class="journal-toolbar">
                    <a href="/user/recovery" class="back-btn" title="Back">
                        <i data-lucide="chevron-left" style="width:18px;height:18px;"></i>
                    </a>
                    <a href="/user/recovery/journal/write" class="btn btn-primary">
                        <i data-lucide="plus" style="width:16px;height:16px;margin-right:4px;"></i>
                        New Entry
                    </a>
                </div>

                <?php if (isset($_GET['saved'])): ?>
                <div class="success-message">Journal entry saved.</div>
                <?php endif; ?>

                <?php if (empty($entries)): ?>
                <div class="journal-empty">
                    <i data-lucide="book" style="width:48px;height:48px;color:var(--color-text-muted);display:block;margin:0 auto var(--spacing-md);"></i>
                    <p>No journal entries yet.</p>
                    <a href="/user/recovery/journal/write" class="btn btn-primary" style="display:inline-flex;margin-top:var(--spacing-md);">
                        Write your first entry
                    </a>
                </div>
                <?php else: ?>
                <div class="journal-list">
                    <?php foreach ($entries as $entry):
                        $preview = mb_strimwidth(strip_tags($entry['content']), 0, 120, '…');
                        $dateStr = date('M j, Y', strtotime($entry['created_at']));
                        $catColor = $entry['category_color'] ?? 'var(--color-primary)';
                    ?>
                    <div class="journal-card <?= $entry['is_highlight'] ? 'highlighted' : '' ?>">
                        <div class="journal-card-meta">
                            <?php if ($entry['category_name']): ?>
                            <span class="journal-category-tag">
                                <?= htmlspecialchars($entry['category_name']) ?>
                            </span>
                            <?php endif; ?>
                            <?php if ($entry['mood']): ?>
                            <span class="journal-mood-tag"><?= htmlspecialchars($entry['mood']) ?></span>
                            <?php endif; ?>
                            <?php if ($entry['is_highlight']): ?>
                            <span class="journal-highlight-tag">
                                <i data-lucide="star" style="width:11px;height:11px;"></i> Highlight
                            </span>
                            <?php endif; ?>
                            <span class="journal-date"><?= $dateStr ?></span>
                        </div>
                        <?php if ($entry['title']): ?>
                        <h4 class="journal-card-title"><?= htmlspecialchars($entry['title']) ?></h4>
                        <?php endif; ?>
                        <p class="journal-card-preview"><?= htmlspecialchars($preview) ?></p>
                        <a href="/user/recovery/journal/write?id=<?= $entry['entry_id'] ?>"
                           class="journal-read-link">
                            Read &amp; Edit
                            <i data-lucide="arrow-right" style="width:12px;height:12px;"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="pagination-btn">
                            <i data-lucide="arrow-left" stroke-width="1"></i>
                        </a>
                    <?php else: ?>
                        <button class="pagination-btn" disabled>
                            <i data-lucide="arrow-left" stroke-width="1"></i>
                        </button>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <button class="pagination-btn active"><?= $i ?></button>
                        <?php else: ?>
                            <a href="?page=<?= $i ?>" class="pagination-btn"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>" class="pagination-btn">
                            <i data-lucide="arrow-right" stroke-width="1"></i>
                        </a>
                    <?php else: ?>
                        <button class="pagination-btn" disabled>
                            <i data-lucide="arrow-right" stroke-width="1"></i>
                        </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>

            </div>
        </div>
    </section>
</main>

<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
</body>
</html>
