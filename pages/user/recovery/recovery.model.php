<?php

class RecoveryModel
{
    public static function getUserDailyTasks(int $userId): array
    {
        $rs = Database::search(
            "SELECT rt.task_id, rt.title, rt.status, rt.priority, rt.task_type, rt.due_date, rt.phase
             FROM recovery_tasks rt
             INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             WHERE rp.user_id = $userId
               AND rp.status = 'active'
               AND (rp.assigned_status IS NULL OR rp.assigned_status = 'accepted')
               AND rt.phase = (
                   SELECT MIN(rt2.phase)
                   FROM recovery_tasks rt2
                   WHERE rt2.plan_id = rp.plan_id
                     AND rt2.status <> 'completed'
               )
             ORDER BY rt.status ASC, rt.priority DESC, rt.sort_order ASC"
        );

        $tasks = [];
        while ($row = $rs->fetch_assoc()) {
            $tasks[] = [
                'taskId'   => (int)$row['task_id'],
                'title'    => $row['title'] ?? 'Task',
                'status'   => $row['status'] ?? 'pending',
                'priority' => $row['priority'] ?? 'medium',
                'taskType' => $row['task_type'] ?? 'custom',
                'phase'    => (int)$row['phase'],
                'dueDate'  => !empty($row['due_date']) ? date('M j', strtotime($row['due_date'])) : null,
            ];
        }
        return $tasks;
    }

    public static function getUserTaskStats(int $userId): array
    {
        $rs = Database::search(
            "SELECT
                SUM(CASE WHEN rt.status = 'completed' THEN 1 ELSE 0 END) AS completed_count,
                SUM(CASE WHEN rt.status <> 'completed' THEN 1 ELSE 0 END) AS pending_count
             FROM recovery_tasks rt
             INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             WHERE rp.user_id = $userId
               AND rp.status = 'active'"
        );

        $row = $rs->fetch_assoc();
        return [
            'completed' => isset($row['completed_count']) ? (int)$row['completed_count'] : 0,
            'pending' => isset($row['pending_count']) ? (int)$row['pending_count'] : 0,
        ];
    }

    public static function getUserActivePlans(int $userId): array
    {
        // Only return plans that are genuinely active for the user to work on.
        // Counselor-assigned plans that are still 'pending' acceptance must NOT appear
        // here — they belong in the pending/assigned list until the user accepts them.
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
                'planId' => (int)$row['plan_id'],
                'title' => $row['title'] ?? 'Recovery Plan',
                'description' => $row['description'] ?? '',
                'progressPercentage' => (int)($row['progress_percentage'] ?? 0),
                'assignedStatus' => $row['assigned_status'] ?? null,
                'counselorId' => isset($row['counselor_id']) ? (int)$row['counselor_id'] : null,
            ];
        }

        return $plans;
    }

    public static function getAssignedPlansForUser(int $userId): array
    {
        $rs = Database::search(
            "SELECT plan_id, title, description, progress_percentage
             FROM recovery_plans
             WHERE user_id = $userId
               AND assigned_status = 'pending'
             ORDER BY updated_at DESC"
        );

        $plans = [];
        while ($row = $rs->fetch_assoc()) {
            $plans[] = [
                'planId' => (int)$row['plan_id'],
                'title' => $row['title'] ?? 'Recovery Plan',
                'description' => $row['description'] ?? '',
                'progressPercentage' => (int)($row['progress_percentage'] ?? 0),
            ];
        }

        return $plans;
    }

    public static function getUserPausedPlans(int $userId): array
    {
        $rs = Database::search(
            "SELECT plan_id, title, description, progress_percentage
             FROM recovery_plans
             WHERE user_id = $userId
               AND status = 'paused'
             ORDER BY updated_at DESC"
        );

        $plans = [];
        while ($row = $rs->fetch_assoc()) {
            $plans[] = [
                'planId' => (int)$row['plan_id'],
                'title' => $row['title'] ?? 'Recovery Plan',
                'description' => $row['description'] ?? '',
                'progressPercentage' => (int)($row['progress_percentage'] ?? 0),
            ];
        }
        return $plans;
    }

    // ── User Goal Management ─────────────────────────────────────────

    /**
     * Get all goals for the user's active plan.
     */
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

    /**
     * Create a goal tied to the user's current active plan.
     * Returns false if the user has no active plan.
     */
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

    /**
     * Update a goal — verifies ownership via plan join.
     */
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

    /**
     * Delete a goal — verifies ownership via plan join.
     */
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

    /**
     * Log progress on a goal (+N days).
     * Auto-marks achieved when current_progress >= target_days.
     */
    public static function logGoalProgress(int $goalId, int $userId, int $days = 1): bool
    {
        if ($goalId <= 0 || $userId <= 0 || $days <= 0) return false;

        // Verify ownership and get current state
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

    // ── End Goal Management ──────────────────────────────────────────

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

    public static function getProgressStats(int $userId): array
    {
        $stats = [
            'daysSober'       => 0,
            'totalDaysTracked' => 0,
            'urgesLogged'     => 0,
            'sessionsCompleted' => 0,
            'trackingStarted' => false,
        ];

        // Always compute days_sober live from sobriety_start_date — this is the
        // source of truth. user_progress rows can be stale (days_sober = 0 from
        // when tracking first started) and must not override the real count.
        $rsProfile = Database::search(
            "SELECT DATEDIFF(CURDATE(), sobriety_start_date) AS days
             FROM user_profiles
             WHERE user_id = $userId
               AND sobriety_start_date IS NOT NULL
             LIMIT 1"
        );
        if ($p = $rsProfile->fetch_assoc()) {
            $stats['daysSober'] = max(0, (int)($p['days'] ?? 0));
        }

        // totalDaysTracked = number of distinct days the user has logged a progress entry
        $rsTracked = Database::search(
            "SELECT COUNT(DISTINCT date) AS tracked
             FROM user_progress
             WHERE user_id = $userId"
        );
        if ($t = $rsTracked->fetch_assoc()) {
            $stats['totalDaysTracked'] = max($stats['daysSober'], (int)($t['tracked'] ?? 0));
        } else {
            $stats['totalDaysTracked'] = $stats['daysSober'];
        }

        $rsUrges = Database::search("SELECT COUNT(*) AS urge_count FROM urge_logs WHERE user_id = $userId");
        if ($u = $rsUrges->fetch_assoc()) {
            $stats['urgesLogged'] = (int)($u['urge_count'] ?? 0);
        }

        $rsSessions = Database::search(
            "SELECT COUNT(*) AS session_count
             FROM sessions
             WHERE user_id = $userId
               AND status = 'completed'"
        );
        if ($s = $rsSessions->fetch_assoc()) {
            $stats['sessionsCompleted'] = (int)($s['session_count'] ?? 0);
        }

        $rsStarted = Database::search(
            "SELECT sobriety_start_date FROM user_profiles
             WHERE user_id = $userId LIMIT 1"
        );
        if ($row = $rsStarted->fetch_assoc()) {
            $stats['trackingStarted'] = $row['sobriety_start_date'] !== null;
        }

        return $stats;
    }

    public static function getUrgeLogs(int $userId, int $page = 1, int $limit = 15): array
    {
        $offset = ($page - 1) * $limit;

        $countRs = Database::search("SELECT COUNT(*) AS total FROM urge_logs WHERE user_id = $userId");
        $total   = (int)($countRs->fetch_assoc()['total'] ?? 0);

        $rs = Database::search(
            "SELECT urge_id, intensity, trigger_category, coping_strategy_used, outcome, notes, logged_at
             FROM urge_logs
             WHERE user_id = $userId
             ORDER BY logged_at DESC
             LIMIT $limit OFFSET $offset"
        );

        $logs = [];
        while ($row = $rs->fetch_assoc()) {
            $logs[] = [
                'urgeId'          => (int)$row['urge_id'],
                'intensity'       => (int)$row['intensity'],
                'triggerCategory' => $row['trigger_category'] ?? '',
                'copingStrategy'  => $row['coping_strategy_used'] ?? '',
                'outcome'         => $row['outcome'] ?? '',
                'notes'           => $row['notes'] ?? '',
                'loggedAt'        => date('M j, Y g:i A', strtotime($row['logged_at'])),
            ];
        }

        return [
            'logs'       => $logs,
            'total'      => $total,
            'totalPages' => max(1, (int)ceil($total / $limit)),
            'page'       => $page,
        ];
    }

    public static function getNextSessionSummary(int $userId): array
    {
        $rs = Database::search(
            "SELECT s.session_datetime,
                    COALESCE(u.display_name, CONCAT(u.first_name, ' ', u.last_name)) AS counselor_name
             FROM sessions s
             INNER JOIN counselors c ON c.counselor_id = s.counselor_id
             INNER JOIN users u ON u.user_id = c.user_id
             WHERE s.user_id = $userId
               AND s.session_datetime >= NOW()
               AND s.status IN ('scheduled', 'confirmed')
             ORDER BY s.session_datetime ASC
             LIMIT 1"
        );

        if ($row = $rs->fetch_assoc()) {
            return [
                'time' => date('M j, g:i A', strtotime($row['session_datetime'])),
                'counselorName' => $row['counselor_name'] ?? 'Counselor',
            ];
        }

        return [
            'time' => 'No upcoming sessions',
            'counselorName' => 'Counselor',
        ];
    }

    public static function getPlanByIdForUser(int $planId, int $userId): ?array
    {
        $rs = Database::search(
            "SELECT plan_id, title, description, plan_type, status, assigned_status, progress_percentage, counselor_id
             FROM recovery_plans
             WHERE plan_id = $planId
               AND user_id = $userId
             LIMIT 1"
        );

        if (!$rs || $rs->num_rows === 0) {
            return null;
        }

        $row = $rs->fetch_assoc();
        return [
            'planId' => (int)$row['plan_id'],
            'title' => $row['title'] ?? 'Recovery Plan',
            'description' => $row['description'] ?? '',
            'planType' => $row['plan_type'] ?? 'self',
            'status' => $row['status'] ?? 'draft',
            'assignedStatus' => $row['assigned_status'] ?? null,
            'progressPercentage' => (int)($row['progress_percentage'] ?? 0),
            'counselorId' => isset($row['counselor_id']) ? (int)$row['counselor_id'] : null,
        ];
    }

    public static function acceptAssignedPlan(int $planId, int $userId): bool
    {
        if ($planId <= 0 || $userId <= 0) return false;

        // Pause any currently active plan — the new plan takes over immediately.
        Database::iud(
            "UPDATE recovery_plans
             SET status = 'paused', updated_at = NOW()
             WHERE user_id = $userId
               AND status = 'active'
               AND is_template = 0
               AND plan_id <> $planId"
        );

        Database::iud(
            "UPDATE recovery_plans
             SET assigned_status = 'accepted',
                 status = 'active',
                 start_date = COALESCE(start_date, CURDATE()),
                 updated_at = NOW()
             WHERE plan_id = $planId
               AND user_id = $userId
               AND assigned_status = 'pending'"
        );

        return true;
    }

    public static function rejectAssignedPlan(int $planId, int $userId): bool
    {
        if ($planId <= 0 || $userId <= 0) return false;

        // Per PRD §2.3: assigned_status → 'rejected'.
        // Also revert status to 'draft' so the plan is no longer shown as active
        // for the user — the counselor can revise and re-publish if needed.
        Database::iud(
            "UPDATE recovery_plans
             SET assigned_status = 'rejected',
                 status = 'draft',
                 updated_at = NOW()
             WHERE plan_id = $planId
               AND user_id = $userId
               AND assigned_status = 'pending'"
        );

        return true;
    }

    public static function completeTask(int $taskId, int $userId): bool
    {
        if ($taskId <= 0 || $userId <= 0) return false;

        // 1. Verify task belongs to this user and get phase/plan info
        $taskRs = Database::search(
            "SELECT rt.phase, rt.plan_id, rt.status
             FROM recovery_tasks rt
             INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             WHERE rt.task_id = $taskId AND rp.user_id = $userId
             LIMIT 1"
        );
        if (!$taskRs || $taskRs->num_rows === 0) return false;
        $taskRow = $taskRs->fetch_assoc();

        if ($taskRow['status'] === 'completed') return true;

        $currentPhase = (int)$taskRow['phase'];
        $planId       = (int)$taskRow['plan_id'];

        // 2. Phase-lock: block if an earlier phase has incomplete tasks
        if ($currentPhase > 1) {
            $blockRs = Database::search(
                "SELECT COUNT(*) AS blocked
                 FROM recovery_tasks
                 WHERE plan_id = $planId
                   AND phase < $currentPhase
                   AND status <> 'completed'"
            );
            $blockRow = $blockRs->fetch_assoc();
            if ((int)($blockRow['blocked'] ?? 0) > 0) {
                return false; // caller shows error
            }
        }

        // 3. Mark complete (existing logic preserved)
        Database::iud(
            "UPDATE recovery_tasks
             SET status = 'completed', completed_at = NOW(), updated_at = NOW()
             WHERE task_id = $taskId"
        );

        self::recalculatePlanProgress($planId);
        return true;
    }

    public static function uncompleteTask(int $taskId, int $userId): bool
    {
        if ($taskId <= 0 || $userId <= 0) return false;

        $taskRs = Database::search(
            "SELECT rt.plan_id, rt.status
             FROM recovery_tasks rt
             INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             WHERE rt.task_id = $taskId AND rp.user_id = $userId
             LIMIT 1"
        );
        if (!$taskRs || $taskRs->num_rows === 0) return false;
        $taskRow = $taskRs->fetch_assoc();

        if ($taskRow['status'] !== 'completed') return true;

        $planId = (int)$taskRow['plan_id'];

        Database::iud(
            "UPDATE recovery_tasks
             SET status = 'pending', completed_at = NULL, updated_at = NOW()
             WHERE task_id = $taskId"
        );

        self::recalculatePlanProgress($planId);
        return true;
    }

    /**
     * Recalculate and persist progress_percentage for a plan based on completed tasks.
     * Also marks the plan 'completed' when all tasks are done.
     */
    public static function recalculatePlanProgress(int $planId): void
    {
        if ($planId <= 0) return;

        $stats = Database::search(
            "SELECT COUNT(*) AS total_count,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_count
             FROM recovery_tasks
             WHERE plan_id = $planId"
        );
        $row = $stats ? $stats->fetch_assoc() : null;
        $total = (int) ($row['total_count'] ?? 0);
        $completed = (int) ($row['completed_count'] ?? 0);
        $progress = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        // Auto-complete the plan when every task is done
        $newStatus = ($total > 0 && $completed >= $total) ? 'completed' : 'active';

        Database::iud(
            "UPDATE recovery_plans
             SET progress_percentage = $progress,
                 status = '$newStatus',
                 actual_completion_date = " . ($newStatus === 'completed' ? 'CURDATE()' : 'actual_completion_date') . ",
                 updated_at = NOW()
             WHERE plan_id = $planId"
        );

        // Award plan_completed the moment the plan transitions — look up owner here
        if ($newStatus === 'completed') {
            $ownerRs = Database::search(
                "SELECT user_id FROM recovery_plans WHERE plan_id = $planId LIMIT 1"
            );
            if ($ownerRs && ($ownerRow = $ownerRs->fetch_assoc())) {
                self::awardAchievement((int)$ownerRow['user_id'], 'plan_completed');
            }
        }
    }

    public static function getCompletedPlanDetails(int $planId, int $userId): ?array
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
            'planId'         => (int)$row['plan_id'],
            'title'          => $row['title']          ?? 'Recovery Plan',
            'category'       => $row['category']       ?? 'General',
            'planType'       => $row['plan_type']       ?? 'self',
            'counselorId'    => $row['counselor_id']   ? (int)$row['counselor_id'] : null,
            'counselorName'  => $row['counselor_name'] ?? null,
            'startDate'      => $row['start_date']     ?? null,
            'completionDate' => $row['actual_completion_date'] ?? null,
            'daysTaken'      => (int)$row['days_taken'],
        ];
    }

    public static function requestFollowUpPlan(int $userId, int $planId): bool
    {
        $rs = Database::search(
            "SELECT rp.counselor_id, rp.title,
                    COALESCE(u.display_name, CONCAT(u.first_name, ' ', u.last_name)) AS user_name,
                    cu.user_id AS counselor_user_id
             FROM recovery_plans rp
             INNER JOIN users u  ON u.user_id  = $userId
             LEFT JOIN counselors c  ON c.counselor_id  = rp.counselor_id
             LEFT JOIN users cu      ON cu.user_id       = c.user_id
             WHERE rp.plan_id = $planId AND rp.user_id = $userId
             LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;

        $row              = $rs->fetch_assoc();
        $counselorUserId  = $row['counselor_user_id'] ? (int)$row['counselor_user_id'] : null;
        $conn             = Database::setUpConnection();
        $safeUserName     = $conn->real_escape_string($row['user_name']  ?? 'A user');
        $safePlanTitle    = $conn->real_escape_string($row['title']       ?? 'their recovery plan');

        // Notify the counselor
        if ($counselorUserId) {
            Database::iud(
                "INSERT INTO notifications (user_id, type, title, message, link, is_read, created_at)
                 VALUES (
                     $counselorUserId,
                     'plan_followup_request',
                     'Follow-up Plan Requested',
                     '$safeUserName completed \"$safePlanTitle\" and is requesting a follow-up recovery plan.',
                     '/counselor/clients',
                     0, NOW()
                 )"
            );
        }

        // Confirm to the user
        Database::iud(
            "INSERT INTO notifications (user_id, type, title, message, link, is_read, created_at)
             VALUES (
                 $userId,
                 'plan_followup_sent',
                 'Follow-up Requested',
                 'Your counselor has been notified and will create a new plan for you soon.',
                 '/user/recovery',
                 0, NOW()
             )"
        );

        return true;
    }

    public static function startSobrietyTracking(int $userId): bool
    {
        if ($userId <= 0) return false;

        Database::iud(
            "UPDATE user_profiles
             SET sobriety_start_date = CURDATE(), updated_at = NOW()
             WHERE user_id = $userId
               AND sobriety_start_date IS NULL"
        );

        Database::iud(
            "INSERT INTO user_progress (user_id, date, days_sober, is_sober_today, notes)
             VALUES ($userId, CURDATE(), 0, 1, 'Started sobriety tracking')
             ON DUPLICATE KEY UPDATE
               is_sober_today = VALUES(is_sober_today),
               notes = VALUES(notes),
               updated_at = NOW()"
        );

        return true;
    }

    public static function resetSobrietyCounter(int $userId, string $reason = ''): bool
    {
        if ($userId <= 0) return false;

        // 1. Read current days_sober BEFORE resetting
        $currentDays = 0;
        $rsProfile = Database::search(
            "SELECT DATEDIFF(CURDATE(), sobriety_start_date) AS days
             FROM user_profiles
             WHERE user_id = $userId AND sobriety_start_date IS NOT NULL
             LIMIT 1"
        );
        if ($row = $rsProfile->fetch_assoc()) {
            $currentDays = max(0, (int)$row['days']);
        }

        // 2. Insert relapse record BEFORE resetting (so the date is accurate)
        Database::setUpConnection();
        $safeNotes = Database::$connection->real_escape_string($reason);
        Database::iud(
            "INSERT INTO relapse_history
                (user_id, relapse_date, days_sober_before, trigger_notes, counselor_notified)
             VALUES ($userId, CURDATE(), $currentDays, '$safeNotes', 0)"
        );

        // 3. Reset sobriety (original logic preserved)
        Database::iud(
            "UPDATE user_profiles
             SET sobriety_start_date = CURDATE(), updated_at = NOW()
             WHERE user_id = $userId"
        );

        Database::iud(
            "INSERT INTO user_progress (user_id, date, days_sober, is_sober_today, notes)
             VALUES ($userId, CURDATE(), 0, 1, '$safeNotes')
             ON DUPLICATE KEY UPDATE
               days_sober = 0,
               is_sober_today = 1,
               notes = VALUES(notes),
               updated_at = NOW()"
        );

        return true;
    }

    public static function getRelapseHistory(int $userId): array
    {
        $rs = Database::search(
            "SELECT relapse_id, relapse_date, days_sober_before, trigger_notes, created_at
             FROM relapse_history
             WHERE user_id = $userId
             ORDER BY relapse_date DESC"
        );

        $history = [];
        while ($row = $rs->fetch_assoc()) {
            $history[] = [
                'relapseId'       => (int)$row['relapse_id'],
                'relapseDate'     => date('M j, Y', strtotime($row['relapse_date'])),
                'daysSoberBefore' => (int)$row['days_sober_before'],
                'triggerNotes'    => $row['trigger_notes'] ?? '',
            ];
        }
        return $history;
    }

    public static function resumePlan(int $planId, int $userId): bool
    {
        if ($planId <= 0 || $userId <= 0) return false;

        // If another plan is currently active, pause it first (swap), so
        // only one plan is ever active at a time.
        Database::iud(
            "UPDATE recovery_plans
             SET status = 'paused', updated_at = NOW()
             WHERE user_id = $userId
               AND status = 'active'
               AND is_template = 0
               AND plan_id <> $planId"
        );

        Database::iud(
            "UPDATE recovery_plans
             SET status = 'active',
                 start_date = COALESCE(start_date, CURDATE()),
                 updated_at = NOW()
             WHERE plan_id = $planId
               AND user_id = $userId
               AND status = 'paused'"
        );

        return true;
    }

    public static function getTasksByPlanId(int $planId, int $userId): array
    {
        $rs = Database::search(
            "SELECT rt.task_id, rt.title, rt.status, rt.priority, rt.task_type, rt.due_date, rt.phase
             FROM recovery_tasks rt
             INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             WHERE rt.plan_id = $planId
               AND rp.user_id = $userId
             ORDER BY rt.phase ASC, rt.sort_order ASC, rt.task_id ASC"
        );

        $tasks = [];
        while ($row = $rs->fetch_assoc()) {
            $tasks[] = [
                'taskId'   => (int)$row['task_id'],
                'title'    => $row['title'] ?? 'Task',
                'status'   => $row['status'] ?? 'pending',
                'priority' => $row['priority'] ?? 'medium',
                'taskType' => $row['task_type'] ?? 'custom',
                'phase'    => (int)($row['phase'] ?? 1),
                'dueDate'  => !empty($row['due_date']) ? date('M j', strtotime($row['due_date'])) : null,
            ];
        }
        return $tasks;
    }

    public static function getGeneralPlans(): array
    {
        $rs = Database::search(
            "SELECT plan_id, title, description, category, plan_type, progress_percentage
             FROM recovery_plans
             WHERE is_template = 1
             ORDER BY updated_at DESC"
        );

        $plans = [];
        while ($row = $rs->fetch_assoc()) {
            $plans[] = [
                'planId' => (int)$row['plan_id'],
                'title' => $row['title'] ?? 'Recovery Plan',
                'description' => $row['description'] ?? '',
                'category' => $row['category'] ?? 'General',
                'planType' => $row['plan_type'] ?? 'self',
                'progressPercentage' => (int)($row['progress_percentage'] ?? 0),
            ];
        }
        return $plans;
    }

    // ── Achievements ─────────────────────────────────────────────────

    private static array $achievementDefs = [
        'sober_1d'       => ['type'=>'sober','days'=>1,   'title'=>'1 Day Sober',        'badge'=>'1D',  'icon'=>'sun',            'milestone'=>false],
        'sober_7d'       => ['type'=>'sober','days'=>7,   'title'=>'7 Days Sober',       'badge'=>'7D',  'icon'=>'calendar',       'milestone'=>false],
        'sober_14d'      => ['type'=>'sober','days'=>14,  'title'=>'2 Weeks Sober',      'badge'=>'2W',  'icon'=>'calendar-check', 'milestone'=>false],
        'sober_30d'      => ['type'=>'sober','days'=>30,  'title'=>'First Month',        'badge'=>'1M',  'icon'=>'medal',          'milestone'=>true],
        'sober_60d'      => ['type'=>'sober','days'=>60,  'title'=>'Two Months',         'badge'=>'2M',  'icon'=>'award',          'milestone'=>false],
        'sober_90d'      => ['type'=>'sober','days'=>90,  'title'=>'3 Months Sober',     'badge'=>'3M',  'icon'=>'trophy',         'milestone'=>true],
        'sober_180d'     => ['type'=>'sober','days'=>180, 'title'=>'Half a Year',        'badge'=>'6M',  'icon'=>'star',           'milestone'=>true],
        'sober_365d'     => ['type'=>'sober','days'=>365, 'title'=>'One Full Year',      'badge'=>'1Y',  'icon'=>'crown',          'milestone'=>true],
        'first_checkin'  => ['type'=>'activity',          'title'=>'First Check-in',     'badge'=>'CI',  'icon'=>'clipboard-list', 'milestone'=>false],
        'first_journal'  => ['type'=>'activity',          'title'=>'First Journal Entry','badge'=>'JE',  'icon'=>'book-open',      'milestone'=>false],
        'plan_completed' => ['type'=>'activity',          'title'=>'Plan Completed',     'badge'=>'PC',  'icon'=>'check-circle',   'milestone'=>true],
    ];

    public static function checkAndAwardAchievements(int $userId): void
    {
        if ($userId <= 0) return;

        $earnedRs = Database::search(
            "SELECT achievement_key FROM user_achievements WHERE user_id = $userId"
        );
        $earned = [];
        while ($row = $earnedRs->fetch_assoc()) {
            $earned[$row['achievement_key']] = true;
        }

        $stats     = self::getProgressStats($userId);
        $daysSober = (int)$stats['daysSober'];

        foreach (self::$achievementDefs as $key => $def) {
            if (isset($earned[$key])) continue;
            if ($def['type'] === 'sober' && $daysSober >= $def['days']) {
                self::awardAchievement($userId, $key);
            }
        }

        if (!isset($earned['first_checkin'])) {
            $rs = Database::search("SELECT 1 FROM daily_checkins WHERE user_id = $userId LIMIT 1");
            if ($rs && $rs->num_rows > 0) self::awardAchievement($userId, 'first_checkin');
        }

        if (!isset($earned['first_journal'])) {
            $rs = Database::search("SELECT 1 FROM journal_entries WHERE user_id = $userId LIMIT 1");
            if ($rs && $rs->num_rows > 0) self::awardAchievement($userId, 'first_journal');
        }

    }

    private static function awardAchievement(int $userId, string $key): void
    {
        Database::iud(
            "INSERT IGNORE INTO user_achievements (user_id, achievement_key, awarded_at)
             VALUES ($userId, '" . addslashes($key) . "', NOW())"
        );
    }

    public static function getUserAchievements(int $userId): array
    {
        $earnedRs = Database::search(
            "SELECT achievement_key, awarded_at FROM user_achievements WHERE user_id = $userId"
        );
        $earned = [];
        while ($row = $earnedRs->fetch_assoc()) {
            $earned[$row['achievement_key']] = $row['awarded_at'];
        }

        $result = [];
        foreach (self::$achievementDefs as $key => $def) {
            $result[] = array_merge($def, [
                'key'       => $key,
                'earned'    => isset($earned[$key]),
                'awardedAt' => $earned[$key] ?? null,
            ]);
        }
        return $result;
    }

    // ── End Achievements ─────────────────────────────────────────────

    // ── Task Change Requests (user side) ────────────────────────────

    public static function createChangeRequest(int $taskId, int $userId, string $reason, string $requestedChange): bool
    {
        if ($taskId <= 0 || $userId <= 0 || trim($reason) === '') return false;

        $rs = Database::search(
            "SELECT rt.plan_id, rp.counselor_id, c.user_id AS counselor_user_id
             FROM recovery_tasks rt
             INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             INNER JOIN counselors c ON c.counselor_id = rp.counselor_id
             WHERE rt.task_id = $taskId
               AND rp.user_id = $userId
               AND rp.counselor_id IS NOT NULL
             LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;

        $row              = $rs->fetch_assoc();
        $counselorId      = (int)$row['counselor_id'];
        $planId           = (int)$row['plan_id'];
        $counselorUserId  = (int)$row['counselor_user_id'];

        Database::setUpConnection();
        $conn = Database::$connection;
        $safeReason = $conn->real_escape_string($reason);
        $safeChange = $conn->real_escape_string($requestedChange);

        Database::iud(
            "INSERT INTO task_change_requests
                (task_id, plan_id, user_id, counselor_id, reason, requested_change, status, created_at)
             VALUES ($taskId, $planId, $userId, $counselorId, '$safeReason', '$safeChange', 'pending', NOW())"
        );

        // Notify the counselor
        if ($counselorUserId > 0) {
            $t = $conn->real_escape_string('Task Change Request');
            $m = $conn->real_escape_string('A client has requested a change to one of their assigned tasks.');
            $l = $conn->real_escape_string('/counselor/recovery-plans/task-changes');
            Database::iud(
                "INSERT INTO notifications (user_id, type, title, message, link)
                 VALUES ($counselorUserId, 'task_change_request', '$t', '$m', '$l')"
            );
        }

        return true;
    }

    public static function getChangeRequestsForUser(int $userId): array
    {
        $rs = Database::search(
            "SELECT tcr.request_id, tcr.status, tcr.reason, tcr.requested_change,
                    tcr.counselor_note, tcr.created_at,
                    rt.title AS task_title
             FROM task_change_requests tcr
             INNER JOIN recovery_tasks rt ON rt.task_id = tcr.task_id
             WHERE tcr.user_id = $userId
             ORDER BY tcr.created_at DESC"
        );

        $requests = [];
        if (!$rs) return $requests;
        while ($row = $rs->fetch_assoc()) {
            $requests[] = [
                'requestId'       => (int)$row['request_id'],
                'taskTitle'       => $row['task_title'] ?? 'Task',
                'status'          => $row['status'] ?? 'pending',
                'reason'          => $row['reason'] ?? '',
                'requestedChange' => $row['requested_change'] ?? '',
                'counselorNote'   => $row['counselor_note'] ?? '',
                'createdAt'       => date('M j, Y', strtotime($row['created_at'])),
            ];
        }
        return $requests;
    }

    // ── End Task Change Requests ─────────────────────────────────────

    public static function adoptGeneralPlan(int $templatePlanId, int $userId): bool
    {
        if ($templatePlanId <= 0 || $userId <= 0) return false;

        $activeRs = Database::search(
            "SELECT plan_id FROM recovery_plans
             WHERE user_id = $userId AND status = 'active' AND is_template = 0
             LIMIT 1"
        );
        if ($activeRs && $activeRs->num_rows > 0) {
            return false;
        }

        $templateRs = Database::search(
            "SELECT title, description, category, plan_type
             FROM recovery_plans
             WHERE plan_id = $templatePlanId
               AND is_template = 1
             LIMIT 1"
        );

        if (!$templateRs || $templateRs->num_rows === 0) {
            return false;
        }

        $tpl = $templateRs->fetch_assoc();
        $title = addslashes($tpl['title'] ?? 'Recovery Plan');
        $description = addslashes($tpl['description'] ?? '');
        $category = addslashes($tpl['category'] ?? '');
        $planType = addslashes($tpl['plan_type'] ?? 'self');

        Database::iud(
            "INSERT INTO recovery_plans
                (user_id, title, description, category, plan_type, status, start_date, progress_percentage, is_template, assigned_status, created_at, updated_at)
             VALUES
                ($userId, '$title', '$description', '$category', '$planType', 'active', CURDATE(), 0, 0, 'accepted', NOW(), NOW())"
        );

        $newPlanRs = Database::search("SELECT LAST_INSERT_ID() AS id");
        $newPlanRow = $newPlanRs->fetch_assoc();
        $newPlanId = (int)($newPlanRow['id'] ?? 0);
        if ($newPlanId <= 0) {
            return false;
        }

        Database::iud(
            "INSERT INTO recovery_goals (plan_id, goal_type, title, description, target_days, current_progress, status, created_at, updated_at)
             SELECT $newPlanId, goal_type, title, description, target_days, 0, 'in_progress', NOW(), NOW()
             FROM recovery_goals
             WHERE plan_id = $templatePlanId"
        );

        Database::iud(
            "INSERT INTO recovery_tasks (plan_id, title, description, task_type, status, priority, due_date, is_recurring, recurrence_pattern, sort_order, phase, created_at, updated_at)
             SELECT $newPlanId, title, description, task_type, 'pending', priority, due_date, is_recurring, recurrence_pattern, sort_order, phase, NOW(), NOW()
             FROM recovery_tasks
             WHERE plan_id = $templatePlanId"
        );

        return true;
    }

    // ── Self-managed plan task editing ───────────────────────────────

    /**
     * Add a task to a user-owned plan that has no counselor assigned.
     */
    public static function addUserTask(int $planId, int $userId, array $data): bool
    {
        if ($planId <= 0 || $userId <= 0) return false;

        // Verify ownership and no counselor
        $rs = Database::search(
            "SELECT plan_id FROM recovery_plans
             WHERE plan_id = $planId AND user_id = $userId AND counselor_id IS NULL LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;

        Database::setUpConnection();
        $title    = Database::$connection->real_escape_string(trim($data['title'] ?? ''));
        $taskType = Database::$connection->real_escape_string(trim($data['taskType'] ?? 'custom'));
        $priority = Database::$connection->real_escape_string(trim($data['priority'] ?? 'medium'));
        $phase    = max(1, (int)($data['phase'] ?? 1));

        if ($title === '') return false;

        Database::iud(
            "INSERT INTO recovery_tasks (plan_id, title, task_type, status, priority, phase, sort_order, created_at, updated_at)
             VALUES ($planId, '$title', '$taskType', 'pending', '$priority', $phase, 999, NOW(), NOW())"
        );
        return true;
    }

    /**
     * Edit a task that belongs to a user-owned plan with no counselor.
     */
    public static function updateUserTask(int $taskId, int $userId, array $data): bool
    {
        if ($taskId <= 0 || $userId <= 0) return false;

        // Verify task → plan → user ownership and no counselor
        $rs = Database::search(
            "SELECT rt.task_id FROM recovery_tasks rt
             INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             WHERE rt.task_id = $taskId AND rp.user_id = $userId AND rp.counselor_id IS NULL LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;

        Database::setUpConnection();
        $title    = Database::$connection->real_escape_string(trim($data['title'] ?? ''));
        $taskType = Database::$connection->real_escape_string(trim($data['taskType'] ?? 'custom'));
        $priority = Database::$connection->real_escape_string(trim($data['priority'] ?? 'medium'));

        if ($title === '') return false;

        Database::iud(
            "UPDATE recovery_tasks SET title='$title', task_type='$taskType', priority='$priority', updated_at=NOW()
             WHERE task_id = $taskId"
        );
        return true;
    }

    /**
     * Delete a task from a user-owned plan with no counselor.
     */
    public static function deleteUserTask(int $taskId, int $userId): bool
    {
        if ($taskId <= 0 || $userId <= 0) return false;

        $rs = Database::search(
            "SELECT rt.task_id FROM recovery_tasks rt
             INNER JOIN recovery_plans rp ON rp.plan_id = rt.plan_id
             WHERE rt.task_id = $taskId AND rp.user_id = $userId AND rp.counselor_id IS NULL LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;

        Database::iud("DELETE FROM recovery_tasks WHERE task_id = $taskId");
        return true;
    }

    // ── System plans (admin-created templates) ───────────────────────

    /**
     * Return all admin-created system plans for the browse page.
     */
    public static function getSystemPlans(): array
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

    /**
     * Deep-copy a system plan into recovery_plans for a user.
     * Returns false if the user already has an active plan.
     */
    public static function adoptSystemPlan(int $systemPlanId, int $userId): bool
    {
        if ($systemPlanId <= 0 || $userId <= 0) return false;

        // Pause any currently active plans so the new one takes over
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

        $title       = $conn->real_escape_string($sp['title']       ?? 'Recovery Plan');
        $description = $conn->real_escape_string($sp['description'] ?? '');
        $category    = $conn->real_escape_string($sp['category']    ?? 'General');
        $startDate   = !empty($sp['start_date']) ? "'" . $conn->real_escape_string($sp['start_date']) . "'" : 'CURDATE()';

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

        // Copy goals (short-term, long-term)
        $stTitle = $conn->real_escape_string($sp['short_term_goal_title'] ?? '');
        $stDays  = max(1, (int)($sp['short_term_goal_days'] ?? 30));
        $ltTitle = $conn->real_escape_string($sp['long_term_goal_title']  ?? '');
        $ltDays  = max(1, (int)($sp['long_term_goal_days']  ?? 90));

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

        // Copy tasks and milestones from system_plan_tasks
        $tasksRs = Database::search(
            "SELECT * FROM system_plan_tasks WHERE plan_id = $systemPlanId ORDER BY phase, sort_order"
        );
        while ($tasksRs && ($t = $tasksRs->fetch_assoc())) {
            $tTitle     = $conn->real_escape_string($t['title']    ?? '');
            $tType      = $conn->real_escape_string($t['task_type'] ?? 'custom');
            $tPriority  = $conn->real_escape_string($t['priority'] ?? 'medium');
            $tPhase     = (int)($t['phase'] ?? 1);
            $tRecurring = (int)($t['is_recurring'] ?? 0);
            $tPattern   = $t['recurrence_pattern'] ? "'" . $conn->real_escape_string($t['recurrence_pattern']) . "'" : 'NULL';
            $tOrder     = (int)($t['sort_order'] ?? 0);
            $tMilestone = (int)($t['is_milestone'] ?? 0);

            if (!$tMilestone) {
                // Tasks only — milestones are display-only in the admin view
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
