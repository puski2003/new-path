<?php

/**
 * Step 1 Model: Create the user.
 */
class Step1Model
{
    public static function emailExists(string $email): bool
    {
        Database::setUpConnection();
        $safeEmail = Database::$connection->real_escape_string($email);

        $rs = Database::search("SELECT 1 FROM users WHERE email = '$safeEmail' LIMIT 1");
        return $rs->num_rows > 0;
    }

    public static function createUser(string $email, string $password, string $displayName, ?int $age, ?string $gender): ?array
    {
        Database::setUpConnection();

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $salt = password_hash(random_bytes(16), PASSWORD_BCRYPT);

        $safeEmail       = Database::$connection->real_escape_string($email);
        $safeHash        = Database::$connection->real_escape_string($hash);
        $safeSalt        = Database::$connection->real_escape_string($salt);
        $safeDisplayName = Database::$connection->real_escape_string($displayName);
        $safeAge         = $age !== null ? (int)$age : 'NULL';
        $safeGender      = $gender !== null ? "'" . Database::$connection->real_escape_string($gender) . "'" : 'NULL';

        $ageValue = $age !== null ? (int)$age : 'NULL';

        Database::iud(
            "INSERT INTO users (email, password_hash, salt, display_name, age, gender, role, onboarding_completed, current_onboarding_step) 
             VALUES ('$safeEmail', '$safeHash', '$safeSalt', '$safeDisplayName', $ageValue, $safeGender, 'user', 0, 2)"
        );

        $id = Database::$connection->insert_id;

        // Fetch the newly created user
        $rs = Database::search("SELECT user_id, email, display_name, role FROM users WHERE user_id = $id");
        $user = $rs->fetch_assoc();
        return $user ?: null;
    }
}
