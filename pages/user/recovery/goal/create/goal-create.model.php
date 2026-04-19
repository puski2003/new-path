<?php

class GoalCreateModel
{
    public static function create(int $userId, string $title, string $goalType, int $targetDays, string $description): bool
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

        $planId = (int)$planRs->fetch_assoc()['plan_id'];
        Database::setUpConnection();
        $conn = Database::$connection;
        $safeTitle = $conn->real_escape_string($title);
        $safeDesc = $conn->real_escape_string($description);
        $safeType = in_array($goalType, ['short_term', 'long_term']) ? $goalType : 'short_term';

        Database::iud(
            "INSERT INTO recovery_goals (plan_id, goal_type, title, description, target_days, current_progress, status, created_at, updated_at)
             VALUES ($planId, '$safeType', '$safeTitle', '$safeDesc', $targetDays, 0, 'in_progress', NOW(), NOW())"
        );
        return true;
    }
}