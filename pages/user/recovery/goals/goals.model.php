<?php

class GoalsModel
{
    public function getUserGoalsForActivePlan(int $userId): array
    {
        $rs = Database::search(
            "SELECT rg.goal_id, rg.title, rg.description, rg.goal_type,
                    rg.target_days, rg.current_progress, rg.status, rg.plan_id
             FROM recovery_goals rg
             INNER JOIN recovery_plans rp ON rp.plan_id = rg.plan_id
             WHERE rp.user_id = $userId
               AND rp.status = 'active'
               AND (rp.assigned_status IS NULL OR rp.assigned_status = 'accepted')
             ORDER BY rg.goal_type ASC, rg.goal_id ASC"
        );

        $goals = [];
        while ($row = $rs->fetch_assoc()) {
            $target  = max(1, (int)$row['target_days']);
            $current = max(0, (int)$row['current_progress']);
            $goals[] = [
                'goalId'             => (int)$row['goal_id'],
                'planId'             => (int)$row['plan_id'],
                'title'              => $row['title'],
                'description'        => $row['description'] ?? '',
                'goalType'           => $row['goal_type'],
                'targetDays'         => $target,
                'currentProgress'    => $current,
                'progressPercentage' => min(100, (int)round(($current / $target) * 100)),
                'status'             => $row['status'],
            ];
        }
        return $goals;
    }

    public function getUserActivePlans(int $userId): array
    {
        $rs = Database::search(
            "SELECT plan_id, title
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
                'title'  => $row['title'] ?? 'Recovery Plan',
            ];
        }
        return $plans;
    }
}
