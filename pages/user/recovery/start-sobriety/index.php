<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/start-sobriety.model.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    StartSobrietyModel::start((int)$user['id']);
}

Response::redirect('/user/recovery?status=success&msg=sobriety_started');