<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/progress.model.php';

$userId = (int)$user['id'];
$model  = new ProgressModel();

$stats     = $model->getProgressStats($userId);
$taskStats = $model->getUserTaskStats($userId);

$daysSober         = (int)$stats['daysSober'];
$daysTracked       = (int)$stats['totalDaysTracked'];
$urgesLogged       = (int)$stats['urgesLogged'];
$sessionsCompleted = (int)$stats['sessionsCompleted'];
$trackingStarted   = (bool)$stats['trackingStarted'];

$milestones    = [1, 7, 14, 30, 60, 90, 180, 365];
$nextMilestone = 365;
$prevMilestone = 0;
foreach ($milestones as $m) {
    if ($daysSober < $m) { $nextMilestone = $m; break; }
    $prevMilestone = $m;
}
$milestoneProgress = ($nextMilestone > $prevMilestone)
    ? min(100, (int)round(($daysSober - $prevMilestone) / ($nextMilestone - $prevMilestone) * 100))
    : 100;

$totalTasks   = $taskStats['completed'] + $taskStats['pending'];
$taskRate     = ($totalTasks > 0) ? (int)round($taskStats['completed'] / $totalTasks * 100) : 0;
$recoveryRate = ($daysTracked > 0) ? min(100, (int)round($daysSober / $daysTracked * 100)) : 0;
$sessionRate  = min(100, $sessionsCompleted * 10);
$soberChange  = $daysSober - $model->getPreviousDaysSober($userId);

$data = [
    'daysSober'         => $daysSober,
    'daysTracked'       => $daysTracked,
    'urgesLogged'       => $urgesLogged,
    'sessionsCompleted' => $sessionsCompleted,
    'trackingStarted'   => $trackingStarted,
    'nextMilestone'     => $nextMilestone,
    'milestoneProgress' => $milestoneProgress,
    'achievements'      => $model->getUserAchievements($userId),
    'soberChart'        => $model->getSobrietyChartData($userId, $daysSober),
    'urgeChart'         => $model->getUrgeSparklineData($userId),
    'sessionChart'      => $model->getSessionBarData($userId),
    'sessionsHistory'   => $model->getSessionsHistory($userId),
    'taskStats'         => $taskStats,
    'totalTasks'        => $totalTasks,
    'taskRate'          => $taskRate,
    'recoveryRate'      => $recoveryRate,
    'sessionRate'       => $sessionRate,
    'soberChange'       => $soberChange,
];

$pageTitle = 'Progress Tracker';
$pageStyle = ['user/progress-tracker'];

require __DIR__ . '/progress.view.php';
