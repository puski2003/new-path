<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/resume.model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::redirect('/user/recovery/manage');
    exit;
}

$planId = (int)($_POST['planId'] ?? 0);
if ($planId > 0) {
    ResumeModel::resume($planId, (int)$user['id']);
}

Response::redirect('/user/recovery/manage?status=success');