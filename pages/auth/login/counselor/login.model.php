<?php

/**
 * Counselor Login Model
 */
class LoginModel
{
    /**
     * Find a counselor user by email.
     * Counselors must have the 'counselor' role.
     */
    public static function findByEmail(string $email): ?array
    {
        Database::setUpConnection();
        $safeEmail = Database::$connection->real_escape_string($email);

        $rs = Database::search(
            "SELECT user_id, email, password_hash, role, display_name, first_name 
             FROM users 
             WHERE email = '$safeEmail' AND role = 'counselor'
             LIMIT 1"
        );

        $user = $rs->fetch_assoc();
        return $user ?: null;
    }
}
