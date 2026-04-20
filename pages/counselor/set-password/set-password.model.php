<?php

class SetPasswordModel
{
    public static function update(int $userId, string $newPassword): bool
    {
        Database::setUpConnection();
        $hash = Database::$connection->real_escape_string(password_hash($newPassword, PASSWORD_BCRYPT));

        Database::iud(
            "UPDATE users
             SET password_hash = '$hash', must_change_password = 0, updated_at = NOW()
             WHERE user_id = $userId AND role = 'counselor'"
        );

        return true;
    }
}
