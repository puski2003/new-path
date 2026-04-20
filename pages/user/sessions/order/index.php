<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/order.model.php';

$sessionId = filter_var(Request::get('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($sessionId === false) {
    Response::redirect('/user/sessions');
    exit;
}

$sessionData = OrderModel::getSessionData((int)$user['id'], (int)$sessionId);
if (!$sessionData || !$sessionData['hasPayment']) {
    Response::redirect('/user/sessions?id=' . (int)$sessionId);
    exit;
}

$pageTitle = 'Order Details';
$pageStyle = ['user/dashboard', 'user/sessions'];

require_once __DIR__ . '/order.view.php';
