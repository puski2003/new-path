<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/request-followup.model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::redirect('/user/recovery');
    exit;
}

$planId = (int)($_POST['planId'] ?? 0);
if ($planId > 0) {
    RequestFollowupModel::request((int)$user['id'], $planId);
}

Response::redirect('/user/recovery?status=success&msg=followup_sent');