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
require_once __DIR__ . '/../../view/view.model.php';

$sessionId = (int)(Request::get('session_id') ?? 0);

if ($sessionId <= 0) {
    Response::redirect('/user/sessions');
    exit;
}

// Load the session (validates it belongs to this user)
$sessionData = ViewModel::getSessionById((int)$user['id'], $sessionId);

if (!$sessionData) {
    Response::redirect('/user/sessions');
    exit;
}

$successData     = BookingModel::getBookingSuccess($sessionId);
$durationMinutes = $successData['durationMinutes'];
$transaction     = $successData['transaction'];

// Load counselor details
$counselorData = BookingModel::getCounselorForBooking((int)$sessionData['counselorId']);

$pageTitle = 'Booking Confirmed';

require_once __DIR__ . '/success.view.php';
