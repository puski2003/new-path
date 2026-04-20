<?php

class EmergencyContactModel
{
    public static function saveEmergencyContact(int $userId, string $name, string $phone): bool
    {
        if (trim($name) === '' || trim($phone) === '') {
            return false;
        }

        Database::setUpConnection();
        $conn = Database::$connection;
        $safeName = $conn->real_escape_string(trim($name));
        $safePhone = $conn->real_escape_string(trim($phone));

        $exists = Database::search("SELECT profile_id FROM user_profiles WHERE user_id = $userId LIMIT 1");
        if ($exists && $exists->num_rows > 0) {
            Database::iud(
                "UPDATE user_profiles
                 SET emergency_contact_name = '$safeName', emergency_contact_phone = '$safePhone', updated_at = NOW()
                 WHERE user_id = $userId"
            );
        } else {
            Database::iud(
                "INSERT INTO user_profiles (user_id, emergency_contact_name, emergency_contact_phone, created_at, updated_at)
                 VALUES ($userId, '$safeName', '$safePhone', NOW(), NOW())"
            );
        }

        return true;
    }
}