<?php

require_once __DIR__ . '/../common/counselor.data.php';

class CounselorClientsModel
{
    public static function getAll(int $counselorId): array
    {
        return CounselorData::getClientsByCounselor($counselorId);
    }
}
