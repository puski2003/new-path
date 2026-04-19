<?php

class PlanCompletedModel
{
    public static function getPlanDetails(int $planId, int $userId): ?array
    {
        $rs = Database::search(
            "SELECT rp.plan_id, rp.title, rp.category, rp.plan_type, rp.counselor_id,
                    rp.start_date, rp.actual_completion_date,
                    GREATEST(1, DATEDIFF(COALESCE(rp.actual_completion_date, CURDATE()), rp.start_date) + 1) AS days_taken,
                    COALESCE(cu.display_name, CONCAT(cu.first_name, ' ', cu.last_name)) AS counselor_name
             FROM recovery_plans rp
             LEFT JOIN counselors c  ON c.counselor_id  = rp.counselor_id
             LEFT JOIN users cu      ON cu.user_id       = c.user_id
             WHERE rp.plan_id = $planId
               AND rp.user_id = $userId
               AND rp.status  = 'completed'
             LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return null;
        $row = $rs->fetch_assoc();

        return [
            'planId' => (int)$row['plan_id'],
            'title' => $row['title'] ?? 'Recovery Plan',
            'category' => $row['category'] ?? 'General',
            'planType' => $row['plan_type'] ?? 'self',
            'counselorId' => $row['counselor_id'] ? (int)$row['counselor_id'] : null,
            'counselorName' => $row['counselor_name'] ?? null,
            'startDate' => $row['start_date'] ?? null,
            'completionDate' => $row['actual_completion_date'] ?? null,
            'daysTaken' => (int)$row['days_taken'],
        ];
    }

    public static function getStats(int $userId): array
    {
        $rs = Database::search(
            "SELECT sobriety_start_date, days_sober, total_days_tracked, total_sessions_completed
             FROM user_progress
             WHERE user_id = $userId
             LIMIT 1"
        );

        $row = $rs ? $rs->fetch_assoc() : null;

        $startDate = $row['sobriety_start_date'] ?? null;
        $daysSober = $startDate ? (int)((time() - strtotime($startDate)) / 86400) + 1 : 0;
        $daysSober = max(0, $daysSober);

        $urgesLogged = 0;
        $urgeRs = Database::search("SELECT COUNT(*) AS cnt FROM urge_logs WHERE user_id = $userId AND outcome = 'resisted'");
        if ($urgeRs) {
            $urgesLogged = (int)($urgeRs->fetch_assoc()['cnt'] ?? 0);
        }

        return [
            'daysSober' => $daysSober,
            'totalDaysTracked' => (int)($row['total_days_tracked'] ?? 0),
            'urgesLogged' => $urgesLogged,
            'sessionsCompleted' => (int)($row['total_sessions_completed'] ?? 0),
            'trackingStarted' => $startDate !== null,
        ];
    }
}