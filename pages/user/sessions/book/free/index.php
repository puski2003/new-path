<?php

/**
 * Route: POST /user/sessions/book/free
 *
 * Confirms a free rebook when the user has an approved reschedule credit.
 * No payment is taken. The hold must already exist (created by book/index.php).
 *
 * Required POST fields:
 *   hold_id    — booking_holds.hold_id
 *   credit_id  — reschedule_requests.request_id
 *   session_type — video|audio|chat|in_person
 */

require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../book.model.php';
require_once __DIR__ . '/free.model.php';
require_once ROOT . '/core/GoogleMeetService.php';
require_once ROOT . '/core/Mailer.php';

if (!Request::isPost()) {
    Response::redirect('/user/sessions');
    exit;
}

$userId      = (int)$user['id'];
$holdId      = (int)(Request::post('hold_id')     ?? 0);
$creditId    = (int)(Request::post('credit_id')   ?? 0);
$sessionType = trim((string)(Request::post('session_type') ?? 'video'));

if (!in_array($sessionType, ['video','audio','chat','in_person'], true)) {
    $sessionType = 'video';
}

function freeBookError(string $msg): never
{
    Response::redirect('/user/sessions?error=' . urlencode($msg));
    exit;
}

// ------------------------------------------------------------------
// 1. Validate hold
// ------------------------------------------------------------------
if ($holdId <= 0) freeBookError('Invalid booking reference.');

$hold = BookingModel::getHold($holdId);
if (!$hold)                        freeBookError('Booking hold not found.');
if ($hold['userId'] !== $userId)   freeBookError('Booking hold mismatch.');
if ($hold['status'] === 'confirmed') {
    Response::redirect('/user/sessions?booked=1');
    exit;
}
if ($hold['status'] !== 'held')    freeBookError('Your slot reservation has expired. Please try again.');

// ------------------------------------------------------------------
// 2. Validate credit
// ------------------------------------------------------------------
if ($creditId <= 0) freeBookError('Invalid reschedule credit.');

$credit = BookingModel::getActiveFreeCredit($userId, (int)$hold['counselorId']);
if (!$credit || $credit['requestId'] !== $creditId) {
    freeBookError('Reschedule credit is invalid or has already been used.');
}

// ------------------------------------------------------------------
// 3. Generate Google Meet link
// ------------------------------------------------------------------
$counselorId   = $hold['counselorId'];
$slotDt        = $hold['slotDatetime'];
$durationMin   = $hold['durationMinutes'];

$counselorInfo = BookingModel::getCounselorForBooking($counselorId);
$counselorName = $counselorInfo ? $counselorInfo['name'] : 'Your Counselor';

$meetSpace  = GoogleMeetService::createMeetSpace();
$meetLink   = $meetSpace['uri']       ?? null;
$spaceName  = $meetSpace['spaceName'] ?? '';

// ------------------------------------------------------------------
// 4. Create session record
// ------------------------------------------------------------------
$sessionId = BookingModel::createSession(
    $userId,
    $counselorId,
    $slotDt,
    $durationMin,
    $sessionType,
    $meetLink ?? '',
    $spaceName
);

if ($sessionId <= 0) {
    BookingModel::releaseHold($holdId);
    freeBookError('Failed to create your session. Please contact support.');
}

// ------------------------------------------------------------------
// 5. Record $0 transaction (type = reschedule_credit)
// ------------------------------------------------------------------
FreeBookModel::recordZeroTransaction($sessionId, $userId, $counselorId, $creditId);

// ------------------------------------------------------------------
// 6. Confirm hold + consume credit (in this order so hold is safe first)
// ------------------------------------------------------------------
BookingModel::confirmHold($holdId);
BookingModel::consumeFreeCredit($creditId, $userId);

// ------------------------------------------------------------------
// 7. Side effects — emails + notifications
// ------------------------------------------------------------------
$userName         = $user['name'] ?? 'Client';
$userEmail        = BookingModel::getUserEmail($userId);
$counselorEmail   = $counselorInfo['email'] ?? '';
$counselorUserId  = (int)($counselorInfo['userId'] ?? 0);
$sessionDateLabel = date('F j, Y \a\t g:i A', strtotime($slotDt));

if ($userEmail !== '') {
    $meetLinkHtml = (!empty($meetLink))
        ? "<p style='margin:8px 0;'><strong>Meeting link:</strong> <a href='" . htmlspecialchars($meetLink) . "' style='color:#4CAF50;'>" . htmlspecialchars($meetLink) . "</a></p>"
        : "<p style='color:#999;font-size:0.85rem;'>A meeting link will be shared before your session.</p>";

    $userHtml = "
        <div style='font-family:Montserrat,sans-serif;max-width:520px;margin:auto;padding:32px;'>
            <h2 style='color:#2c3e50;margin-bottom:8px;'>Session Rescheduled!</h2>
            <p style='color:#555;'>Hi " . htmlspecialchars($userName) . ", your rescheduled session has been confirmed.</p>
            <div style='background:#f9f9f9;border-radius:8px;padding:20px;margin:20px 0;'>
                <p style='margin:8px 0;'><strong>Counselor:</strong> " . htmlspecialchars($counselorName) . "</p>
                <p style='margin:8px 0;'><strong>Date &amp; Time:</strong> " . htmlspecialchars($sessionDateLabel) . "</p>
                <p style='margin:8px 0;'><strong>Duration:</strong> " . (int)$durationMin . " minutes</p>
                <p style='margin:8px 0;color:#4CAF50;font-weight:600;'>No charge — reschedule credit applied.</p>
                " . $meetLinkHtml . "
            </div>
            <a href='/user/sessions' style='display:inline-block;padding:12px 28px;background:#4CAF50;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;'>View My Sessions</a>
        </div>";

    Mailer::send($userEmail, 'NewPath  Your rescheduled session is confirmed', $userHtml, $userName);
}

if ($counselorEmail !== '') {
    $counselorHtml = "
        <div style='font-family:Montserrat,sans-serif;max-width:520px;margin:auto;padding:32px;'>
            <h2 style='color:#2c3e50;margin-bottom:8px;'>Rescheduled Session Confirmed</h2>
            <p style='color:#555;'>Hi " . htmlspecialchars($counselorName) . ", a client has completed their rescheduled booking.</p>
            <div style='background:#f9f9f9;border-radius:8px;padding:20px;margin:20px 0;'>
                <p style='margin:8px 0;'><strong>Client:</strong> " . htmlspecialchars($userName) . "</p>
                <p style='margin:8px 0;'><strong>Date &amp; Time:</strong> " . htmlspecialchars($sessionDateLabel) . "</p>
                <p style='margin:8px 0;'><strong>Duration:</strong> " . (int)$durationMin . " minutes</p>
            </div>
            <a href='/counselor/sessions' style='display:inline-block;padding:12px 28px;background:#4CAF50;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;'>View My Schedule</a>
        </div>";

    Mailer::send($counselorEmail, 'NewPath  Rescheduled session booked', $counselorHtml, $counselorName);
}

FreeBookModel::insertNotifications($userId, $counselorUserId, $userName, $counselorName, $sessionDateLabel);

Response::redirect('/user/sessions/book/success?session_id=' . $sessionId);
exit;
