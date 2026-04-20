<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/noshow.model.php';

$sessionId   = (int) Request::post('session_id');
$description = trim((string) (Request::post('description') ?? ''));
$userId      = (int) $user['id'];

if ($sessionId <= 0) {
    Response::redirect('/user/sessions?status=error&msg=Invalid+session');
    exit;
}

$ok = NoShowModel::reportNoShow($userId, $sessionId, $description);

if ($ok) {
    Response::redirect('/user/sessions?status=success&msg=Report+submitted');
} else {
    Response::redirect('/user/sessions?id=' . $sessionId . '&status=error&msg=Could+not+submit+report');
}