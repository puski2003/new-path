<?php
require_once __DIR__ . '/counselor-management.model.php';

$filters = [
    'appStatus' => Request::get('appStatus') ?? 'pending',
    'specialization' => Request::get('specialization') ?? 'all',
    'counselorStatus' => Request::get('counselorStatus') ?? 'all',
];

$page = Pagination::sanitizePage(Request::get('page') ?? 1);
$perPage = 15;

$pageData = CounselorManagementModel::getPageData($filters, $page, $perPage);
$stats = $pageData['stats'];
$applications = $pageData['applications']['items'];
$applicationsPagination = $pageData['applications']['pagination'];
$counselors = $pageData['counselors']['items'];
$counselorsPagination = $pageData['counselors']['pagination'];
