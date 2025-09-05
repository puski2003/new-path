<?php

/**
 * Route: /user/sessions/book/hash
 * Returns a JSON { hash: "..." } for the PayHere JS checkout.
 *
 * PayHere hash formula (sandbox + production are identical):
 *   MD5( merchant_id + order_id + amount + currency + MD5(merchant_secret) )
 *   — all uppercased.
 *
 * Sandbox credentials (PRD):
 *   Merchant ID:     1233865
 *   Merchant Secret: MjI4MzM1ODk5MDE4NjQyMjI0MTUzODY2Njg3OTQxMTUwMjQxMjIwMQ==
 */

require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../book.model.php';

header('Content-Type: application/json; charset=utf-8');

// --- Read + validate query params ---
$orderId  = trim((string)(Request::get('orderId')  ?? ''));
$amount   = trim((string)(Request::get('amount')   ?? ''));
$currency = 'LKR';

if ($orderId === '' || $amount === '' || !is_numeric($amount) || (float)$amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters.']);
    exit;
}

// --- Generate hash ---
$hash = BookingModel::generatePayHereHash($orderId, $amount, $currency);

if ($hash === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Unable to generate hash.']);
    exit;
}

echo json_encode(['hash' => $hash]);
exit;
