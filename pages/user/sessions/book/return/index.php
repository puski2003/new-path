<?php

/**
 * Route: /user/sessions/book/return
 *
 * PayHere redirects here after a completed payment.
 * On success (status_code = 2):
 *   1. Confirm booking hold
 *   2. Generate Google Meet link
 *   3. Create session record (with Meet link)
 *   4. Record transaction
 *   5. Redirect to booking success page
 */

require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../book.model.php';
require_once __DIR__ . '/../../../../../core/GoogleMeetService.php';
require_once __DIR__ . '/../../../../../core/Mailer.php';

// ------------------------------------------------------------------
// 1. Read params
// ------------------------------------------------------------------
$holdId         = (int)(Request::get('holdId') ?? 0);
$payhereOrderId = trim((string)(Request::get('order_id')       ?? ''));
$payherePayId   = trim((string)(Request::get('payment_id')     ?? ''));
$statusCode     = trim((string)(Request::get('status_code')    ?? ''));
$amount         = trim((string)(Request::get('payhere_amount') ?? '0'));
$currency       = trim((string)(Request::get('payhere_currency') ?? 'LKR'));

function redirectWithError(string $msg): never
{
    Response::redirect('/user/sessions?error=' . urlencode($msg));
    exit;
}

// ------------------------------------------------------------------
// 2. Validate hold
// ------------------------------------------------------------------
if ($holdId <= 0) {
    redirectWithError('Invalid booking reference.');
}

$hold = BookingModel::getHold($holdId);
if (!$hold) {
    redirectWithError('Booking hold not found.');
}
if ($hold['status'] === 'confirmed') {
    // Already processed (duplicate redirect) — send to sessions list
    Response::redirect('/user/sessions?booked=1');
    exit;
}
if ($hold['status'] !== 'held') {
    redirectWithError('Your slot reservation has expired. Please try again.');
}

// ------------------------------------------------------------------
// 3. Accept the payment — status_code 2 = success
// ------------------------------------------------------------------
if ($statusCode !== '2') {
    BookingModel::releaseHold($holdId);
    redirectWithError('Payment was not successful. Please try again.');
}

// ------------------------------------------------------------------
// 4. Generate Google Meet link (best-effort — session is created even
//    if Meet link generation fails; the link can be added later)
// ------------------------------------------------------------------
$userId      = (int)$user['id'];
$counselorId = $hold['counselorId'];
$slotDt      = $hold['slotDatetime'];
$durationMin = $hold['durationMinutes'];

$counselorInfo = BookingModel::getCounselorForBooking($counselorId);
$counselorName = $counselorInfo ? $counselorInfo['name'] : 'Your Counselor';

$meetSpace  = GoogleMeetService::createMeetSpace();
$meetLink   = $meetSpace['uri']       ?? null;
$spaceName  = $meetSpace['spaceName'] ?? '';

// ------------------------------------------------------------------
// 5. Create session record
// ------------------------------------------------------------------
$sessionId = BookingModel::createSession(
    $userId,
    $counselorId,
    $slotDt,
    $durationMin,
    'video',
    $meetLink   ?? '',
    $spaceName
);

if ($sessionId <= 0) {
    BookingModel::releaseHold($holdId);
    redirectWithError('Failed to create your session. Please contact support.');
}

// ------------------------------------------------------------------
// 6. Record transaction
// ------------------------------------------------------------------
$fee = (float)$amount;
if ($fee <= 0) {
    $fee = $counselorInfo ? (float)$counselorInfo['fee'] : 0;
}

BookingModel::createTransaction(
    $sessionId,
    $userId,
    $counselorId,
    $fee,
    $payhereOrderId,
    $payherePayId,
    $statusCode
);

// ------------------------------------------------------------------
// 7. Confirm hold
// ------------------------------------------------------------------
BookingModel::confirmHold($holdId);

// ------------------------------------------------------------------
// 8. Side effects — emails + notifications
//    These run after confirmHold so they only fire once (the hold
//    status guard at the top of this file stops duplicate runs).
// ------------------------------------------------------------------
$userName      = $user['name'] ?? 'Client';
$userEmail     = BookingModel::getUserEmail($userId);
$counselorEmail = $counselorInfo['email'] ?? '';
$counselorUserId = (int)($counselorInfo['userId'] ?? 0);
$sessionDateLabel = date('F j, Y \a\t g:i A', strtotime($slotDt));
$sessionLink   = '/user/sessions/book/success?session_id=' . $sessionId;

// -- 8a. Confirmation email to user --
if ($userEmail !== '') {
    $meetLinkHtml = ($meetLink !== '' && $meetLink !== null)
        ? "<p style='margin:8px 0;'>
               <strong>Meeting link:</strong>
               <a href='" . htmlspecialchars($meetLink) . "' style='color:#4CAF50;'>" . htmlspecialchars($meetLink) . "</a>
           </p>"
        : "<p style='color:#999;font-size:0.85rem;'>A meeting link will be shared before your session.</p>";

    $userHtml = "
        <div style='font-family:Montserrat,sans-serif;max-width:520px;margin:auto;padding:32px;'>
            <h2 style='color:#2c3e50;margin-bottom:8px;'>Session Confirmed!</h2>
            <p style='color:#555;'>Hi " . htmlspecialchars($userName) . ", your counseling session has been booked successfully.</p>
            <div style='background:#f9f9f9;border-radius:8px;padding:20px;margin:20px 0;'>
                <p style='margin:8px 0;'><strong>Counselor:</strong> " . htmlspecialchars($counselorName) . "</p>
                <p style='margin:8px 0;'><strong>Date &amp; Time:</strong> " . htmlspecialchars($sessionDateLabel) . "</p>
                <p style='margin:8px 0;'><strong>Duration:</strong> " . (int)$durationMin . " minutes</p>
                " . $meetLinkHtml . "
            </div>
            <a href='/user/sessions' style='display:inline-block;padding:12px 28px;background:#4CAF50;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;'>
                View My Sessions
            </a>
            <p style='color:#999;font-size:0.85rem;margin-top:24px;'>Thank you for choosing NewPath.</p>
        </div>";

    Mailer::send($userEmail, 'NewPath  Your session is confirmed', $userHtml, $userName);
}

// -- 8b. Confirmation email to counselor --
if ($counselorEmail !== '') {
    $counselorHtml = "
        <div style='font-family:Montserrat,sans-serif;max-width:520px;margin:auto;padding:32px;'>
            <h2 style='color:#2c3e50;margin-bottom:8px;'>New Session Booked</h2>
            <p style='color:#555;'>Hi " . htmlspecialchars($counselorName) . ", a client has booked a session with you.</p>
            <div style='background:#f9f9f9;border-radius:8px;padding:20px;margin:20px 0;'>
                <p style='margin:8px 0;'><strong>Client:</strong> " . htmlspecialchars($userName) . "</p>
                <p style='margin:8px 0;'><strong>Date &amp; Time:</strong> " . htmlspecialchars($sessionDateLabel) . "</p>
                <p style='margin:8px 0;'><strong>Duration:</strong> " . (int)$durationMin . " minutes</p>
            </div>
            <a href='/counselor/sessions' style='display:inline-block;padding:12px 28px;background:#4CAF50;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;'>
                View My Schedule
            </a>
        </div>";

    Mailer::send($counselorEmail, 'NewPath  New session booked', $counselorHtml, $counselorName);
}

// -- 8c. Notification for user --
Database::setUpConnection();
$notifTitle = Database::$connection->real_escape_string('Session Confirmed');
$notifMsg   = Database::$connection->real_escape_string(
    'Your session with ' . $counselorName . ' on ' . $sessionDateLabel . ' is confirmed.'
);
$notifLink  = Database::$connection->real_escape_string('/user/sessions');
Database::iud("INSERT INTO notifications (user_id, type, title, message, link)
               VALUES ($userId, 'booking_confirmed', '$notifTitle', '$notifMsg', '$notifLink')");

// -- 8d. Notification for counselor --
if ($counselorUserId > 0) {
    $cNotifTitle = Database::$connection->real_escape_string('New Session Booked');
    $cNotifMsg   = Database::$connection->real_escape_string(
        $userName . ' has booked a session on ' . $sessionDateLabel . '.'
    );
    $cNotifLink  = Database::$connection->real_escape_string('/counselor/sessions');
    Database::iud("INSERT INTO notifications (user_id, type, title, message, link)
                   VALUES ($counselorUserId, 'new_booking', '$cNotifTitle', '$cNotifMsg', '$cNotifLink')");
}

Response::redirect('/user/sessions/book/success?session_id=' . $sessionId);
exit;
