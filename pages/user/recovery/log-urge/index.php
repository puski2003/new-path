<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/log-urge.model.php';

$userId = (int)$user['id'];
$stats = LogUrgeModel::getProgressStats($userId);
$daysSober = (int)$stats['daysSober'];

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = [
        'intensity' => (int)($_POST['intensity'] ?? 0),
        'trigger_category' => trim($_POST['trigger_category'] ?? ''),
        'coping_strategy' => trim($_POST['coping_strategy'] ?? ''),
        'outcome' => trim($_POST['outcome'] ?? ''),
        'notes' => trim($_POST['notes'] ?? ''),
    ];

    $error = LogUrgeModel::validate($postData);
    if ($error === null) {
        LogUrgeModel::save($userId, $postData);
        Response::redirect('/user/recovery?status=success');
    }
}

$data = [
    'daysSober' => $daysSober,
    'error' => $error,
];

$pageTitle = 'Log an Urge';
$pageStyle = ['user/recovery', 'user/log-urge'];

require __DIR__ . '/log-urge.view.php';