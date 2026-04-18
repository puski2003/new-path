<?php

trait GoalModel
{
    public static function getUserGoalsForActivePlan(int $userId): array
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
                'goalId'      => (int)$row['goal_id'],
                'planId'      => (int)$row['plan_id'],
                'title'       => $row['title'],
                'description' => $row['description'] ?? '',
                'goalType'    => $row['goal_type'],
                'targetDays'  => $target,
                'currentProgress' => $current,
                'progressPercentage' => min(100, (int)round(($current / $target) * 100)),
                'status'      => $row['status'],
            ];
        }
        return $goals;
    }

    public static function createGoal(int $userId, string $title, string $goalType, int $targetDays, string $description): bool
    {
        if ($userId <= 0 || trim($title) === '' || $targetDays <= 0) return false;

        $planRs = Database::search(
            "SELECT plan_id FROM recovery_plans
             WHERE user_id = $userId
               AND status = 'active'
               AND is_template = 0
               AND (assigned_status IS NULL OR assigned_status = 'accepted')
             LIMIT 1"
        );
        if (!$planRs || $planRs->num_rows === 0) return false;

        $planId      = (int)$planRs->fetch_assoc()['plan_id'];
        $safeTitle   = addslashes($title);
        $safeDesc    = addslashes($description);
        $safeType    = in_array($goalType, ['short_term', 'long_term']) ? $goalType : 'short_term';

        Database::iud(
            "INSERT INTO recovery_goals (plan_id, goal_type, title, description, target_days, current_progress, status, created_at, updated_at)
             VALUES ($planId, '$safeType', '$safeTitle', '$safeDesc', $targetDays, 0, 'in_progress', NOW(), NOW())"
        );
        return true;
    }

    public static function updateGoal(int $goalId, int $userId, string $title, string $goalType, int $targetDays, string $description): bool
    {
        if ($goalId <= 0 || $userId <= 0 || trim($title) === '' || $targetDays <= 0) return false;

        $safeTitle = addslashes($title);
        $safeDesc  = addslashes($description);
        $safeType  = in_array($goalType, ['short_term', 'long_term']) ? $goalType : 'short_term';

        Database::iud(
            "UPDATE recovery_goals rg
             INNER JOIN recovery_plans rp ON rp.plan_id = rg.plan_id
             SET rg.title = '$safeTitle',
                 rg.description = '$safeDesc',
                 rg.goal_type = '$safeType',
                 rg.target_days = $targetDays,
                 rg.updated_at = NOW()
             WHERE rg.goal_id = $goalId
               AND rp.user_id = $userId"
        );
        return true;
    }

    public static function deleteGoal(int $goalId, int $userId): bool
    {
        if ($goalId <= 0 || $userId <= 0) return false;

        Database::iud(
            "DELETE rg FROM recovery_goals rg
             INNER JOIN recovery_plans rp ON rp.plan_id = rg.plan_id
             WHERE rg.goal_id = $goalId
               AND rp.user_id = $userId"
        );
        return true;
    }

    public static function logGoalProgress(int $goalId, int $userId, int $days = 1): bool
    {
        if ($goalId <= 0 || $userId <= 0 || $days <= 0) return false;

        $rs = Database::search(
            "SELECT rg.current_progress, rg.target_days, rg.status
             FROM recovery_goals rg
             INNER JOIN recovery_plans rp ON rp.plan_id = rg.plan_id
             WHERE rg.goal_id = $goalId AND rp.user_id = $userId
             LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;
        $row = $rs->fetch_assoc();
        if ($row['status'] === 'achieved') return true;

        $newProgress = min((int)$row['current_progress'] + $days, (int)$row['target_days']);
        $achieved    = $newProgress >= (int)$row['target_days'];
        $status      = $achieved ? 'achieved' : 'in_progress';
        $achievedAt  = $achieved ? ', achieved_at = NOW()' : '';

        Database::iud(
            "UPDATE recovery_goals
             SET current_progress = $newProgress,
                 status = '$status'
                 $achievedAt,
                 updated_at = NOW()
             WHERE goal_id = $goalId"
        );
        return true;
    }

    public static function getGoalsByPlanId(int $planId): array
    {
        $rs = Database::search(
            "SELECT goal_type, title, target_days, current_progress
             FROM recovery_goals
             WHERE plan_id = $planId
             ORDER BY goal_id ASC"
        );

        $goals = [];
        while ($row = $rs->fetch_assoc()) {
            $targetDays = max(1, (int)($row['target_days'] ?? 0));
            $current = max(0, (int)($row['current_progress'] ?? 0));
            $goals[] = [
                'goalType' => $row['goal_type'] ?? 'short_term',
                'title' => $row['title'] ?? 'Goal',
                'targetDays' => $targetDays,
                'currentProgress' => $current,
                'progressPercentage' => min(100, (int)round(($current / $targetDays) * 100)),
            ];
        }

        return $goals;
    }
}