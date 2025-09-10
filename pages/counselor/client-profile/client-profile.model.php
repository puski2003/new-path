<?php

require_once __DIR__ . '/../common/counselor.data.php';

class CounselorClientProfileModel
{
    public static function getProfile(int $counselorId, int $clientUserId): ?array
    {
        return CounselorData::getClientProfile($counselorId, $clientUserId);
    }
}
