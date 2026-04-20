<?php
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/task-change-requests.model.php';

$userId = (int)$user['id'];
$requests = TaskChangeRequestsModel::getRequests($userId);

$data = ['requests' => $requests];

$pageTitle = 'My Change Requests';
$pageStyle = ['user/recovery', 'user/journal'];

require __DIR__ . '/task-change-requests.view.php';