<?php
require_once __DIR__ . '/../support-groups.model.php';

class SupportGroupScheduleModel
{
    public static function getGroupsForDropdown(): array
    {
        return SupportGroupsModel::getGroupsForDropdown();
    }

    public static function createSession(array $input, int $adminUserId): bool
    {
        return SupportGroupsModel::createSession($input, $adminUserId);
    }
}
