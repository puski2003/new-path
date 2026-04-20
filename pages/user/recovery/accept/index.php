<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/accept.model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::redirect('/user/recovery');
    exit;
}

$planId = (int)($_POST['planId'] ?? 0);
if ($planId > 0) {
    $result = AcceptModel::accept($planId, (int)$user['id']);
}

if ($result ?? false) {
    Response::redirect('/user/recovery?status=success');
} else {
    Response::redirect('/user/recovery/manage?status=error');
}