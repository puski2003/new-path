<?php
require_once __DIR__ . '/../../../common/user.head.php';

if (!Request::isPost()) {
    Response::redirect('/user/recovery');
}

$userId  = (int)$user['id'];
$ecName  = addslashes(trim(Request::post('ecName')  ?? ''));
$ecPhone = addslashes(trim(Request::post('ecPhone') ?? ''));

if ($ecName === '' || $ecPhone === '') {
    Response::redirect('/user/recovery?ecError=1');
}

// Upsert into user_profiles
$exists = Database::search("SELECT profile_id FROM user_profiles WHERE user_id = $userId LIMIT 1");
if ($exists && $exists->num_rows > 0) {
    Database::iud(
        "UPDATE user_profiles
         SET emergency_contact_name = '$ecName', emergency_contact_phone = '$ecPhone', updated_at = NOW()
         WHERE user_id = $userId"
    );
} else {
    Database::iud(
        "INSERT INTO user_profiles (user_id, emergency_contact_name, emergency_contact_phone, created_at, updated_at)
         VALUES ($userId, '$ecName', '$ecPhone', NOW(), NOW())"
    );
}

Response::redirect('/user/recovery?ecSaved=1');
