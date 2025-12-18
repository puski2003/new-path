<?php

require_once __DIR__ . '/../../common/counselor.data.php';

class CounselorRecoveryCreateModel
{
    public static function getClients(int $counselorId): array
    {
        return CounselorData::getClientsByCounselor($counselorId);
    }

    public static function create(int $counselorId, array $input): bool
    {
        return CounselorData::createPlan($counselorId, $input);
    }
}
