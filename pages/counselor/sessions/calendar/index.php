<?php

require_once __DIR__ . '/../../common/counselor.head.php';
require_once __DIR__ . '/../../dashboard/dashboard.model.php';

header('Content-Type: application/json');

$requestedCounselorId = (int) (Request::get('counselorId') ?? 0);
$counselorId = (int) ($user['counselorId'] ?? 0);

if ($requestedCounselorId > 0 && $requestedCounselorId !== $counselorId) {
    Response::status(403);
    echo json_encode(['error' => 'Forbidden', 'sessions' => []]);
    return;
}

$startDate = Request::get('startDate') ?? date('Y-m-01');
$endDate = Request::get('endDate') ?? date('Y-m-t');

$sessions = CounselorDashboardModel::getCalendarSessionsByCounselor($counselorId, $startDate, $endDate);
echo json_encode(['sessions' => $sessions], JSON_UNESCAPED_SLASHES);
