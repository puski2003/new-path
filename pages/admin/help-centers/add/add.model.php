<?php
require_once __DIR__ . '/../help-center.model.php';

class AddHelpCenterModel
{
    public static function create(array $input, int $adminUserId): bool
    {
        return HelpCenterModel::createHelpCenter($input, $adminUserId);
    }
}
