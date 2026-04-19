<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/journal.model.php';

$userId = (int)$user['id'];
$stats = JournalModel::getStats($userId);
$daysSober = (int)$stats['daysSober'];

$page = max(1, (int)($_GET['page'] ?? 1));
$result = JournalModel::getEntries($userId, $page, 10);

$data = [
    'entries' => $result['entries'],
    'total' => $result['total'],
    'page' => $result['page'],
    'totalPages' => $result['totalPages'],
    'daysSober' => $daysSober,
];

$pageTitle = 'Journal';
$pageStyle = ['user/recovery', 'user/journal'];

require __DIR__ . '/journal.view.php';