<?php

$counselorIdRaw = Request::get('id');
$counselorId = filter_var($counselorIdRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($counselorId === false) {
    Response::status(404);
    require ROOT . '/pages/404.php';
    exit;
}

$counselor = CounselorsModel::getCounselorById((int)$counselorId);
if ($counselor === null) {
    Response::status(404);
    require ROOT . '/pages/404.php';
    exit;
}

$availabilityData = json_decode((string)($counselor['availability_schedule'] ?? '{}'), true);
if (!is_array($availabilityData)) {
    $availabilityData = [];
}

$availabilityJson = json_encode(
    $availabilityData,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);

if ($availabilityJson === false) {
    $availabilityJson = '{}';
}

// Reviews data for the reviews section
$reviewsPerPage    = 5;
$reviewsPage       = max(1, (int)(Request::get('reviewsPage') ?? 1));
$reviewsData       = CounselorsModel::getReviewsByCounselor((int)$counselorId, $reviewsPage, $reviewsPerPage);
$reviews           = $reviewsData['data'];
$reviewsTotal      = $reviewsData['total'];
$reviewsTotalPages = max(1, (int)ceil($reviewsTotal / $reviewsPerPage));
$ratingBreakdown   = CounselorsModel::getRatingBreakdown((int)$counselorId);

$pageTitle = 'Counselor Details';
$pageStyle = ['user/dashboard', 'user/counselors'];
