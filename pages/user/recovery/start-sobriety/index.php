<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/../recovery.model.php';

if (Request::isPost()) {
    RecoveryModel::startSobrietyTracking((int)$user['id']);
}

Response::redirect('/user/recovery?started=1');
