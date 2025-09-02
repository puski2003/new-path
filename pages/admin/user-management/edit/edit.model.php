<?php
require_once __DIR__ . '/../user-management.model.php';

class EditUserModel
{
    public static function getUser(int $userId): ?array
    {
        return UserManagementModel::getUserById($userId);
    }

    public static function update(int $userId, array $input): array
    {
        return UserManagementModel::updateUser($userId, $input);
    }
}
