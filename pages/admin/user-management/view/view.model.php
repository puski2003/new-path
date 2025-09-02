<?php
require_once __DIR__ . '/../user-management.model.php';

class ViewUserModel
{
    public static function getUser(int $userId): ?array
    {
        return UserManagementModel::getUserById($userId);
    }
}
