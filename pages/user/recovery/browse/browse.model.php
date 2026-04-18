<?php

require_once __DIR__ . '/../recovery.model.php';

class BrowseModel
{
    public static function getSystemPlans(): array
    {
        return RecoveryModel::getSystemPlans();
    }

    public static function getUserActivePlans(int $userId): array
    {
        return RecoveryModel::getUserActivePlans($userId);
    }
}