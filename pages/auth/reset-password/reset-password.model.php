<?php

/**
 * Reset Password Model
 */
class ResetPasswordModel
{
    /**
     * Find a user whose reset token matches AND has not expired.
     * Returns the user row, or null if token is invalid/expired.
     */
    public static function findByToken(string $token): ?array
    {
        Database::setUpConnection();
        $safe = Database::$connection->real_escape_string($token);

        $rs = Database::search(
            "SELECT user_id, email, role
             FROM users
             WHERE password_reset_token   = '$safe'
               AND password_reset_expires > NOW()
               AND is_active = 1
             LIMIT 1"
        );

        $row = $rs->fetch_assoc();
        return $row ?: null;
    }

    /**
     * Update password hash and clear the reset token.
     */
    public static function updatePassword(int $userId, string $hash): void
    {
        Database::setUpConnection();
        $safeHash = Database::$connection->real_escape_string($hash);

        Database::iud(
            "UPDATE users
             SET password_hash          = '$safeHash',
                 password_reset_token   = NULL,
                 password_reset_expires = NULL
             WHERE user_id = $userId"
        );
    }
}
