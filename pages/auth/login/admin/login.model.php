<?php

/**
 * Admin Login Model
 */
class LoginModel
{
    /**
     * Find an admin user by email.
     * Admins must have the 'admin' role.
     */
    public static function findByEmail(string $email): ?array
    {
        Database::setUpConnection();
        $safeEmail = Database::$connection->real_escape_string($email);

        $rs = Database::search(
            "SELECT user_id, email, password_hash, role, display_name, first_name 
             FROM users 
             WHERE email = '$safeEmail' AND role = 'admin'
             LIMIT 1"
        );

        $user = $rs->fetch_assoc();
        return $user ?: null;
    }
}
