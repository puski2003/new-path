<?php

require_once __DIR__ . '/dashboard.model.php';

$flashSuccess = isset($_GET['updateSuccess']) ? 'Profile updated successfully.' : null;

$counselorId = (int) ($user['counselorId'] ?? 0);
$currentCounselor = CounselorDashboardModel::getCounselorById($counselorId) ?? $currentCounselor;
$upcomingSessions = CounselorDashboardModel::getUpcomingSessionsByCounselor($counselorId);
$activeClients = CounselorDashboardModel::getActiveClientsCount($counselorId);
$totalIncome = CounselorDashboardModel::getTotalIncome($counselorId);
$clientActivities = CounselorDashboardModel::getClientActivities($counselorId);

$data = compact(
    'currentCounselor',
    'upcomingSessions',
    'activeClients',
    'totalIncome',
    'clientActivities'
);
