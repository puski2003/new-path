<?php

/**
 * Route: /user/sessions/extend/checkout
 *
 * Shows the PayHere payment form for an accepted session extension.
 * Expects: ?extension_id=X
 */

require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../extend.model.php';
require_once __DIR__ . '/../../book/book.model.php';
require_once __DIR__ . '/checkout.model.php';

$extensionId = (int)(Request::get('extension_id') ?? 0);
$userId      = (int)$user['id'];

if ($extensionId <= 0) {
    Response::redirect('/user/sessions');
    exit;
}

$ext = ExtendModel::getAccepted($extensionId, $userId);
if (!$ext) {
    Response::redirect('/user/sessions?error=' . urlencode('Extension request not found or already processed.'));
    exit;
}

$counselor = BookingModel::getCounselorForBooking($ext['counselorId']);
if (!$counselor) {
    Response::redirect('/user/sessions?error=' . urlencode('Counselor not found.'));
    exit;
}

$userDetails     = CheckoutModel::getUserDetails($userId);
$userDisplayName = $userDetails['displayName'];
$userEmail       = $userDetails['email'];
$userPhone       = $userDetails['phone'];

$orderId         = 'EXT-' . $extensionId;
$fee             = $ext['fee'];
$amountFormatted = number_format($fee, 2, '.', '');
$payhereHash     = BookingModel::generatePayHereHash($orderId, $amountFormatted);

$scheme    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
$returnUrl = $scheme . '://' . $host . '/user/sessions/extend/return?extension_id=' . $extensionId;
$cancelUrl = $scheme . '://' . $host . '/user/sessions?id=' . $ext['sessionId'];

$pageTitle = 'Extend Session — Checkout';
$pageStyle = ['user/sessions'];

require_once __DIR__ . '/checkout.view.php';
