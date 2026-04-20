<?php

$userId = (int)$user['id'];

$tasks = RecoveryModel::getUserDailyTasks($userId);
$taskStats = RecoveryModel::getUserTaskStats($userId);
$activePlans = RecoveryModel::getUserActivePlans($userId);
$pendingPlans = RecoveryModel::getAssignedPlansForUser($userId);

$shortTermGoal = null;
$longTermGoal = null;
$progressPercentage = 0;

if (!empty($activePlans)) {
    $activePlan = $activePlans[0];
    $progressPercentage = (int)($activePlan['progressPercentage'] ?? 0);

    $goals = RecoveryModel::getGoalsByPlanId((int)$activePlan['planId']);
    foreach ($goals as $goal) {
        if (($goal['goalType'] ?? '') === 'short_term' && $shortTermGoal === null) {
            $shortTermGoal = $goal;
        } elseif (($goal['goalType'] ?? '') === 'long_term' && $longTermGoal === null) {
            $longTermGoal = $goal;
        }
    }
}

$progressStats = RecoveryModel::getProgressStats($userId);
$daysSober        = (int)$progressStats['daysSober'];
$totalDaysTracked = (int)$progressStats['totalDaysTracked'];
$urgesLogged      = (int)$progressStats['urgesLogged'];
$sessionsCompleted = (int)$progressStats['sessionsCompleted'];
$trackingStarted  = (bool)$progressStats['trackingStarted'];

$progressCirclePercentage = min(100, (int)(($daysSober * 100) / 100));
$strokeOffset = number_format(282.7 - (282.7 * $progressCirclePercentage / 100), 1, '.', '');

$completedCount = (int)$taskStats['completed'];
$pendingCount = (int)$taskStats['pending'];

$nextSession = RecoveryModel::getNextSessionSummary($userId);
$nextSessionTime = $nextSession['time'];
$counselorName = $nextSession['counselorName'];
$counselorNotes = "Great progress this week! Let's focus on mindfulness techniques next session.";

// Flash messages
$flashMsg  = null;
$flashType = 'success';
$msgMap = [
    'sobriety_started' => 'Sobriety tracking started! Day 1 begins today.',
    'followup_sent'    => 'Follow-up request sent! Your counselor will create a new plan for you soon.',
    'task_blocked'     => 'Complete all tasks in the current phase before moving to the next.',
];
if (isset($_GET['status'])) {
    $flashType = $_GET['status'] === 'error' ? 'error' : 'success';
    $msgKey    = $_GET['msg'] ?? '';
    $flashMsg  = $msgMap[$msgKey] ?? ($flashType === 'success' ? 'Action completed successfully.' : 'Something went wrong. Please try again.');
}

// Check if already checked in today
$todayCheckRs = Database::search(
    "SELECT 1 FROM daily_checkins WHERE user_id = $userId AND checkin_date = CURDATE() LIMIT 1"
);
$checkedInToday = $todayCheckRs && $todayCheckRs->num_rows > 0;

$pageTitle = 'Recovery Plan';
$pageStyle = ['user/dashboard', 'user/recovery'];
