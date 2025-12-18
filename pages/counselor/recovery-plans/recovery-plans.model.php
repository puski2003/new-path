<?php

require_once __DIR__ . '/../common/counselor.data.php';

class CounselorRecoveryPlansModel
{
    public static function getAll(int $counselorId): array
    {
        return CounselorData::getPlansByCounselor($counselorId);
    }

    public static function getPendingChangeRequests(int $counselorId): array
    {
        return CounselorData::getChangeRequestsForCounselor($counselorId);
    }
}
