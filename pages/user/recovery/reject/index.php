<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/reject.model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::redirect('/user/recovery');
    exit;
}

$planId = (int)($_POST['planId'] ?? 0);
if ($planId > 0) {
    RejectModel::reject($planId, (int)$user['id']);
}

Response::redirect('/user/recovery?status=success');