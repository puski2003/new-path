<?php
$activePage = 'dashboard';
$displayName = trim((string) ($currentCounselor['displayName'] ?? 'Counselor'));
$welcomeName = $displayName !== '' ? (explode(' ', $displayName)[0] ?? 'Counselor') : 'Counselor';
$pageScripts = [];
?>
<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Counselor Dashboard';
$pageStyle = ['counselor/dashboard'];
require_once __DIR__ . '/../common/counselor.html.head.php';
?>
<body>
<main class="main-container theme-counselor">
    <?php require_once __DIR__ . '/../common/counselor.sidebar.php'; ?>

    <section class="main-content">
        <?php if (!empty($flashSuccess)): ?>
            <div class="success-message" style="margin:16px 24px 0;"><?= htmlspecialchars($flashSuccess) ?></div>
        <?php endif; ?>
        <img src="/assets/img/main-content-head.svg" alt="Main Content Head background" class="main-header-bg-image" />

        <div class="main-content-header">
            <div class="main-content-header-text">
                <h2>Welcome back, <?= htmlspecialchars($welcomeName) ?>! &#128075;</h2>
                <p>Here's an overview of the day.</p>
            </div>

            <div class="card-container">
                <div class="card days-sober-card">
                    <div class="days-sober-content">
                        <p>INCOME</p>
                        <h1>Rs.<?= number_format((float)$totalIncome, 2) ?></h1>
                    </div>
                    <p>This month</p>
                </div>

                <div class="card days-sober-card" style="width: 200px;">
                    <div class="days-sober-content">
                        <p>Active Clients</p>
                        <h1><?= htmlspecialchars((string) (!empty($activeClientsCount) ? $activeClientsCount : ($currentCounselor['totalClients'] ?? 0))) ?></h1>
                    </div>
                    <p>Total</p>
                </div>
            </div>
        </div>

        <div class="main-content-body">
            <div class="inner-body-content">
                <div class="body-column">
                    <div class="col-1-row-1 dashboard-card counselor-schedule-card">
                        <div class="card-header">
                            <h3>Schedule</h3>
                        </div>
                        <?php require_once __DIR__ . '/../common/counselor.calendar.php'; ?>
                    </div>

                    <div class="col-1-row-2 dashboard-card counselor-activity-section">
                        <div class="card-header">
                            <h3>Client Activity Feed</h3>
                        </div>
                        <div class="community-highlights counselor-activity-list">
                            <?php if (!empty($clientActivities)): ?>
                                <?php foreach ($clientActivities as $activity): ?>
                                    <div class="client-activity-card">
                                        <div class="client-activity-card-info">
                                            <img src="<?= htmlspecialchars($activity['profilePicture']) ?>" alt="<?= htmlspecialchars($activity['clientName']) ?>">
                                            <div class="highlight-info">
                                                <h4><?= htmlspecialchars($activity['clientName']) ?></h4>
                                                <span><?= htmlspecialchars($activity['description']) ?></span>
                                            </div>
                                        </div>
                                        <span class="client-activity-time"><?= htmlspecialchars($activity['timeAgo']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="client-activity-card">
                                    <div class="client-activity-card-info">
                                        <img src="/assets/img/avatar.png" alt="Default avatar">
                                        <div class="highlight-info">
                                            <h4>No recent activity</h4>
                                            <span>Client activities will appear here.</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="body-column">

                    <div class="col-2-row-1 dashboard-card counselor-upcoming-section">
                        <div class="card-header">
                            <h3>Upcoming Sessions</h3>
                        </div>
                        <div class="counselor-session-list">
                            <?php if (!empty($upcomingSessions)): ?>
                                <?php foreach ($upcomingSessions as $session): ?>
                                    <?php
                                    $isUpcoming = true;
                                    require __DIR__ . '/../common/counselor.session-card.php';
                                    ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="counselor-empty-state">
                                    No upcoming sessions scheduled.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-2-row-2 dashboard-card">
                        <div class="card-header">
                            <h3>Active Clients</h3>
                        </div>
                        <?php if(!empty($activeClients)): ?>
                            <?php foreach($activeClients as $client): ?>
                                <div class="active-clients-card">
                                    <p><?= $client['name'] ?></p>
                                    <a class="btn-join" href="/counselor/client-profile?id=<?= $client["id"] ?>">view profile</a>
                                </div>
                            <?php endforeach; ?>
                                
                        <?php else:?>
                            <div class="active-clients-card">
                                <p>No Active Clients </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../common/counselor.footer.php'; ?>
</body>
</html>
