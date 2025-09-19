<?php
require_once __DIR__ . '/../help-center.model.php';

class EditHelpCenterModel
{
    public static function getCenter(int $helpCenterId): ?array
    {
        return HelpCenterModel::getHelpCenterById($helpCenterId);
    }

    public static function update(int $helpCenterId, array $input): bool
    {
        return HelpCenterModel::updateHelpCenter($helpCenterId, $input);
    }
}
