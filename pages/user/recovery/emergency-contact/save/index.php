<?php
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/emergency-contact.model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::redirect('/user/recovery');
    exit;
}

$userId = (int)$user['id'];
$name = trim($_POST['ecName'] ?? '');
$phone = trim($_POST['ecPhone'] ?? '');

if ($name === '' || $phone === '') {
    Response::redirect('/user/recovery?status=error&msg=empty');
}

EmergencyContactModel::saveEmergencyContact($userId, $name, $phone);
Response::redirect('/user/recovery?status=success');