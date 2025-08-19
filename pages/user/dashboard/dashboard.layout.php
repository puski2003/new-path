<?php

/**
 * User Dashboard Layout
 * Variables available: $user, $data (from controller)
 */
$activePage = 'dashboard';
$pageScripts = [
    '/assets/js/user/dashboard/dashboard.js',
];



?>
<!DOCTYPE html>
<html lang="en">

<?php
$pageTitle = "User Dashboard — New Path";
$pageStyle = ["user/dashboard"];
require_once __DIR__ . '/../common/user.html.head.php';
?>

<body>
    <main class="main-container">
        <?php require_once __DIR__ . '/../common/user.sidebar.php'; ?>

        <section class="main-content">
            <?php if ($flashSuccess): ?>
                <div class="success-message" style="margin:16px 24px 0;"><?= htmlspecialchars($flashSuccess) ?></div>
            <?php elseif ($flashError): ?>
                <div class="error-message" style="margin:16px 24px 0;"><?= htmlspecialchars($flashError) ?></div>
            <?php endif; ?>
            <img
                src="/assets/img/main-content-head.svg"
                alt="Main Content Head background"
                class="main-header-bg-image" />

            <div class="main-content-header">
                <div class="main-content-header-text">
                    <h2>Hi, <?= htmlspecialchars(explode(' ', $userName)[0]) ?>! 👋</h2>
                    <p>Track your progress and stay motivated</p>
                </div>

                <div class="card-container">
                    <div class="card days-sober-card">
                        <div class="days-sober-content">
                            <p>DAYS SOBER</p>
                            <i data-lucide="heart" stroke-width="1" style="color: #335346"></i>
                        </div>
                        <h2><?= $daysSober ?></h2>
                    </div>

                    <div class="card milestone-progress-card">
                        <p>SOBRIETY PROGRESS</p>
                        <span><?= $milestoneProgress ?>%</span>
                        <div class="progress" style="--value: <?= $milestoneProgress ?>%">
                            <div class="bar"></div>
                            <div class="thumb" aria-label="Progress <?= $milestoneProgress ?> percent"></div>
                        </div>
                    </div>
                </div>

                
            </div>

            <div class="main-content-body">
                <div class="inner-body-content">
                    <div class="body-column">
                        <!-- Due Now Section -->
                        <div class="col-1-row-1 dashboard-card">
                            <div class="card-header">
                                <h3>Due now</h3>
                            </div>
                            <div class="due-now-content">
                                <?php if (!empty($nextSession)): ?>
                                    <div class="upcoming-session">
                                        <div class="upcoming-session-info">
                                            <div class="session-icon">
                                            <i
                                                data-lucide="calendar-days"
                                                stroke-width="1"
                                                 width="30px"
                                                    height="30px"
                                                    color="white"></i>
                                            </div>
                                            <div class="session-details">
                                                <h4>Upcoming Session</h4>
                                                <p>With <?= htmlspecialchars($nextSession['counselorName']) ?>, <?= htmlspecialchars($nextSession['formattedTime']) ?></p>
                                            </div>
                                        </div>
                                        <a href="<?= htmlspecialchars($nextSession['meetingLink']) ?>" class="btn btn-primary btn-sm" target="_blank">Join</a>
                                    </div>
                                <?php else: ?>
                                    <div class="upcoming-session">
                                        <div class="upcoming-session-info">
                                            <div class="session-icon">
                                                <i
                                                    data-lucide="calendar-days"
                                                    stroke-width="1"
                                                    width="30px"
                                                    height="30px"
                                                    color="white"
                                                ></i>
                                                
                                            </div>
                                            <div class="session-details">
                                                <h4>No Upcoming Sessions</h4>
                                                <p>Book a session with a counselor</p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Community Highlights Section -->
                        <div class="col-1-row-2 dashboard-card">
                            <div class="card-header">
                                <h3>Community highlights</h3>
                                <i data-lucide="shield" stroke-width="1"></i>
                            </div>
                            <div class="community-highlights">
                                <?php if (!empty($communityHighlights)): ?>
                                    <?php foreach ($communityHighlights as $post): ?>
                                        <div class="highlight-item">
                                            <img
                                                src="<?= !empty($post['profilePictureUrl']) ? htmlspecialchars($post['profilePictureUrl']) : '/assets/img/avatar.png' ?>"
                                                alt="<?= htmlspecialchars($post['displayName']) ?>"
                                                class="user-avatar" />
                                            <div class="highlight-info">
                                                <span class="username"><?= $post['anonymous'] ? 'Anonymous' : htmlspecialchars($post['displayName']) ?></span>
                                                <span class="time"><?= htmlspecialchars($post['title']) ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="highlight-item">
                                        <div class="highlight-info">
                                            <span class="username" style="color: var(--color-text-muted)">No community posts yet</span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="body-column">
                        <!-- Daily Tasks Section -->
                        <div class="col-2-row-1 dashboard-card">
                            <div class="card-header">
                                <h3>Daily Tasks</h3>
                                <a href="/user/recovery" class="view-all-link">
                                    View All
                                    <i data-lucide="arrow-right" stroke-width="2"></i>
                                </a>
                            </div>
                            <?php if (isset($_GET['taskBlocked'])): ?>
                            <div class="error-message" style="margin:var(--spacing-xs) var(--spacing-md);">
                                Complete all tasks in the current phase first.
                            </div>
                            <?php endif; ?>
                            <div class="daily-tasks">
                                <?php if (!empty($dailyTasks)): ?>
                                    <?php foreach ($dailyTasks as $task): ?>
                                        <div class="task-item <?= $task['completed'] ? 'task-item--done' : '' ?> <?= $task['urgent'] ? 'task-item--urgent' : '' ?>" data-task-id="<?= $task['id'] ?>">
                                            <div class="task-checkbox <?= $task['completed'] ? 'completed' : ($task['urgent'] ? 'urgent' : '') ?>">
                                                <?php if ($task['completed']): ?>
                                                    <i data-lucide="check" style="width:14px;height:14px;" color="white"></i>
                                                <?php elseif ($task['urgent']): ?>
                                                    <i data-lucide="alert-circle" style="width:14px;height:14px;color:var(--color-warning);"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="task-item-body">
                                                <span class="task-text <?= $task['completed'] ? 'completed' : '' ?>"><?= htmlspecialchars($task['title']) ?></span>
                                                <span class="task-type-label"><?= htmlspecialchars($task['taskType']) ?></span>
                                            </div>
                                            <?php if (!$task['completed']): ?>
                                            <form method="post" action="/user/recovery/task/complete">
                                                <input type="hidden" name="taskId" value="<?= $task['id'] ?>" />
                                                <input type="hidden" name="returnTo" value="dashboard" />
                                                <button type="submit" class="task-done-btn">Done</button>
                                            </form>
                                            <?php else: ?>
                                            <span class="task-done-label">Done</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="task-item-empty">
                                        <i data-lucide="clipboard-check" style="width:28px;height:28px;color:var(--color-text-muted);"></i>
                                        <span>No tasks for today</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="tasks-pagination" id="dashTasksPagination" style="display:none;">
                                <button type="button" class="tasks-page-btn" id="dashTasksPrev" aria-label="Previous page">&#8592;</button>
                                <span class="tasks-page-info" id="dashTasksPageInfo"></span>
                                <button type="button" class="tasks-page-btn" id="dashTasksNext" aria-label="Next page">&#8594;</button>
                            </div>
                        </div>

                        <!-- Progress Tracker Section -->
                        <a href="/user/recovery/progress" style="text-decoration: none;">
                        <div class="col-2-row-2 dashboard-card progress-tracker-card">
                            <div class="card-header">
                                <h3>Plan Tracker</h3>
                                <i data-lucide="arrow-right" stroke-width="1" class="arrow-icon"></i>
                            </div>
                            <div class="progress-content">
                                <div class="progress-bar-wrapper">
                                    <div class="progress-bar-container">
                                        <div
                                            class="progress-bar-fill"
                                            style="--progress: <?= $progressPercentage ?>%"></div>
                                    </div>
                                    <span class="progress-percentage"><?= $progressPercentage ?>%</span>
                                </div>
                            </div>
                        </div>
                        </a>
                    </div>

                    <div class="body-column">
                        <!-- Quick Log Section -->
                        <div class="col-3-row-1 dashboard-card">
                            <div class="card-header">
                                <h3>Quick Log</h3>
                                <?php if (isset($_GET['logSuccess']) && $_GET['logSuccess'] === 'true'): ?>
                                    <span style="color: var(--color-success); font-size: 0.875rem;">✓ Saved!</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <form action="/user/quick-log" method="POST" class="quick-log-content">
                                    <textarea
                                        class="log-textarea"
                                        name="logText"
                                        placeholder="How are you feeling today? Tell us about your mood, any urges, or what's on your mind..."
                                        required></textarea>
                                    <button type="submit" class="btn btn-primary log-submit-btn">
                                        Save Log
                                    </button>
                                </form>
                                <?php if (isset($_GET['error']) && $_GET['error'] === 'empty'): ?>
                                    <p style="color: var(--color-error); font-size: 0.75rem; margin-top: 0.5rem;">Please enter something before saving.</p>
                                <?php endif; ?>
                                <?php if (isset($_GET['error']) && $_GET['error'] === 'saveFailed'): ?>
                                    <p style="color: var(--color-error); font-size: 0.75rem; margin-top: 0.5rem;">Could not save log. Please try again.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Achievements Section -->
                        <div class="col-3-row-2 dashboard-card achievements-card">
                            <div class="card-header">
                                <h3>Achievements</h3>
                                <div class="achievement-icon">
                                    <i data-lucide="trophy" stroke-width="1"></i>
                                </div>
                            </div>
                            <div class="achievement-content">
                                <?php if ($achievements['oneYear']): ?>
                                    <div class="achievement-badge">
                                        <i data-lucide="crown" stroke-width="1"></i>
                                        <span class="achievement-text">1 Year Champion! 🎉</span>
                                    </div>
                                <?php elseif ($achievements['sixMonths']): ?>
                                    <div class="achievement-badge">
                                        <i data-lucide="medal" stroke-width="1"></i>
                                        <span class="achievement-text">6 Months Strong! 💪</span>
                                    </div>
                                <?php elseif ($achievements['threeMonths']): ?>
                                    <div class="achievement-badge">
                                        <i data-lucide="award" stroke-width="1"></i>
                                        <span class="achievement-text">90 Days Strong!</span>
                                    </div>
                                <?php elseif ($achievements['firstMonth']): ?>
                                    <div class="achievement-badge">
                                        <i data-lucide="star" stroke-width="1"></i>
                                        <span class="achievement-text">First Month Complete!</span>
                                    </div>
                                <?php elseif ($achievements['sevenDays']): ?>
                                    <div class="achievement-badge">
                                        <i data-lucide="circle-star" stroke-width="1"></i>
                                        <span class="achievement-text">7 Days Strong</span>
                                    </div>
                                <?php else: ?>
                                    <div class="achievement-badge">
                                        <i data-lucide="target" stroke-width="1"></i>
                                        <span class="achievement-text">First week goal: <?= $nextMilestone ?> days</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Goals Widget -->
                        <div class="col-3-row-3 dashboard-card goals-widget-card">
                            <div class="card-header">
                                <h3>My Goals</h3>
                                <a href="/user/recovery/goals" class="view-all-link">
                                    Manage <i data-lucide="arrow-right" stroke-width="2"></i>
                                </a>
                            </div>

                            <?php if (empty($userGoals)): ?>
                            <div class="goals-empty">
                                <i data-lucide="target" style="width:26px;height:26px;color:var(--color-text-muted);"></i>
                                <p>No goals set yet.</p>
                                <a href="/user/recovery/goals" class="goal-add-link">+ Add a goal</a>
                            </div>
                            <?php else: ?>
                            <div class="goals-list">
                                <?php foreach (array_slice($userGoals, 0, 3) as $g):
                                    $isAchieved = $g['status'] === 'achieved';
                                ?>
                                <div class="goal-widget-item <?= $isAchieved ? 'goal-widget-item--done' : '' ?>">
                                    <div class="goal-widget-top">
                                        <span class="goal-widget-title"><?= htmlspecialchars($g['title']) ?></span>
                                        <span class="goal-widget-days"><?= $g['currentProgress'] ?>/<?= $g['targetDays'] ?>d</span>
                                    </div>
                                    <div class="goal-widget-bar">
                                        <div class="goal-widget-fill" style="width:<?= $g['progressPercentage'] ?>%"></div>
                                    </div>
                                    <?php if (!$isAchieved): ?>
                                    <form method="post" action="/user/recovery/goal/log-progress" style="display:inline;">
                                        <input type="hidden" name="goal_id" value="<?= $g['goalId'] ?>" />
                                        <input type="hidden" name="days" value="1" />
                                        <input type="hidden" name="returnTo" value="dashboard" />
                                        <button type="submit" class="goal-log-btn">+1 day</button>
                                    </form>
                                    <?php else: ?>
                                    <span class="goal-achieved-badge">
                                        <i data-lucide="check-circle-2" style="width:11px;height:11px;vertical-align:middle;"></i> Achieved
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                                <?php if (count($userGoals) > 3): ?>
                                <p style="font-size:var(--font-size-xs);color:var(--color-text-muted);text-align:center;margin-top:4px;">
                                    +<?= count($userGoals) - 3 ?> more — <a href="/user/recovery/goals" style="color:var(--color-primary);">view all</a>
                                </p>
                                <?php endif; ?>
                            </div>
                            <a href="/user/recovery/goals" class="goal-add-link" style="margin-top:auto;">+ Add goal</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php require_once __DIR__ . '/../common/user.footer.php'; ?>
</body>

</html>
