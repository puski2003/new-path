<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/urge-history.model.php';

$userId = (int)$user['id'];
$page = max(1, (int)($_GET['page'] ?? 1));
$data = UrgeHistoryModel::getUrgeLogs($userId, $page);

$pageTitle = 'Urge History';
$pageStyle = ['user/recovery', 'user/journal'];

require __DIR__ . '/urge-history.view.php';