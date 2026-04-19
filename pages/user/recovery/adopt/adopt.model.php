<?php

class AdoptModel
{
    public static function adopt(int $systemPlanId, int $userId): bool
    {
        if ($systemPlanId <= 0 || $userId <= 0) return false;

        Database::iud(
            "UPDATE recovery_plans
             SET status = 'paused', updated_at = NOW()
             WHERE user_id = $userId AND status = 'active' AND is_template = 0"
        );

        $spRs = Database::search(
            "SELECT * FROM system_plans WHERE plan_id = $systemPlanId LIMIT 1"
        );
        if (!$spRs || $spRs->num_rows === 0) return false;

        $sp = $spRs->fetch_assoc();
        Database::setUpConnection();
        $conn = Database::$connection;

        $title = $conn->real_escape_string($sp['title'] ?? 'Recovery Plan');
        $description = $conn->real_escape_string($sp['description'] ?? '');
        $category = $conn->real_escape_string($sp['category'] ?? 'General');
        $startDate = !empty($sp['start_date']) ? "'" . $conn->real_escape_string($sp['start_date']) . "'" : 'CURDATE()';

        Database::iud(
            "INSERT INTO recovery_plans
                (user_id, title, description, category, plan_type, status,
                 start_date, progress_percentage,
                 is_template, assigned_status, source_plan_id, created_at, updated_at)
             VALUES
                ($userId, '$title', '$description', '$category', 'self', 'active',
                 $startDate, 0,
                 0, 'accepted', $systemPlanId, NOW(), NOW())"
        );

        $newPlanId = (int)$conn->insert_id;
        if ($newPlanId <= 0) return false;

        $stTitle = $conn->real_escape_string($sp['short_term_goal_title'] ?? '');
        $stDays = max(1, (int)($sp['short_term_goal_days'] ?? 30));
        $ltTitle = $conn->real_escape_string($sp['long_term_goal_title'] ?? '');
        $ltDays = max(1, (int)($sp['long_term_goal_days'] ?? 90));

        if ($stTitle !== '') {
            Database::iud(
                "INSERT INTO recovery_goals (plan_id, goal_type, title, target_days, current_progress, status, created_at, updated_at)
                 VALUES ($newPlanId, 'short_term', '$stTitle', $stDays, 0, 'in_progress', NOW(), NOW())"
            );
        }
        if ($ltTitle !== '') {
            Database::iud(
                "INSERT INTO recovery_goals (plan_id, goal_type, title, target_days, current_progress, status, created_at, updated_at)
                 VALUES ($newPlanId, 'long_term', '$ltTitle', $ltDays, 0, 'in_progress', NOW(), NOW())"
            );
        }

        $tasksRs = Database::search(
            "SELECT * FROM system_plan_tasks WHERE plan_id = $systemPlanId ORDER BY phase, sort_order"
        );
        while ($tasksRs && ($t = $tasksRs->fetch_assoc())) {
            $tTitle = $conn->real_escape_string($t['title'] ?? '');
            $tType = $conn->real_escape_string($t['task_type'] ?? 'custom');
            $tPriority = $conn->real_escape_string($t['priority'] ?? 'medium');
            $tPhase = (int)($t['phase'] ?? 1);
            $tRecurring = (int)($t['is_recurring'] ?? 0);
            $tPattern = $t['recurrence_pattern'] ? "'" . $conn->real_escape_string($t['recurrence_pattern']) . "'" : 'NULL';
            $tOrder = (int)($t['sort_order'] ?? 0);
            $tMilestone = (int)($t['is_milestone'] ?? 0);

            if (!$tMilestone) {
                Database::iud(
                    "INSERT INTO recovery_tasks
                        (plan_id, title, task_type, status, priority, phase,
                         is_recurring, recurrence_pattern, sort_order, created_at, updated_at)
                     VALUES
                        ($newPlanId, '$tTitle', '$tType', 'pending', '$tPriority', $tPhase,
                         $tRecurring, $tPattern, $tOrder, NOW(), NOW())"
                );
            }
        }

        return true;
    }
}