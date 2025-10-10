<?php

$helpCenters = HelpModel::getActiveHelpCenters();
$emergencyCenters = HelpModel::getActiveEmergencyServices();

$pageTitle = 'Help Center';
$pageStyle = ['user/dashboard', 'user/helpCenter'];

$mapCenter = static function (array $center): array {
    $availability = (string)($center['availability'] ?? 'Available');
    $status = 'available';
    if (stripos($availability, 'weekend') !== false || stripos($availability, 'busy') !== false) {
        $status = 'busy';
    }
    if (stripos($availability, 'offline') !== false || stripos($availability, 'closed') !== false) {
        $status = 'offline';
    }

    $type = strtolower(trim((string)($center['type'] ?? 'resources')));
    $category = strtolower(trim((string)($center['category'] ?? 'community')));

    $typeLabels = [
        'hotline' => 'Phone Support',
        'chat' => 'Live Chat',
        'appointment' => 'Appointment',
        'resources' => 'Self-Help Resources',
    ];

    $contactLabel = 'Contact';
    if (!empty($center['phoneNumber'])) {
        $contactLabel = 'Call: ' . $center['phoneNumber'];
    } elseif ($type === 'chat') {
        $contactLabel = 'Start Chat';
    } elseif ($type === 'appointment') {
        $contactLabel = 'Schedule';
    } elseif ($type === 'resources') {
        $contactLabel = 'Browse';
    }

    $locationParts = array_filter([
        (string)($center['address'] ?? ''),
        (string)($center['city'] ?? ''),
        (string)($center['state'] ?? ''),
        (string)($center['zipCode'] ?? ''),
    ], static fn ($part) => trim($part) !== '');

    $searchText = strtolower(implode(' ', [
        (string)($center['name'] ?? ''),
        (string)($center['description'] ?? ''),
        (string)($center['category'] ?? ''),
        (string)($center['organization'] ?? ''),
        (string)($center['specialties'] ?? ''),
    ]));

    return [
        'id' => (int)($center['helpCenterId'] ?? 0),
        'title' => (string)($center['name'] ?? ''),
        'organization' => (string)($center['organization'] ?? ''),
        'type' => $type !== '' ? $type : 'resources',
        'typeLabel' => $typeLabels[$type] ?? (string)($center['type'] ?? ''),
        'category' => $category !== '' ? $category : 'community',
        'categoryLabel' => ucwords(str_replace('-', ' ', $category !== '' ? $category : 'community')),
        'phoneNumber' => (string)($center['phoneNumber'] ?? ''),
        'email' => (string)($center['email'] ?? ''),
        'website' => (string)($center['website'] ?? ''),
        'availability' => $availability !== '' ? $availability : 'Available',
        'description' => (string)($center['description'] ?? ''),
        'specialties' => (string)($center['specialties'] ?? ''),
        'status' => $status,
        'contactLabel' => $contactLabel,
        'location' => implode(', ', $locationParts),
        'searchText' => $searchText,
    ];
};

$helpServices = array_map($mapCenter, $helpCenters);
$emergencyServices = array_map($mapCenter, $emergencyCenters);
