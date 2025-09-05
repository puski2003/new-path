<?php

$counselorId     = filter_var(Request::get('counselorId'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$dateParam       = trim((string)(Request::get('date') ?? ''));    // YYYY-MM-DD
$timeParam       = trim((string)(Request::get('time') ?? ''));    // HH:MM
$sessionType     = trim((string)(Request::get('type') ?? 'video'));

// ------------------------------------------------------------------
// Validate params
// ------------------------------------------------------------------
$bookingError = null;

if ($counselorId === false || $counselorId <= 0) {
    $bookingError = 'Invalid counselor.';
}

if (!$bookingError) {
    // Validate date (YYYY-MM-DD)
    $dateObj = $dateParam ? DateTime::createFromFormat('Y-m-d', $dateParam) : false;
    if (!$dateObj || $dateObj->format('Y-m-d') !== $dateParam) {
        $bookingError = 'Invalid booking date.';
    }
}

if (!$bookingError) {
    // Validate time (HH:MM)
    if (!preg_match('/^\d{2}:\d{2}$/', $timeParam)) {
        $bookingError = 'Invalid booking time.';
    }
}

// ------------------------------------------------------------------
// Load counselor
// ------------------------------------------------------------------
$counselor = null;
if (!$bookingError) {
    $counselor = BookingModel::getCounselorForBooking((int)$counselorId);
    if (!$counselor) {
        $bookingError = 'Counselor not found.';
    }
}

// ------------------------------------------------------------------
// Build slot datetime
// ------------------------------------------------------------------
$slotDatetime    = null;
$durationMinutes = 60;

if (!$bookingError) {
    $slotDatetime = $dateParam . ' ' . $timeParam . ':00';

    // Ensure slot is in the future
    if (strtotime($slotDatetime) <= time()) {
        $bookingError = 'This time slot is in the past. Please select a future slot.';
    }
}

// ------------------------------------------------------------------
// Lock slot — create a 15-minute hold
// ------------------------------------------------------------------
$holdId = 0;
if (!$bookingError) {
    $userId  = (int)$user['id'];
    $holdId  = BookingModel::lockSlot((int)$counselorId, $userId, $slotDatetime, $durationMinutes);
    if ($holdId === 0) {
        $bookingError = 'This time slot is no longer available. Please go back and choose a different time.';
    }
}

// ------------------------------------------------------------------
// Check for an active free-rebook credit (approved reschedule)
// ------------------------------------------------------------------
$freeCredit = null;
if (!$bookingError && $holdId > 0) {
    $freeCredit = BookingModel::getActiveFreeCredit($userId, (int)$counselorId);
}

// ------------------------------------------------------------------
// Build PayHere order details (only when paying)
// ------------------------------------------------------------------
$payhereOrderId  = $holdId > 0 ? ('HOLD-' . $holdId) : '';
$sessionFee      = $counselor['fee'] ?? 0;
$platformFee     = $freeCredit ? 0 : round($sessionFee * 0.10, 2);
$amount          = $freeCredit ? 0 : round($sessionFee + $platformFee, 2);
$amountFormatted = number_format($amount, 2, '.', '');
$payhereHash     = ($holdId > 0 && !$freeCredit) ? BookingModel::generatePayHereHash($payhereOrderId, number_format($sessionFee + round($sessionFee * 0.10, 2), 2, '.', '')) : null;

// Return / Cancel URLs (absolute for PayHere)
$scheme       = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host         = $_SERVER['HTTP_HOST'] ?? 'localhost';
$returnUrl    = $scheme . '://' . $host . '/user/sessions/book/return?holdId=' . $holdId;
$cancelUrl    = $scheme . '://' . $host . '/user/sessions/book/cancel?holdId=' . $holdId;
$notifyUrl    = ''; // PRD: no server-side notify needed

// User name/email for PayHere form
$userDisplayName = $user['name'] ?? 'User';
$userEmail       = '';
$userPhone       = '';

// Fetch user details for PayHere
$userRs = Database::search(
    "SELECT email, phone_number, COALESCE(display_name, CONCAT(first_name,' ',last_name), username, 'User') AS display_name
     FROM users WHERE user_id = " . (int)$user['id'] . " LIMIT 1"
);
if ($userRs && ($userRow = $userRs->fetch_assoc())) {
    $userDisplayName = $userRow['display_name'];
    $userEmail       = $userRow['email'] ?? '';
    $userPhone       = $userRow['phone_number'] ?? '';
}

$pageTitle = 'Checkout';
$pageStyle = ['user/sessions'];
