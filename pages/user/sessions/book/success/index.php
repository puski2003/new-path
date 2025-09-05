<?php

/**
 * Route: /user/sessions/book/success
 *
 * Shown after a successful PayHere payment and session creation.
 * Receives: ?session_id=X via query string (set by return/index.php)
 */

$pageStyle = ['user/sessions'];

require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../book.model.php';
require_once __DIR__ . '/../../../sessions/sessions.model.php';

$sessionId = (int)(Request::get('session_id') ?? 0);

if ($sessionId <= 0) {
    Response::redirect('/user/sessions');
    exit;
}

// Load the session (validates it belongs to this user)
$sessionData = SessionsModel::getSessionById((int)$user['id'], $sessionId);

if (!$sessionData) {
    Response::redirect('/user/sessions');
    exit;
}

// Fetch duration_minutes (not returned by getSessionById's map)
$durRs = Database::search(
    "SELECT duration_minutes FROM sessions WHERE session_id = $sessionId LIMIT 1"
);
$durRow = $durRs ? $durRs->fetch_assoc() : null;
$durationMinutes = (int)($durRow['duration_minutes'] ?? 60);

// Load counselor details
$counselorData = BookingModel::getCounselorForBooking((int)$sessionData['counselorId']);

// Load transaction for display
$txRs = Database::search(
    "SELECT transaction_uuid, payhere_payment_id, amount, processed_at
     FROM transactions
     WHERE session_id = $sessionId
     ORDER BY created_at DESC
     LIMIT 1"
);
$transaction = $txRs ? $txRs->fetch_assoc() : null;

$pageTitle = 'Booking Confirmed';

require_once __DIR__ . '/success.layout.php';
