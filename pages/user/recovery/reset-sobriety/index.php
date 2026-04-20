<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/reset-sobriety.model.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = $_POST['reason'] ?? '';
    ResetSobrietyModel::reset((int)$user['id'], $reason);
}

Response::redirect('/user/recovery?status=success');