<?php

class CounselorData
{
    private static function esc(string $value): string
    {
        Database::setUpConnection();
        return Database::$connection->real_escape_string($value);
    }

    public static function getClientsByCounselor(int $counselorId): array
    {
        $safeCounselorId = max(0, $counselorId);
        $rs = Database::search(
            "SELECT DISTINCT u.user_id, u.email, u.display_name, u.first_name, u.last_name,
                    u.profile_picture, u.phone_number, u.is_active,
                    MAX(s.session_datetime) AS last_session_at,
                    COUNT(s.session_id) AS session_count,
                    COALESCE(MAX(rp.progress_percentage), 0) AS progress_percentage
             FROM sessions s
             INNER JOIN users u ON u.user_id = s.user_id
             LEFT JOIN recovery_plans rp ON rp.user_id = u.user_id AND rp.counselor_id = $safeCounselorId
             WHERE s.counselor_id = $safeCounselorId
             GROUP BY u.user_id, u.email, u.display_name, u.first_name, u.last_name, u.profile_picture, u.phone_number, u.is_active
             ORDER BY MAX(s.session_datetime) DESC, u.display_name ASC"
        );

        $clients = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $name = $row['display_name']
                ?: trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))
                ?: 'Client';
            $clients[] = [
                'id' => (int) $row['user_id'],
                'name' => $name,
                'email' => $row['email'] ?? '',
                'phone' => $row['phone_number'] ?? '',
                'avatarUrl' => $row['profile_picture'] ?: '/assets/img/avatar.png',
                'status' => !empty($row['is_active']) ? 'Active' : 'Inactive',
                'progressPercentage' => (int) ($row['progress_percentage'] ?? 0),
                'sessionCount' => (int) ($row['session_count'] ?? 0),
                'lastSessionAt' => $row['last_session_at'] ?? null,
                'latestPlanId' => self::getLatestPlanIdForClient($safeCounselorId, (int) $row['user_id']),
            ];
        }

        return $clients;
    }

    public static function getClientProfile(int $counselorId, int $clientUserId): ?array
    {
        $safeCounselorId = max(0, $counselorId);
        $safeClientUserId = max(0, $clientUserId);

        // Per PRD §3.3 and §9.1 (GDPR/PDPA compliance):
        // Counselors may only see basic demographics + session/plan context.
        // Private health data (sobriety, urge logs, check-ins, journals) must NOT be fetched.
        $rs = Database::search(
            "SELECT u.user_id, u.email, u.display_name, u.first_name, u.last_name,
                    u.profile_picture, u.phone_number, u.is_active, u.bio, u.created_at
             FROM users u
             WHERE u.user_id = $safeClientUserId
               AND EXISTS (
                   SELECT 1 FROM sessions s
                   WHERE s.user_id = u.user_id AND s.counselor_id = $safeCounselorId
               )
             LIMIT 1"
        );

        $client = $rs ? $rs->fetch_assoc() : null;
        if (!$client) {
            return null;
        }

        $name = $client['display_name']
            ?: trim(($client['first_name'] ?? '') . ' ' . ($client['last_name'] ?? ''))
            ?: 'Client';

        // Only fetch sessions that belong to THIS counselor
        $sessionStatsRs = Database::search(
            "SELECT COUNT(*) AS total_sessions,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_sessions,
                    MAX(session_datetime) AS last_session_at,
                    MAX(session_notes) AS latest_notes
             FROM sessions
             WHERE user_id = $safeClientUserId
               AND counselor_id = $safeCounselorId"
        );
        $sessionStats = $sessionStatsRs ? $sessionStatsRs->fetch_assoc() : [];

        // Only fetch recovery plans assigned by THIS counselor
        $planRs = Database::search(
            "SELECT plan_id, title, description, status, progress_percentage, custom_notes
             FROM recovery_plans
             WHERE user_id = $safeClientUserId
               AND counselor_id = $safeCounselorId
             ORDER BY updated_at DESC
             LIMIT 1"
        );
        $plan = $planRs ? $planRs->fetch_assoc() : null;

        $totalPostsRs = Database::search(
            "SELECT COUNT(*) AS total_posts
             FROM community_posts
             WHERE user_id = $safeClientUserId
               AND is_active = 1"
        );
        $communityPostsCount = $totalPostsRs ? (int)($totalPostsRs->fetch_assoc()['total_posts'] ?? 0) : 0;

        $postRs = Database::search(
            "SELECT post_id, title, content, image_url, likes_count, comments_count, shares_count, created_at
             FROM community_posts
             WHERE user_id = $safeClientUserId
               AND is_active = 1
             ORDER BY created_at DESC
             LIMIT 3"
        );

        $communityPosts = [];
        while ($postRs && ($postRow = $postRs->fetch_assoc())) {
            $communityPosts[] = [
                'postId' => (int)$postRow['post_id'],
                'title' => $postRow['title'] ?? '',
                'content' => $postRow['content'] ?? '',
                'imageUrl' => $postRow['image_url'] ?? '',
                'likesCount' => (int)($postRow['likes_count'] ?? 0),
                'commentsCount' => (int)($postRow['comments_count'] ?? 0),
                'sharesCount' => (int)($postRow['shares_count'] ?? 0),
                'createdAt' => $postRow['created_at'] ?? null,
            ];
        }

        return [
            'id' => (int) $client['user_id'],
            'name' => $name,
            'email' => $client['email'] ?? '',
            'phone' => $client['phone_number'] ?? '',
            'bio' => $client['bio'] ?? '',
            'joinedAt' => !empty($client['created_at']) ? date('M j, Y', strtotime($client['created_at'])) : null,
            'avatarUrl' => $client['profile_picture'] ?: '/assets/img/avatar.png',
            'status' => !empty($client['is_active']) ? 'Active' : 'Inactive',
            'progressPercentage' => (int) ($plan['progress_percentage'] ?? 0),
            'sessionNotes' => $sessionStats['latest_notes'] ?? 'No session notes available yet.',
            'totalSessions' => (int) ($sessionStats['total_sessions'] ?? 0),
            'completedSessions' => (int) ($sessionStats['completed_sessions'] ?? 0),
            'plan' => $plan ? [
                'planId' => (int) $plan['plan_id'],
                'title' => $plan['title'] ?? 'Personalized recovery journey',
                'status' => $plan['status'] ?? 'draft',
                'description' => $plan['description'] ?? '',
                'progressPercentage' => (int) ($plan['progress_percentage'] ?? 0),
            ] : null,
            'communityPostsCount' => $communityPostsCount,
            'communityPosts' => $communityPosts,
        ];
    }

    public static function getSessionsByCounselor(int $counselorId): array
    {
        $safeCounselorId = max(0, $counselorId);
        $rs = Database::search(
            "SELECT s.session_id, s.user_id, s.session_datetime, s.session_type, s.status, s.location, s.meeting_link, s.session_notes,
                    COALESCE(u.display_name, CONCAT(u.first_name, ' ', u.last_name), u.username, 'Client') AS user_name,
                    u.profile_picture
             FROM sessions s
             INNER JOIN users u ON u.user_id = s.user_id
             WHERE s.counselor_id = $safeCounselorId
             ORDER BY s.session_datetime DESC"
        );

        $sessions = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $dateTime = $row['session_datetime'] ?? null;
            $timestamp = $dateTime ? strtotime($dateTime) : false;
            $sessions[] = [
                'sessionId' => (int) $row['session_id'],
                'userId' => (int) $row['user_id'],
                'userName' => $row['user_name'] ?? 'Client',
                'userAvatar' => $row['profile_picture'] ?: '/assets/img/avatar.png',
                'sessionDatetime' => $dateTime,
                'sessionType' => $row['session_type'] ?? 'video',
                'location' => $row['location'] ?? '',
                'status' => $row['status'] ?? 'scheduled',
                'sessionNotes' => $row['session_notes'] ?? '',
                'meetingLink' => $row['meeting_link'] ?? '',
                'isUpcoming' => $timestamp ? $timestamp >= time() && in_array($row['status'], ['scheduled', 'confirmed', 'in_progress'], true) : false,
            ];
        }

        return $sessions;
    }

    public static function getPlansByCounselor(int $counselorId): array
    {
        $safeCounselorId = max(0, $counselorId);
        $rs = Database::search(
            "SELECT rp.plan_id, rp.user_id, rp.title, rp.description, rp.status, rp.progress_percentage, rp.updated_at,
                    COALESCE(u.display_name, CONCAT(u.first_name, ' ', u.last_name), u.username, 'Client') AS client_name
             FROM recovery_plans rp
             INNER JOIN users u ON u.user_id = rp.user_id
             WHERE rp.counselor_id = $safeCounselorId
             ORDER BY rp.updated_at DESC, rp.plan_id DESC"
        );

        $plans = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $plans[] = [
                'planId' => (int) $row['plan_id'],
                'userId' => (int) $row['user_id'],
                'title' => $row['title'] ?? 'Recovery Plan',
                'description' => $row['description'] ?? '',
                'status' => $row['status'] ?? 'draft',
                'progressPercentage' => (int) ($row['progress_percentage'] ?? 0),
                'clientName' => $row['client_name'] ?? 'Client',
                'updatedAt' => $row['updated_at'] ?? null,
            ];
        }

        return $plans;
    }

    public static function getPlanById(int $counselorId, int $planId): ?array
    {
        $safeCounselorId = max(0, $counselorId);
        $safePlanId = max(0, $planId);
        $rs = Database::search(
            "SELECT rp.*, COALESCE(u.display_name, CONCAT(u.first_name, ' ', u.last_name), u.username, 'Client') AS client_name
             FROM recovery_plans rp
             INNER JOIN users u ON u.user_id = rp.user_id
             WHERE rp.plan_id = $safePlanId
               AND rp.counselor_id = $safeCounselorId
             LIMIT 1"
        );
        $row = $rs ? $rs->fetch_assoc() : null;
        if (!$row) {
            return null;
        }

        return [
            'planId' => (int) $row['plan_id'],
            'userId' => (int) $row['user_id'],
            'title' => $row['title'] ?? 'Recovery Plan',
            'description' => $row['description'] ?? '',
            'category' => $row['category'] ?? '',
            'planType' => $row['plan_type'] ?? 'counselor',
            'status' => $row['status'] ?? 'draft',
            'progressPercentage' => (int) ($row['progress_percentage'] ?? 0),
            'customNotes' => $row['custom_notes'] ?? '',
            'startDate' => $row['start_date'] ?? '',
            'targetCompletionDate' => $row['target_completion_date'] ?? '',
            'updatedAt' => $row['updated_at'] ?? '',
            'clientName' => $row['client_name'] ?? 'Client',
        ];
    }

    public static function getTasksByPlanId(int $planId): array
    {
        $safePlanId = max(0, $planId);
        $rs = Database::search(
            "SELECT task_id, title, description, task_type, status, due_date, recurrence_pattern, phase
             FROM recovery_tasks
             WHERE plan_id = $safePlanId
             ORDER BY phase ASC, sort_order ASC, task_id ASC"
        );

        $tasks = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $tasks[] = [
                'taskId' => (int) $row['task_id'],
                'title' => $row['title'] ?? '',
                'description' => $row['description'] ?? '',
                'taskType' => $row['task_type'] ?? 'custom',
                'status' => $row['status'] ?? 'pending',
                'dueDate' => $row['due_date'] ?? '',
                'recurrencePattern' => $row['recurrence_pattern'] ?? '',
                'phase' => (int) ($row['phase'] ?? 1),
            ];
        }

        return $tasks;
    }

    public static function getGoalsByPlanId(int $planId): array
    {
        $safePlanId = max(0, $planId);
        $rs = Database::search(
            "SELECT goal_id, goal_type, title, target_days
             FROM recovery_goals
             WHERE plan_id = $safePlanId
             ORDER BY goal_id ASC"
        );

        $goals = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $goals[] = [
                'goalId' => (int) $row['goal_id'],
                'goalType' => $row['goal_type'] ?? 'short_term',
                'title' => $row['title'] ?? '',
                'targetDays' => (int) ($row['target_days'] ?? 0),
            ];
        }
        return $goals;
    }

    public static function createApplication(array $input): array
    {
        Database::setUpConnection();
        $email = self::esc($input['email'] ?? '');
        $exists = Database::search("SELECT application_id FROM counselor_applications WHERE email = '$email' LIMIT 1");
        
        if ($exists && $exists->num_rows > 0) {
            return ['ok' => false, 'error' => 'An application already exists for this email address.'];
        }

        $fullName = self::esc($input['fullName'] ?? '');
        $phoneNumber = self::esc($input['phoneNumber'] ?? '');
        $title = self::esc($input['title'] ?? '');
        $specialty = self::esc($input['specialty'] ?? '');
        $bio = self::esc($input['bio'] ?? '');
        $experienceYears = (int) ($input['experienceYears'] ?? 0);
        $education = self::esc($input['education'] ?? '');
        $certifications = self::esc($input['certifications'] ?? '');
        $languagesSpoken = self::esc($input['languagesSpoken'] ?? '');
        $consultationFee = is_numeric($input['consultationFee'] ?? null) ? (float) $input['consultationFee'] : 'NULL';
        $documentsUrl = self::esc($input['documentsUrl'] ?? '');

        // Build availability JSON from day/slot POST fields
        $allDays    = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
        $validTimes = [];
        for ($h = 6; $h <= 22; $h++) { $validTimes[] = sprintf('%02d:00', $h); }
        $availabilityData = [];
        foreach ($allDays as $day) {
            if (empty($input["{$day}_enabled"])) continue;
            $rawSlots = $input["{$day}_slots"] ?? [];
            $slots    = [];
            foreach ((array) $rawSlots as $slot) {
                $start = trim((string) ($slot['start'] ?? ''));
                $end   = trim((string) ($slot['end']   ?? ''));
                if (in_array($start, $validTimes, true) && in_array($end, $validTimes, true) && $start < $end) {
                    $slots[] = ['start' => $start, 'end' => $end];
                }
            }
            if (!empty($slots)) {
                $availabilityData[$day] = $slots;
            }
        }
        $availabilitySchedule = self::esc(json_encode($availabilityData));

        Database::iud(
            "INSERT INTO counselor_applications
                (full_name, email, phone_number, title, specialty, bio, experience_years, education, certifications, languages_spoken, consultation_fee, availability_schedule, documents_url, status, created_at, updated_at)
             VALUES
                ('$fullName', '$email', '$phoneNumber', '$title', '$specialty', '$bio', $experienceYears, '$education', '$certifications', '$languagesSpoken', $consultationFee, '$availabilitySchedule', '$documentsUrl', 'pending', NOW(), NOW())"
        );

        return ['ok' => true];
    }

    public static function createPlan(int $counselorId, array $input): bool
    {
        Database::setUpConnection();
        $userId = (int) ($input['userId'] ?? 0);
        if ($userId <= 0) {
            return false;
        }

        // Per PRD §2.3 and §3.4: counselors may only create plans for users who have
        // booked at least one session with them.
        $bookingCheck = Database::search(
            "SELECT 1 FROM sessions
             WHERE user_id = $userId AND counselor_id = $counselorId
             LIMIT 1"
        );
        if (!$bookingCheck || $bookingCheck->num_rows === 0) {
            return false;
        }

        $title = self::esc($input['title'] ?? 'Recovery Plan');
        $description = self::esc($input['description'] ?? '');
        $category = self::esc($input['category'] ?? '');
        $planType = self::esc($input['planType'] ?? 'counselor');
        $status = self::esc($input['planStatus'] ?? 'draft');
        $startDate = self::esc($input['startDate'] ?? date('Y-m-d'));
        $targetDate = self::esc($input['targetCompletionDate'] ?? date('Y-m-d'));
        $customNotes = self::esc($input['customNotes'] ?? '');

        Database::iud(
            "INSERT INTO recovery_plans
                (user_id, counselor_id, title, description, category, plan_type, status, start_date, target_completion_date, progress_percentage, custom_notes, assigned_status, created_at, updated_at)
             VALUES
                ($userId, $counselorId, '$title', '$description', '$category', '$planType', '$status', '$startDate', '$targetDate', 0, '$customNotes', 'pending', NOW(), NOW())"
        );

        $planId = (int) Database::$connection->insert_id;
        if ($planId <= 0) {
            return false;
        }

        self::syncGoals($planId, $input);
        self::syncTasks($planId, $input, false);
        return true;
    }

    public static function updatePlan(int $counselorId, int $planId, array $input): bool
    {
        Database::setUpConnection();
        $plan = self::getPlanById($counselorId, $planId);
        if (!$plan) {
            return false;
        }

        $userId = (int) ($input['userId'] ?? $plan['userId']);
        $title = self::esc($input['title'] ?? $plan['title']);
        $description = self::esc($input['description'] ?? $plan['description']);
        $category = self::esc($input['category'] ?? $plan['category']);
        $planType = self::esc($input['planType'] ?? $plan['planType']);
        $status = self::esc($input['planStatus'] ?? $plan['status']);
        $startDate = self::esc($input['startDate'] ?? $plan['startDate']);
        $targetDate = self::esc($input['targetCompletionDate'] ?? $plan['targetCompletionDate']);
        $customNotes = self::esc($input['customNotes'] ?? $plan['customNotes']);

        Database::iud(
            "UPDATE recovery_plans
             SET user_id = $userId,
                 title = '$title',
                 description = '$description',
                 category = '$category',
                 plan_type = '$planType',
                 status = '$status',
                 start_date = '$startDate',
                 target_completion_date = '$targetDate',
                 custom_notes = '$customNotes',
                 updated_at = NOW()
             WHERE plan_id = $planId
               AND counselor_id = $counselorId"
        );

        Database::iud("DELETE FROM recovery_goals WHERE plan_id = $planId");
        self::syncGoals($planId, $input);
        self::syncTasks($planId, $input, false);
        self::recalculatePlanProgress($planId);
        return true;
    }

    /**
     * Soft-delete a recovery plan by marking it 'cancelled'.
     * PRD §3.4: "Delete plan: soft-delete with confirmation"
     * Hard DELETE is avoided to preserve audit history and related data.
     */
    public static function deletePlan(int $counselorId, int $planId): bool
    {
        Database::iud(
            "UPDATE recovery_plans
             SET status = 'cancelled', updated_at = NOW()
             WHERE plan_id = $planId
               AND counselor_id = $counselorId
               AND status <> 'cancelled'"
        );
        return true;
    }

    private static function syncGoals(int $planId, array $input): void
    {
        $goalMap = [
            'short_term' => [
                'title' => trim((string) ($input['shortTermGoalTitle'] ?? '')),
                'days' => (int) ($input['shortTermGoalDays'] ?? 0),
            ],
            'long_term' => [
                'title' => trim((string) ($input['longTermGoalTitle'] ?? '')),
                'days' => (int) ($input['longTermGoalDays'] ?? 0),
            ],
        ];

        foreach ($goalMap as $type => $goal) {
            if ($goal['title'] === '') {
                continue;
            }
            $title = self::esc($goal['title']);
            $targetDays = max(1, $goal['days']);
            Database::iud(
                "INSERT INTO recovery_goals (plan_id, goal_type, title, target_days, current_progress, status, created_at, updated_at)
                 VALUES ($planId, '$type', '$title', $targetDays, 0, 'in_progress', NOW(), NOW())"
            );
        }
    }

    private static function syncTasks(int $planId, array $input, bool $replaceExisting): void
    {
        $titles = $input['taskTitle'] ?? [];
        $types = $input['taskType'] ?? [];
        $recurrences = $input['recurrencePattern'] ?? [];
        $phases = $input['taskPhase'] ?? [];

        if (!is_array($titles)) {
            if ($replaceExisting) {
                Database::iud("DELETE FROM recovery_tasks WHERE plan_id = $planId");
            }
            return;
        }

        // Build the set of incoming titles (non-empty only)
        $incomingTitles = [];
        foreach ($titles as $rawTitle) {
            $t = trim((string) $rawTitle);
            if ($t !== '') {
                $incomingTitles[] = $t;
            }
        }

        if ($replaceExisting) {
            // Hard replace — wipe everything (used on plan creation)
            Database::iud("DELETE FROM recovery_tasks WHERE plan_id = $planId");
            foreach ($titles as $index => $rawTitle) {
                $title = trim((string) $rawTitle);
                if ($title === '') continue;
                $taskType  = self::esc((string) ($types[$index] ?? 'custom'));
                $recurrence = self::esc((string) ($recurrences[$index] ?? ''));
                $phase     = max(1, (int) ($phases[$index] ?? 1));
                $safeTitle = self::esc($title);
                $isRecurring = $recurrence !== '' ? 1 : 0;
                Database::iud(
                    "INSERT INTO recovery_tasks (plan_id, title, description, task_type, status, priority, is_recurring, recurrence_pattern, sort_order, phase, created_at, updated_at)
                     VALUES ($planId, '$safeTitle', '', '$taskType', 'pending', 'medium', $isRecurring, '$recurrence', $index, $phase, NOW(), NOW())"
                );
            }
            return;
        }

        // Smart merge — preserve completion status of existing tasks
        // 1. Load existing tasks keyed by title (lower-cased for comparison)
        $existingRs = Database::search(
            "SELECT task_id, title, status FROM recovery_tasks WHERE plan_id = $planId"
        );
        $existing = []; // lowercase title => ['task_id' => int, 'status' => string]
        while ($row = $existingRs->fetch_assoc()) {
            $existing[strtolower(trim($row['title']))] = [
                'task_id' => (int) $row['task_id'],
                'status'  => $row['status'],
            ];
        }

        // 2. Build set of incoming titles (lowercase) so we can detect removals
        $incomingLower = [];
        foreach ($incomingTitles as $t) {
            $incomingLower[] = strtolower($t);
        }

        // 3. Delete tasks that the counselor removed from the list
        foreach ($existing as $lcTitle => $data) {
            if (!in_array($lcTitle, $incomingLower, true)) {
                Database::iud("DELETE FROM recovery_tasks WHERE task_id = {$data['task_id']}");
            }
        }

        // 4. Insert new tasks / update metadata of existing ones
        foreach ($titles as $index => $rawTitle) {
            $title = trim((string) $rawTitle);
            if ($title === '') continue;
            $lcTitle    = strtolower($title);
            $taskType   = self::esc((string) ($types[$index] ?? 'custom'));
            $recurrence = self::esc((string) ($recurrences[$index] ?? ''));
            $phase      = max(1, (int) ($phases[$index] ?? 1));
            $safeTitle  = self::esc($title);
            $isRecurring = $recurrence !== '' ? 1 : 0;

            if (isset($existing[$lcTitle])) {
                // Task already exists — update metadata but keep its status intact
                $taskId = $existing[$lcTitle]['task_id'];
                Database::iud(
                    "UPDATE recovery_tasks
                     SET task_type = '$taskType', is_recurring = $isRecurring,
                         recurrence_pattern = '$recurrence', sort_order = $index,
                         phase = $phase, updated_at = NOW()
                     WHERE task_id = $taskId"
                );
            } else {
                // Brand new task — insert as pending
                Database::iud(
                    "INSERT INTO recovery_tasks (plan_id, title, description, task_type, status, priority, is_recurring, recurrence_pattern, sort_order, phase, created_at, updated_at)
                     VALUES ($planId, '$safeTitle', '', '$taskType', 'pending', 'medium', $isRecurring, '$recurrence', $index, $phase, NOW(), NOW())"
                );
            }
        }
    }

    public static function recalculatePlanProgress(int $planId): void
    {
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
        $newStatus = ($total > 0 && $completed >= $total) ? 'completed' : 'active';
        Database::iud("UPDATE recovery_plans SET progress_percentage = $progress, status = '$newStatus', updated_at = NOW() WHERE plan_id = $planId");
    }

    private static function getLatestPlanIdForClient(int $counselorId, int $clientUserId): ?int
    {
        $rs = Database::search(
            "SELECT plan_id
             FROM recovery_plans
             WHERE counselor_id = $counselorId
               AND user_id = $clientUserId
             ORDER BY updated_at DESC, plan_id DESC
             LIMIT 1"
        );
        $row = $rs ? $rs->fetch_assoc() : null;
        return $row ? (int) $row['plan_id'] : null;
    }

    // ── Task Change Requests (counselor side) ────────────────────────

    public static function getChangeRequestsForCounselor(int $counselorId): array
    {
        $rs = Database::search(
            "SELECT tcr.request_id, tcr.task_id, tcr.status, tcr.reason,
                    tcr.requested_change, tcr.created_at,
                    rt.title AS task_title,
                    COALESCE(u.display_name, CONCAT(u.first_name, ' ', u.last_name)) AS client_name
             FROM task_change_requests tcr
             INNER JOIN recovery_tasks rt ON rt.task_id = tcr.task_id
             INNER JOIN users u ON u.user_id = tcr.user_id
             WHERE tcr.counselor_id = $counselorId AND tcr.status = 'pending'
             ORDER BY tcr.created_at ASC"
        );

        $requests = [];
        if (!$rs) return $requests;
        while ($row = $rs->fetch_assoc()) {
            $requests[] = [
                'requestId'       => (int)$row['request_id'],
                'taskId'          => (int)$row['task_id'],
                'taskTitle'       => $row['task_title'] ?? 'Task',
                'clientName'      => $row['client_name'] ?? 'Client',
                'reason'          => $row['reason'] ?? '',
                'requestedChange' => $row['requested_change'] ?? '',
                'createdAt'       => date('M j, Y', strtotime($row['created_at'])),
            ];
        }
        return $requests;
    }

    public static function resolveChangeRequest(int $requestId, int $counselorId, string $decision, string $note = ''): bool
    {
        if ($requestId <= 0 || !in_array($decision, ['approved', 'rejected'], true)) return false;

        // Fetch the request first (need user_id for notification)
        $reqRs = Database::search(
            "SELECT task_id, user_id, requested_change
             FROM task_change_requests
             WHERE request_id = $requestId
               AND counselor_id = $counselorId
               AND status = 'pending'
             LIMIT 1"
        );
        if (!$reqRs || $reqRs->num_rows === 0) return false;
        $reqRow  = $reqRs->fetch_assoc();
        $taskId  = (int)$reqRow['task_id'];
        $userId  = (int)$reqRow['user_id'];

        $safeNote = self::esc($note);
        Database::iud(
            "UPDATE task_change_requests
             SET status = '$decision',
                 counselor_note = '$safeNote',
                 resolved_at = NOW(),
                 updated_at = NOW()
             WHERE request_id = $requestId
               AND counselor_id = $counselorId
               AND status = 'pending'"
        );

        // If approved: apply the requested title to the task
        if ($decision === 'approved') {
            $newTitle = self::esc($reqRow['requested_change']);
            Database::iud(
                "UPDATE recovery_tasks SET title = '$newTitle', updated_at = NOW()
                 WHERE task_id = $taskId"
            );
        }

        // Notify the user
        if ($userId > 0) {
            Database::setUpConnection();
            $conn = Database::$connection;
            if ($decision === 'approved') {
                $t = $conn->real_escape_string('Task Change Approved');
                $m = $conn->real_escape_string('Your counselor approved your task change request. The task has been updated.');
            } else {
                $t = $conn->real_escape_string('Task Change Rejected');
                $m = $conn->real_escape_string('Your counselor reviewed and rejected your task change request.' . ($note !== '' ? ' Note: ' . $note : ''));
            }
            $l = $conn->real_escape_string('/user/recovery/task/change-requests');
            Database::iud(
                "INSERT INTO notifications (user_id, type, title, message, link)
                 VALUES ($userId, 'task_change_resolved', '$t', '$m', '$l')"
            );
        }

        return true;
    }

    // ── End Task Change Requests ─────────────────────────────────────
}
