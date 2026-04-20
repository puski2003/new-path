<?php

class ManageModel
{
    public static function getActivePlans(int $userId): array
    {
        $rs = Database::search(
            "SELECT plan_id, title, description, progress_percentage, assigned_status, counselor_id, source_plan_id
             FROM recovery_plans
             WHERE user_id = $userId
               AND status = 'active'
               AND (assigned_status IS NULL OR assigned_status = 'accepted')
             ORDER BY updated_at DESC"
        );

        $plans = [];
        while ($row = $rs->fetch_assoc()) {
            $plans[] = [
                'planId' => (int)$row['plan_id'],
                'title' => $row['title'] ?? 'Recovery Plan',
                'description' => $row['description'] ?? '',
                'progressPercentage' => (int)($row['progress_percentage'] ?? 0),
                'assignedStatus' => $row['assigned_status'] ?? null,
                'counselorId'  => isset($row['counselor_id'])   ? (int)$row['counselor_id']   : null,
                'sourcePlanId' => isset($row['source_plan_id']) ? (int)$row['source_plan_id'] : null,
            ];
        }
        return $plans;
    }

    public static function getAssignedPlans(int $userId): array
    {
        $rs = Database::search(
            "SELECT plan_id, title, description, progress_percentage, counselor_id, source_plan_id
             FROM recovery_plans
             WHERE user_id = $userId
               AND assigned_status = 'pending'
             ORDER BY updated_at DESC"
        );

        $plans = [];
        while ($row = $rs->fetch_assoc()) {
            $plans[] = [
                'planId'             => (int)$row['plan_id'],
                'title'              => $row['title']              ?? 'Recovery Plan',
                'description'        => $row['description']        ?? '',
                'progressPercentage' => (int)($row['progress_percentage'] ?? 0),
                'counselorId'        => isset($row['counselor_id'])   ? (int)$row['counselor_id']   : null,
                'sourcePlanId'       => isset($row['source_plan_id']) ? (int)$row['source_plan_id'] : null,
            ];
        }
        return $plans;
    }

    public static function getPausedPlans(int $userId): array
    {
        $rs = Database::search(
            "SELECT plan_id, title, description, progress_percentage, counselor_id, source_plan_id
             FROM recovery_plans
             WHERE user_id = $userId
               AND status = 'paused'
             ORDER BY updated_at DESC"
        );

        $plans = [];
        while ($row = $rs->fetch_assoc()) {
            $plans[] = [
                'planId'             => (int)$row['plan_id'],
                'title'              => $row['title']              ?? 'Recovery Plan',
                'description'        => $row['description']        ?? '',
                'progressPercentage' => (int)($row['progress_percentage'] ?? 0),
                'counselorId'        => isset($row['counselor_id'])   ? (int)$row['counselor_id']   : null,
                'sourcePlanId'       => isset($row['source_plan_id']) ? (int)$row['source_plan_id'] : null,
            ];
        }
        return $plans;
    }
}