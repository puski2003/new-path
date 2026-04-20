<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/receipt.model.php';

$sessionId = filter_var(Request::get('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($sessionId === false) {
    Response::redirect('/user/sessions');
    exit;
}

$sessionData = ReceiptModel::getSessionData((int)$user['id'], (int)$sessionId);
if (!$sessionData || !$sessionData['hasPayment']) {
    Response::redirect('/user/sessions?id=' . (int)$sessionId);
    exit;
}

$autoPrint = Request::get('print') === '1';

require_once __DIR__ . '/receipt.view.php';
