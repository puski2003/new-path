<?php

/**
 * Counselors Controller — handles both "My Counselors" and "Find a Counselor" tabs.
 */

$activeTab = $_GET['tab'] ?? 'my';

// Defaults for Find tab (needed even on My tab to avoid undefined-var warnings in layout)
$myCounselors        = [];
$counselors          = [];
$specialties         = [];
$searchQuery         = '';
$selectedSpecialty   = '';
$selectedMinExperience = '';
$selectedMaxPrice    = '';
$selectedMinRating   = '';
$currentPage         = 1;
$totalPages          = 1;

$freeCreditIds = CounselorsModel::getFreeCreditCounselorIds((int)$user['id']);

if ($activeTab === 'my') {
    $myCounselors = CounselorsModel::getMyCounselors((int)$user['id']);
    foreach ($myCounselors as &$mc) {
        $mc['hasFreeCredit'] = in_array((int)$mc['counselor_id'], $freeCreditIds, true);
    }
    unset($mc);
} else {
    $searchQuery         = $_GET['q'] ?? '';
    $selectedSpecialty   = $_GET['specialty'] ?? '';
    $selectedMinExperience = $_GET['minExperience'] ?? '';
    $selectedMaxPrice    = $_GET['maxPrice'] ?? '';
    $selectedMinRating   = $_GET['minRating'] ?? '';
    $currentPage         = max(1, (int)($_GET['page'] ?? 1));
    $limit               = 12;

    $params = [
        'q'             => $searchQuery,
        'specialty'     => $selectedSpecialty,
        'minExperience' => $selectedMinExperience,
        'maxPrice'      => $selectedMaxPrice,
        'minRating'     => $selectedMinRating,
        'limit'         => $limit,
        'offset'        => ($currentPage - 1) * $limit,
    ];

    $result      = CounselorsModel::getCounselors($params);
    $counselors  = $result['data'];
    $totalCount  = $result['total'];
    $specialties = CounselorsModel::getSpecialties();
    $totalPages  = (int)ceil($totalCount / $limit);

    foreach ($counselors as &$c) {
        $c['hasFreeCredit'] = in_array((int)$c['counselor_id'], $freeCreditIds, true);
    }
    unset($c);
}
