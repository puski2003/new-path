<?php

/**
 * POST /counselor/profile/update
 * Updates counselor profile — users table + counselors table.
 */
require_once ROOT . '/pages/counselor/common/counselor.head.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::redirect('/counselor/dashboard');
    exit;
}

$uid         = (int) $user['id'];
$counselorId = (int) $user['counselorId'];

// Sanitise helpers
$db = Database::$connection;
function esc($val) { global $db; return $db->real_escape_string(trim($val)); }

$displayName     = esc($_POST['displayName']      ?? '');
$title           = esc($_POST['title']            ?? '');
$specialty       = esc($_POST['specialty']        ?? '');
$bio             = esc($_POST['bio']              ?? '');
$email           = esc($_POST['email']            ?? '');
$phone           = esc($_POST['phoneNumber']      ?? '');
$fee             = !empty($_POST['consultationFee']) ? (float) $_POST['consultationFee'] : 0;

// Build availability JSON — multi-slot format: {day: [{start, end}, ...]}
$allDays      = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
$validTimes   = [];
for ($h = 6; $h <= 22; $h++) { $validTimes[] = sprintf('%02d:00', $h); }

$availability = [];
foreach ($allDays as $day) {
    if (empty($_POST["{$day}_enabled"])) continue;

    $rawSlots = $_POST["{$day}_slots"] ?? [];
    $slots    = [];
    foreach ((array)$rawSlots as $slot) {
        $start = trim((string)($slot['start'] ?? ''));
        $end   = trim((string)($slot['end']   ?? ''));
        if (in_array($start, $validTimes, true) && in_array($end, $validTimes, true) && $start < $end) {
            $slots[] = ['start' => $start, 'end' => $end];
        }
    }
    if (!empty($slots)) {
        $availability[$day] = $slots;
    }
}
$availJson = esc(json_encode($availability));

// Handle profile picture upload
$picUpdate = '';
$uploadDir = ROOT . '/public/uploads/profiles';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
    $ext         = pathinfo($_FILES['profilePicture']['name'], PATHINFO_EXTENSION);
    $newFile     = 'profile_' . $uid . '_' . time() . '.' . $ext;
    $dest        = $uploadDir . '/' . $newFile;
    if (move_uploaded_file($_FILES['profilePicture']['tmp_name'], $dest)) {
        $picUrl    = esc('/uploads/profiles/' . $newFile);
        $picUpdate = ", profile_picture = '$picUrl'";
    }
}

// Update users table
Database::iud(
    "UPDATE users
     SET display_name = '$displayName', email = '$email', phone_number = '$phone' $picUpdate
     WHERE user_id = $uid"
);

// Update counselors table
Database::iud(
    "UPDATE counselors
     SET title = '$title', specialty = '$specialty', bio = '$bio',
         consultation_fee = $fee, availability_schedule = '$availJson'
     WHERE counselor_id = $counselorId"
);

// Refresh JWT with updated name/picture
$newPayload               = $user;
$newPayload['name']       = $displayName ?: $user['name'];
if ($picUpdate !== '') $newPayload['profilePictureUrl'] = '/uploads/profiles/' . $newFile;

Auth::setTokenCookie(Auth::sign($newPayload));

Response::redirect('/counselor/dashboard?updateSuccess=1');
