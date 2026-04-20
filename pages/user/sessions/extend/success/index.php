<?php

/**
 * Route: /user/sessions/extend/success
 *
 * Shown after a successful extension payment.
 * Displays the new end time and the (same) meeting link.
 */

require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../extend.model.php';
require_once __DIR__ . '/success.model.php';

$extensionId = (int)(Request::get('extension_id') ?? 0);
$userId      = (int)$user['id'];

if ($extensionId <= 0) {
    Response::redirect('/user/sessions');
    exit;
}

$ext = ExtendSuccessModel::getForDisplay($extensionId, $userId);
if (!$ext) {
    Response::redirect('/user/sessions');
    exit;
}

$pageTitle = 'Session Extended';
$pageStyle = ['user/sessions'];

require_once __DIR__ . '/success.view.php';
