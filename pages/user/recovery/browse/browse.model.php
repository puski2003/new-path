<?php

class BrowseModel
{
    public function getSystemPlans(): array
    {
        $rs = Database::search(
            "SELECT plan_id, title, description, category, image, goal,
                    short_term_goal_title, short_term_goal_days,
                    long_term_goal_title,  long_term_goal_days
             FROM system_plans
             ORDER BY updated_at DESC"
        );

        $plans = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $plans[] = [
                'planId'      => (int)$row['plan_id'],
                'title'       => $row['title']       ?? 'Recovery Plan',
                'description' => $row['description'] ?? '',
                'category'    => $row['category']    ?? 'General',
                'image'       => $row['image']       ?? '',
                'goal'        => $row['goal']        ?? '',
            ];
        }
        return $plans;
    }

    public function getUserActivePlans(int $userId): array
    {
        $rs = Database::search(
            "SELECT plan_id, title, description, progress_percentage, assigned_status, counselor_id
             FROM recovery_plans
             WHERE user_id = $userId
               AND status = 'active'
               AND (assigned_status IS NULL OR assigned_status = 'accepted')
             ORDER BY updated_at DESC"
        );

        $plans = [];
        while ($row = $rs->fetch_assoc()) {
            $plans[] = [
                'planId'             => (int)$row['plan_id'],
                'title'              => $row['title']              ?? 'Recovery Plan',
                'description'        => $row['description']        ?? '',
                'progressPercentage' => (int)($row['progress_percentage'] ?? 0),
                'assignedStatus'     => $row['assigned_status']    ?? null,
                'counselorId'        => isset($row['counselor_id']) ? (int)$row['counselor_id'] : null,
            ];
        }
        return $plans;
    }
}
