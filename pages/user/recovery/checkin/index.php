<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/checkin.model.php';

$userId = (int)$user['id'];

$existing = CheckinModel::getTodayCheckin($userId);
$stats = CheckinModel::getStats($userId);
$daysSober = (int)$stats['daysSober'];

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = [
        'mood_rating' => (int)($_POST['mood_rating'] ?? 0),
        'energy_level' => (int)($_POST['energy_level'] ?? 0),
        'sleep_quality' => (int)($_POST['sleep_quality'] ?? 0),
        'stress_level' => (int)($_POST['stress_level'] ?? 0),
        'is_sober' => isset($_POST['is_sober']) ? 1 : 0,
        'notes' => trim($_POST['notes'] ?? ''),
    ];

    $error = CheckinModel::validate($postData);
    if ($error === null) {
        CheckinModel::save($userId, $postData);
        Response::redirect('/user/recovery?status=success');
    }
}

$data = [
    'existing' => $existing,
    'daysSober' => $daysSober,
    'error' => $error,
];

$pageTitle = 'Daily Check-in';
$pageStyle = ['user/recovery', 'user/checkin'];

require __DIR__ . '/checkin.view.php';