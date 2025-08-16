<?php

/**
 * Route: /user/profile/update
 * Handles POST requests to update user profile.
 */
require_once __DIR__ . '/../../../../user/common/user.head.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::redirect('/user/dashboard');
    exit;
}

$userId = (int) $user['id'];

// Escape strings manually since the Database class doesn't use PDO prep statements
function e_str($str)
{
    return addslashes($str);
}

// Get form parameters
$firstName      = e_str(trim($_POST['firstName'] ?? ''));
$lastName       = e_str(trim($_POST['lastName'] ?? ''));
$displayName    = e_str(trim($_POST['displayName'] ?? ''));
$email          = e_str(trim($_POST['email'] ?? ''));
$age            = !empty($_POST['age']) ? (int) $_POST['age'] : 'NULL';
$gender         = e_str(trim($_POST['gender'] ?? ''));
$phoneNumber    = e_str(trim($_POST['phoneNumber'] ?? ''));

$emergencyName  = e_str(trim($_POST['emergencyContactName'] ?? ''));
$emergencyPhone = e_str(trim($_POST['emergencyContactPhone'] ?? ''));
$sobrietyDate   = !empty($_POST['sobrietyStartDate']) ? "'" . e_str($_POST['sobrietyStartDate']) . "'" : 'NULL';
$recoveryType   = e_str(trim($_POST['recoveryType'] ?? ''));
$isAnonymous    = isset($_POST['isAnonymous']) ? 1 : 0;

// Handle Profile Picture Upload
$profilePicUpdate = "";
$uploadDir = ROOT . '/public/uploads/profiles';

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
    $tmpName = $_FILES['profilePicture']['tmp_name'];
    $fileName = basename($_FILES['profilePicture']['name']);
    $ext = pathinfo($fileName, PATHINFO_EXTENSION);

    // Generate unique name: profile_userId_timestamp.ext
    $newFileName = 'profile_' . $userId . '_' . time() . '.' . $ext;
    $destPath = $uploadDir . '/' . $newFileName;

    if (move_uploaded_file($tmpName, $destPath)) {
        // Build relative URL
        $profilePicUrl = '/uploads/profiles/' . $newFileName;
        $profilePicUpdate = ", profile_picture = '" . e_str($profilePicUrl) . "'";
    }
}

try {
    // 1. Update `users` table
    $queryUsers = "UPDATE users SET 
        first_name = '$firstName', 
        last_name = '$lastName', 
        display_name = '$displayName', 
        email = '$email', 
        age = $age, 
        gender = '$gender', 
        phone_number = '$phoneNumber'
        $profilePicUpdate
        WHERE user_id = $userId";

    Database::iud($queryUsers);

    // 2. Insert or Update `user_profiles` table
    $rs = Database::search("SELECT profile_id FROM user_profiles WHERE user_id = $userId");
    $hasProfile = $rs->fetch_assoc();

    if ($hasProfile) {
        $queryProfile = "UPDATE user_profiles SET
                emergency_contact_name = '$emergencyName',
                emergency_contact_phone = '$emergencyPhone',
                sobriety_start_date = $sobrietyDate,
                recovery_type = '$recoveryType',
                is_anonymous = $isAnonymous
             WHERE user_id = $userId";
        Database::iud($queryProfile);
    } else {
        $queryProfile = "INSERT INTO user_profiles
            (user_id, emergency_contact_name, emergency_contact_phone, sobriety_start_date, recovery_type, is_anonymous)
            VALUES
            ($userId, '$emergencyName', '$emergencyPhone', $sobrietyDate, '$recoveryType', $isAnonymous)";
        Database::iud($queryProfile);
    }

    // Refresh JWT Payload
    $newPayload = $user;
    $newPayload['name'] = $displayName ?: "$firstName $lastName";
    if ($profilePicUpdate !== "") {
        $newPayload['profilePictureUrl'] = $profilePicUrl;
    }

    $newToken = Auth::sign($newPayload);
    Auth::setTokenCookie($newToken);

    // Success - redirect back with query param
    Response::redirect('/user/dashboard?updateSuccess=true');
} catch (Exception $e) {
    error_log("Profile Update Error: " . $e->getMessage());
    Response::redirect('/user/dashboard?error=update_failed');
}
