<?php
require_once __DIR__ . '/../support-groups.model.php';

class SupportGroupsCreateModel
{
    public static function create(array $input, int $adminUserId): bool
    {
        return SupportGroupsModel::createGroup($input, $adminUserId);
    }
}
