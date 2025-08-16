<?php

/**
 * Forgot Password Model
 */
class ForgotPasswordModel
{
    /**
     * Find any user (any role) by email.
     */
    public static function findByEmail(string $email): ?array
    {
        Database::setUpConnection();
        $safe = Database::$connection->real_escape_string($email);

        $rs = Database::search(
            "SELECT user_id, email, role
             FROM users
             WHERE email = '$safe'
               AND is_active = 1
             LIMIT 1"
        );

        $row = $rs->fetch_assoc();
        return $row ?: null;
    }

    /**
     * Write reset token + expiry into the users row.
     */
    public static function setResetToken(int $userId, string $token): void
    {
        Database::setUpConnection();
        $safeToken = Database::$connection->real_escape_string($token);

        Database::iud(
            "UPDATE users
             SET password_reset_token   = '$safeToken',
                 password_reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR)
             WHERE user_id = $userId"
        );
    }
}
