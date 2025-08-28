<?php
/**
 * /user/recovery/urge-history — Paginated list of past urge logs
 */
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/../recovery.model.php';

$userId = (int)$user['id'];
$page   = max(1, (int)($_GET['page'] ?? 1));
$data   = RecoveryModel::getUrgeLogs($userId, $page);

$pageTitle = 'Urge History';
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
                <h2>Urge History</h2>
                <p><?= $data['total'] ?> total log<?= $data['total'] !== 1 ? 's' : '' ?></p>
            </div>
            <div class="card-container">
                <div class="card days-sober-card">
                    <div class="days-sober-content">
                        <p>URGES</p>
                        <i data-lucide="activity" stroke-width="1" style="color:#335346"></i>
                    </div>
                    <h2><?= $data['total'] ?></h2>
                </div>
            </div>
        </div>

        <div class="main-content-body">
            <div class="journal-container">

                <div class="journal-toolbar">
                    <a href="/user/recovery" class="back-btn" title="Back">
                        <i data-lucide="chevron-left" style="width:18px;height:18px;"></i>
                    </a>
                    <a href="/user/recovery/log-urge" class="btn btn-primary">
                        <i data-lucide="plus" style="width:16px;height:16px;margin-right:4px;"></i>
                        Log an Urge
                    </a>
                </div>

                <?php if (empty($data['logs'])): ?>
                <div class="journal-empty">
                    <i data-lucide="activity" style="width:48px;height:48px;color:var(--color-text-muted);display:block;margin:0 auto var(--spacing-md);"></i>
                    <p>No urge logs yet.</p>
                    <a href="/user/recovery/log-urge" class="btn btn-primary" style="display:inline-flex;margin-top:var(--spacing-md);">
                        Log your first urge
                    </a>
                </div>
                <?php else: ?>
                <div class="journal-list">
                    <?php foreach ($data['logs'] as $log):
                        $outcomeClass = match($log['outcome']) {
                            'resisted'        => 'success',
                            'relapsed'        => 'danger',
                            default           => 'warning',
                        };
                        $outcomeLabel = match($log['outcome']) {
                            'resisted'        => 'Resisted',
                            'relapsed'        => 'Relapsed',
                            default           => 'Still processing',
                        };
                    ?>
                    <div class="journal-card">
                        <div class="journal-card-meta">
                            <span class="journal-category-tag">
                                <?= htmlspecialchars($log['triggerCategory'] ?: 'Unknown trigger') ?>
                            </span>
                            <span class="journal-mood-tag <?= $outcomeClass ?>">
                                <?= $outcomeLabel ?>
                            </span>
                            <span class="journal-date"><?= htmlspecialchars($log['loggedAt']) ?></span>
                        </div>
                        <div class="urge-intensity-row">
                            <div class="urge-intensity-bar">
                                <div style="width:<?= ($log['intensity'] / 10) * 100 ?>%;height:100%;background:var(--color-primary);border-radius:3px;"></div>
                            </div>
                            <span class="urge-intensity-label">Intensity: <?= $log['intensity'] ?>/10</span>
                        </div>
                        <?php if ($log['copingStrategy']): ?>
                        <p class="journal-card-preview">
                            <strong>Coping strategy:</strong> <?= htmlspecialchars($log['copingStrategy']) ?>
                        </p>
                        <?php endif; ?>
                        <?php if ($log['notes']): ?>
                        <p class="journal-card-preview">
                            <?= htmlspecialchars(mb_strimwidth($log['notes'], 0, 120, '…')) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($data['totalPages'] > 1): ?>
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
                    <?php for ($i = 1; $i <= $data['totalPages']; $i++): ?>
                        <?php if ($i === $page): ?>
                            <button class="pagination-btn active"><?= $i ?></button>
                        <?php else: ?>
                            <a href="?page=<?= $i ?>" class="pagination-btn"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <?php if ($page < $data['totalPages']): ?>
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
