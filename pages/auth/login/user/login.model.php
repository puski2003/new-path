<?php

/**
 * Login Model — queries the DB to find a user by email.
 */
class LoginModel
{
    public static function findByEmail(string $email): ?array
    {
        Database::setUpConnection();
        $safeEmail = Database::$connection->real_escape_string($email);

        $rs = Database::search(
            "SELECT user_id, email, password_hash, role, display_name, first_name, onboarding_completed, current_onboarding_step
             FROM users
             WHERE email = '$safeEmail'
             LIMIT 1"
        );

        $user = $rs->fetch_assoc();
        return $user ?: null;
    }
}
