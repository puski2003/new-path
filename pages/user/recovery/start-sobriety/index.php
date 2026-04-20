<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/start-sobriety.model.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = trim($_POST['sobrietyDate'] ?? '');
    StartSobrietyModel::start((int)$user['id'], $date ?: null);
}

Response::redirect('/user/recovery?status=success&msg=sobriety_started');