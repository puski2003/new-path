<?php

class CheckoutModel
{
    public static function getUserDetails(int $userId): array
    {
        $rs = Database::search(
            "SELECT email, phone_number,
                    COALESCE(display_name, CONCAT(first_name,' ',last_name), username, 'User') AS display_name
             FROM users WHERE user_id = $userId LIMIT 1"
        );
        $row = $rs ? $rs->fetch_assoc() : null;
        return [
            'displayName' => $row['display_name'] ?? 'User',
            'email'       => $row['email'] ?? '',
            'phone'       => $row['phone_number'] ?? '',
        ];
    }
}
