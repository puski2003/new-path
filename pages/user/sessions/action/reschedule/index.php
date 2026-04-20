<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/reschedule.model.php';

$sessionId = (int) Request::post('session_id');
$reason    = trim((string) (Request::post('reason') ?? ''));
$userId    = (int) $user['id'];

if ($sessionId <= 0) {
    Response::redirect('/user/sessions?status=error&msg=Invalid+session');
    exit;
}

$ok = RescheduleModel::requestReschedule($userId, $sessionId, $reason);

if ($ok) {
    Response::redirect('/user/sessions?status=success&msg=Reschedule+request+sent');
} else {
    Response::redirect('/user/sessions?id=' . $sessionId . '&status=error&msg=Could+not+send+request');
}