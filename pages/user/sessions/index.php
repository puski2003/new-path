<?php
require_once __DIR__ . '/../common/user.head.php';
require_once __DIR__ . '/sessions.model.php';

$userId    = (int) $user['id'];
$sessionId = Request::get('id');

if ($sessionId !== null && $sessionId !== '') {
    require_once __DIR__ . '/view/index.php';
    exit;
} else {
    $upcomingPage  = filter_var(Request::get('upage'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
    $historyPage   = filter_var(Request::get('hpage'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
    $reportsPage   = filter_var(Request::get('rpage'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
    $requestedTab  = (string)(Request::get('tab') ?? 'upcoming');
    $activeTab     = in_array($requestedTab, ['upcoming', 'history', 'reports'], true) ? $requestedTab : 'upcoming';

    $upcomingData  = SessionsModel::getSessionsByType($userId, 'upcoming', $upcomingPage, 5);
    $historyData   = SessionsModel::getSessionsByType($userId, 'history',  $historyPage,  5);
    $reportsData   = SessionsModel::getReportItems($userId, $reportsPage, 5);

    $upcomingSessions     = $upcomingData['items'];
    $historySessions      = $historyData['items'];
    $reportItems          = $reportsData['items'];
    $upcomingCurrentPage  = (int) $upcomingData['page'];
    $upcomingTotalPages   = (int) $upcomingData['totalPages'];
    $upcomingTotal        = (int) $upcomingData['total'];
    $historyCurrentPage   = (int) $historyData['page'];
    $historyTotalPages    = (int) $historyData['totalPages'];
    $historyTotal         = (int) $historyData['total'];
    $reportsCurrentPage   = (int) $reportsData['page'];
    $reportsTotalPages    = (int) $reportsData['totalPages'];
    $reportsTotal         = (int) $reportsData['total'];

    $followupSessions = SessionsModel::getFollowupSessions($userId);

    $pageTitle = 'Sessions';
    $pageStyle = ['user/dashboard', 'user/sessions'];
    $pageScripts = [
        '/assets/js/user/sessions/sessions.js',
    ];

    require_once __DIR__ . '/sessions.view.php';
}