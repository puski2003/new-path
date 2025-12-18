<?php

require_once __DIR__ . '/../../common/counselor.data.php';

class CounselorRecoveryViewModel
{
    public static function getClients(int $counselorId): array
    {
        return CounselorData::getClientsByCounselor($counselorId);
    }

    public static function getPlan(int $counselorId, int $planId): ?array
    {
        return CounselorData::getPlanById($counselorId, $planId);
    }

    public static function getTasks(int $planId): array
    {
        return CounselorData::getTasksByPlanId($planId);
    }

    public static function getGoals(int $planId): array
    {
        return CounselorData::getGoalsByPlanId($planId);
    }

    public static function update(int $counselorId, int $planId, array $input): bool
    {
        return CounselorData::updatePlan($counselorId, $planId, $input);
    }
}
