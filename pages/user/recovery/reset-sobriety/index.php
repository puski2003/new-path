<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/../recovery.model.php';

if (Request::isPost()) {
    $reason = Request::post('reason') ?? '';
    RecoveryModel::resetSobrietyCounter((int)$user['id'], $reason);
}

Response::redirect('/user/recovery?reset=1');
