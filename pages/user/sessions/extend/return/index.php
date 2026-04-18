<?php

/**
 * Route: /user/sessions/extend/return
 *
 * PayHere redirects here after an extension payment.
 * On success (status_code = 2):
 *   1. Mark extension as paid
 *   2. Update session extended_minutes + extension_fee
 *   3. Record transaction
 *   4. Send notifications + emails to both parties
 *   5. Redirect to extension success page
 */

require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../extend.model.php';
require_once __DIR__ . '/../../../../../core/Mailer.php';

$extensionId    = (int)(Request::get('extension_id')     ?? 0);
$payhereOrderId = trim((string)(Request::get('order_id')          ?? ''));
$payherePayId   = trim((string)(Request::get('payment_id')        ?? ''));
$statusCode     = trim((string)(Request::get('status_code')       ?? ''));
$userId         = (int)$user['id'];

function extRedirectError(string $msg): never
{
    Response::redirect('/user/sessions?error=' . urlencode($msg));
    exit;
}

if ($extensionId <= 0) {
    extRedirectError('Invalid extension reference.');
}

// Prevent duplicate processing: if already paid, send to success page
$checkRs = Database::search(
    "SELECT status FROM session_extension_requests WHERE extension_id = $extensionId LIMIT 1"
);
$checkRow = $checkRs ? $checkRs->fetch_assoc() : null;
if (!$checkRow) {
    extRedirectError('Extension request not found.');
}
if ($checkRow['status'] === 'paid') {
    Response::redirect('/user/sessions/extend/success?extension_id=' . $extensionId);
    exit;
}
if ($checkRow['status'] !== 'accepted') {
    extRedirectError('This extension request is no longer valid.');
}

// Require successful payment
if ($statusCode !== '2') {
    extRedirectError('Payment was not successful. Your session has not been extended.');
}

// Process payment
$ok = ExtendModel::markPaid($extensionId, $payhereOrderId, $payherePayId, $statusCode);
if (!$ok) {
    extRedirectError('Could not process extension. Please contact support.');
}

// Load full details for notifications + emails
$ext = ExtendModel::getFullById($extensionId);
if (!$ext) {
    // Paid but can't load details — still redirect to success
    Response::redirect('/user/sessions/extend/success?extension_id=' . $extensionId);
    exit;
}

$sessionDateLabel = date('F j, Y \a\t g:i A', strtotime((string)$ext['sessionDatetime']));
$totalMinutes     = $ext['originalDuration'] + $ext['extendedMinutes'];
$newEndTs         = strtotime((string)$ext['sessionDatetime']) + ($totalMinutes * 60);
$newEndLabel      = date('g:i A', $newEndTs);

// ── In-app notification: user ──
Database::setUpConnection();
$uTitle = Database::$connection->real_escape_string('Session Extended!');
$uMsg   = Database::$connection->real_escape_string(
    'Your session has been extended by ' . $ext['durationMinutes'] . ' minutes. New end time: ' . $newEndLabel . '.'
);
$uLink  = Database::$connection->real_escape_string('/user/sessions?id=' . $ext['sessionId']);
Database::iud(
    "INSERT INTO notifications (user_id, type, title, message, link)
     VALUES ({$ext['userId']}, 'extension_paid', '$uTitle', '$uMsg', '$uLink')"
);

// ── In-app notification: counselor ──
if ($ext['counselorUserId'] > 0) {
    $cTitle = Database::$connection->real_escape_string('Extension Payment Confirmed');
    $cMsg   = Database::$connection->real_escape_string(
        $ext['userName'] . ' has paid for the ' . $ext['durationMinutes'] . '-minute extension. New end time: ' . $newEndLabel . '.'
    );
    $cLink  = Database::$connection->real_escape_string('/counselor/sessions/workspace?session_id=' . $ext['sessionId']);
    Database::iud(
        "INSERT INTO notifications (user_id, type, title, message, link)
         VALUES ({$ext['counselorUserId']}, 'extension_paid', '$cTitle', '$cMsg', '$cLink')"
    );
}

$meetLinkHtml = !empty($ext['meetingLink'])
    ? "<p style='margin:8px 0;'><strong>Meeting link:</strong> <a href='" . htmlspecialchars($ext['meetingLink']) . "' style='color:#4CAF50;'>" . htmlspecialchars($ext['meetingLink']) . "</a></p>"
    : '';

// ── Email: user ──
if (!empty($ext['userEmail'])) {
    $userHtml = "
        <div style='font-family:Montserrat,sans-serif;max-width:520px;margin:auto;padding:32px;'>
            <h2 style='color:#2c3e50;margin-bottom:8px;'>Session Extended!</h2>
            <p style='color:#555;'>Hi " . htmlspecialchars($ext['userName']) . ", your session extension payment was successful.</p>
            <div style='background:#f9f9f9;border-radius:8px;padding:20px;margin:20px 0;'>
                <p style='margin:8px 0;'><strong>Counselor:</strong> " . htmlspecialchars($ext['counselorName']) . "</p>
                <p style='margin:8px 0;'><strong>Session Date:</strong> " . htmlspecialchars($sessionDateLabel) . "</p>
                <p style='margin:8px 0;'><strong>Extension:</strong> " . (int)$ext['durationMinutes'] . " minutes</p>
                <p style='margin:8px 0;'><strong>New End Time:</strong> " . htmlspecialchars($newEndLabel) . "</p>
                " . $meetLinkHtml . "
            </div>
            <a href='/user/sessions?id=" . (int)$ext['sessionId'] . "'
               style='display:inline-block;padding:12px 28px;background:#4CAF50;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;'>
                View Session
            </a>
            <p style='color:#999;font-size:0.85rem;margin-top:24px;'>Thank you for choosing NewPath.</p>
        </div>";
    Mailer::send($ext['userEmail'], 'NewPath — Session Extended Successfully', $userHtml, $ext['userName']);
}

// ── Email: counselor ──
if (!empty($ext['counselorEmail'])) {
    $counselorHtml = "
        <div style='font-family:Montserrat,sans-serif;max-width:520px;margin:auto;padding:32px;'>
            <h2 style='color:#2c3e50;margin-bottom:8px;'>Extension Payment Confirmed</h2>
            <p style='color:#555;'>Hi " . htmlspecialchars($ext['counselorName']) . ", your session extension has been paid.</p>
            <div style='background:#f9f9f9;border-radius:8px;padding:20px;margin:20px 0;'>
                <p style='margin:8px 0;'><strong>Client:</strong> " . htmlspecialchars($ext['userName']) . "</p>
                <p style='margin:8px 0;'><strong>Session Date:</strong> " . htmlspecialchars($sessionDateLabel) . "</p>
                <p style='margin:8px 0;'><strong>Extension:</strong> " . (int)$ext['durationMinutes'] . " minutes</p>
                <p style='margin:8px 0;'><strong>New End Time:</strong> " . htmlspecialchars($newEndLabel) . "</p>
            </div>
            <a href='/counselor/sessions/workspace?session_id=" . (int)$ext['sessionId'] . "'
               style='display:inline-block;padding:12px 28px;background:#4CAF50;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;'>
                Open Workspace
            </a>
        </div>";
    Mailer::send($ext['counselorEmail'], 'NewPath — Session Extension Confirmed', $counselorHtml, $ext['counselorName']);
}

Response::redirect('/user/sessions/extend/success?extension_id=' . $extensionId);
exit;
