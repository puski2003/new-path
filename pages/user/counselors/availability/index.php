<?php

require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/../counselors.model.php';

header('Content-Type: application/json');

$counselorId = (int) (Request::get('counselorId') ?? 0);
$startDate = trim((string) (Request::get('startDate') ?? ''));
$endDate = trim((string) (Request::get('endDate') ?? ''));

$startDateObj = DateTime::createFromFormat('Y-m-d', $startDate);
$endDateObj = DateTime::createFromFormat('Y-m-d', $endDate);

if (
    $counselorId <= 0
    || !$startDateObj
    || !$endDateObj
    || $startDateObj->format('Y-m-d') !== $startDate
    || $endDateObj->format('Y-m-d') !== $endDate
) {
    Response::status(400);
    echo json_encode(['error' => 'Invalid parameters.', 'unavailableSlots' => new stdClass()]);
    exit;
}

$unavailableSlots = CounselorsModel::getUnavailableSlotsByCounselor($counselorId, $startDate, $endDate);

echo json_encode(['unavailableSlots' => $unavailableSlots], JSON_UNESCAPED_SLASHES);
exit;
