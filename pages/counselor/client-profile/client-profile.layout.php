<?php
$activePage  = 'clients';
$progressPct = (int) $clientProfile['progressPercentage'];
$completedPct = $clientProfile['totalSessions'] > 0
    ? (int) round(($clientProfile['completedSessions'] / $clientProfile['totalSessions']) * 100)
    : 0;

$pageHeaderTitle    = 'Clients';
$pageHeaderSubtitle = 'Your path to guidance starts here.';
$pageScripts = ['/assets/js/counselor/client-profile/clientProfile.js'];
?>
<!DOCTYPE html>
<html lang="en">
<?php $pageTitle = 'Client Profile'; $pageStyle = ['counselor/clientProfile']; require __DIR__ . '/../common/counselor.html.head.php'; ?>
<body>
<main class="main-container theme-counselor">
    <?php require __DIR__ . '/../common/counselor.sidebar.php'; ?>

    <section class="main-content">
        <?php require __DIR__ . '/../common/counselor.page-header.php'; ?>

        <div class="main-content-body">

            <!-- Back button -->
            <div class="cc-back-row">
                <a class="cc-back-btn" href="/counselor/clients">
                    <i data-lucide="arrow-left" stroke-width="1.8"></i>
                    Back to Clients
                </a>
            </div>

            <!-- ── Hero profile card ── -->
            <div class="cc-profile-card">
                <div class="cc-profile-avatar">
                    <img src="<?= htmlspecialchars($clientProfile['avatarUrl']) ?>"
                         alt="<?= htmlspecialchars($clientProfile['name']) ?>" />
                </div>
                <div class="cc-profile-info">
                    <h3 class="cc-profile-name"><?= htmlspecialchars($clientProfile['name']) ?></h3>
                    <p class="cc-profile-status"><?= htmlspecialchars($clientProfile['status']) ?></p>
                    <?php if (!empty($clientProfile['email'])): ?>
                        <p class="cc-profile-email"><?= htmlspecialchars($clientProfile['email']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($clientProfile['bio'])): ?>
                        <p class="cc-profile-bio"><?= nl2br(htmlspecialchars($clientProfile['bio'])) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($clientProfile['joinedAt'])): ?>
                        <p class="cc-profile-joined">Joined <?= htmlspecialchars($clientProfile['joinedAt']) ?></p>
                    <?php endif; ?>
                </div>
                <div class="cc-profile-pricing">
                    <span class="stat-label">Plan Progress</span>
                    <span class="stat-value"><?= $progressPct ?>%</span>
                    <div class="cc-progress-slider" style="--value: <?= $progressPct ?>%">
                        <div class="bar"></div>
                        <div class="thumb" aria-label="<?= $progressPct ?>% plan progress"></div>
                    </div>
                </div>
            </div>

            <!-- ── Recovery plan section ── -->
            <div class="cc-section">
                <div class="cc-section-header">
                    <h4>Recovery Plan</h4>
                    <?php if (!empty($clientProfile['plan']['planId'])): ?>
                        <a href="/counselor/recovery-plans/view?planId=<?= (int) $clientProfile['plan']['planId'] ?>"
                           class="btn btn-primary" style="font-size:var(--font-size-xs);">View Full Plan</a>
                    <?php else: ?>
                        <a href="/counselor/recovery-plans/create?userId=<?= (int) $clientProfile['id'] ?>"
                           class="btn btn-primary" style="font-size:var(--font-size-xs);">Create Plan</a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($clientProfile['plan'])): ?>
                    <div class="cc-plan-card">
                        <div class="cc-plan-info">
                            <span class="cc-plan-title"><?= htmlspecialchars($clientProfile['plan']['title']) ?></span>
                            <span class="plan-status status-<?= htmlspecialchars($clientProfile['plan']['status']) ?>">
                                <?= htmlspecialchars(ucfirst($clientProfile['plan']['status'])) ?>
                            </span>
                            <?php if (!empty($clientProfile['plan']['description'])): ?>
                                <p class="cc-plan-desc"><?= htmlspecialchars($clientProfile['plan']['description']) ?></p>
                            <?php endif; ?>
                            <div class="plan-progress" style="margin-top:var(--spacing-sm);">
                                <div class="progress-bar-small">
                                    <div class="progress-fill-small"
                                         style="width:<?= (int) $clientProfile['plan']['progressPercentage'] ?>%;"></div>
                                </div>
                                <span class="progress-text"><?= (int) $clientProfile['plan']['progressPercentage'] ?>%</span>
                            </div>
                        </div>
                        <img src="/assets/img/plan.png" alt="Plan" />
                    </div>
                <?php else: ?>
                    <p style="font-size:var(--font-size-sm);color:var(--color-text-muted);">
                        No recovery plan assigned yet. Create one to start tracking progress.
                    </p>
                <?php endif; ?>
            </div>

            <!-- ── Community activity section ── -->
            <div class="cc-section">
                <div class="cc-section-header">
                    <h4>Community Activity</h4>
                    <span class="cc-section-meta"><?= (int) $clientProfile['communityPostsCount'] ?> posts</span>
                </div>

                <?php if (!empty($clientProfile['communityPosts'])): ?>
                    <div class="cc-community-posts">
                        <?php foreach ($clientProfile['communityPosts'] as $post): ?>
                            <div class="cc-community-post-card">
                                <h5><?= htmlspecialchars($post['title'] ?: 'Community post') ?></h5>
                                <p><?= nl2br(htmlspecialchars(mb_strlen($post['content']) > 180 ? mb_substr($post['content'], 0, 180) . '...' : $post['content'])) ?></p>
                                <div class="cc-post-meta">
                                    <span><?= htmlspecialchars($post['createdAt'] ? date('M j, Y', strtotime($post['createdAt'])) : '') ?></span>
                                    <span><?= (int) $post['likesCount'] ?> likes</span>
                                    <span><?= (int) $post['commentsCount'] ?> comments</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="font-size:var(--font-size-sm);color:var(--color-text-muted);">
                        No community posts yet.
                    </p>
                <?php endif; ?>
            </div>

            <!-- ── Session history section ── -->
            <div class="cc-section">
                <div class="cc-section-header">
                    <h4>Session History</h4>
                </div>

                <div class="cc-session-stats">
                    <div class="cc-stat-box">
                        <span class="cc-stat-num"><?= (int) $clientProfile['totalSessions'] ?></span>
                        <span class="cc-stat-caption">Total Sessions</span>
                    </div>
                    <div class="cc-stat-box">
                        <span class="cc-stat-num"><?= (int) $clientProfile['completedSessions'] ?></span>
                        <span class="cc-stat-caption">Completed</span>
                    </div>
                    <div class="cc-stat-box">
                        <span class="cc-stat-num"><?= (int) $clientProfile['totalSessions'] - (int) $clientProfile['completedSessions'] ?></span>
                        <span class="cc-stat-caption">Remaining</span>
                    </div>
                </div>

                <div class="cc-progress-row">
                    <span>Completed</span>
                    <div class="cc-bar">
                        <div class="cc-fill" style="width:<?= $completedPct ?>%;"></div>
                    </div>
                    <span><?= $completedPct ?>%</span>
                </div>
                <div class="cc-progress-row">
                    <span>Plan progress</span>
                    <div class="cc-bar">
                        <div class="cc-fill" style="width:<?= $progressPct ?>%;"></div>
                    </div>
                    <span><?= $progressPct ?>%</span>
                </div>
            </div>

            <!-- ── Session notes section ── -->
            <div class="cc-section">
                <div class="cc-section-header">
                    <h4>Session Notes</h4>
                    <button class="btn btn-secondary" style="font-size:var(--font-size-xs);" type="button">Add Notes</button>
                </div>
                <p class="cc-notes-text"><?= htmlspecialchars($clientProfile['sessionNotes']) ?></p>
            </div>

        </div>
    </section>
</main>
<?php require __DIR__ . '/../common/counselor.footer.php'; ?>
</body>
</html>
