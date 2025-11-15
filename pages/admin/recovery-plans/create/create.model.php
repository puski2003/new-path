<?php

class AdminRecoveryPlanCreateModel
{
    private static function esc(string $value): string
    {
        Database::setUpConnection();
        return Database::$connection->real_escape_string($value);
    }

    public static function create(array $input, array $files = []): bool|array
    {
        try {
            Database::setUpConnection();
            Database::$connection->begin_transaction();

            $title = trim($input['title'] ?? '');
            if (empty($title)) {
                return ['error' => 'Plan title is required.'];
            }

            $description        = self::esc(trim($input['description'] ?? ''));
            $goal               = self::esc(trim($input['planGoal'] ?? ''));
            $category           = self::esc(trim($input['category'] ?? ''));
            $notes              = self::esc(trim($input['customNotes'] ?? ''));
            $startDate          = !empty($input['startDate']) ? self::esc($input['startDate']) : null;
            $endDate            = !empty($input['targetCompletionDate']) ? self::esc($input['targetCompletionDate']) : null;
            $shortTermTitle     = self::esc(trim($input['shortTermGoalTitle'] ?? ''));
            $shortTermDays      = max(1, (int) ($input['shortTermGoalDays'] ?? 30));
            $longTermTitle      = self::esc(trim($input['longTermGoalTitle'] ?? ''));
            $longTermDays       = max(1, (int) ($input['longTermGoalDays'] ?? 90));

            // Handle image upload
            $imagePath = null;
            $fileInfo  = $files['planImage'] ?? null;
            if ($fileInfo && isset($fileInfo['tmp_name']) && $fileInfo['error'] === UPLOAD_ERR_OK) {
                $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                $mime    = mime_content_type($fileInfo['tmp_name']);
                if (!in_array($mime, $allowed, true)) {
                    return ['error' => 'Image must be JPEG, PNG, WebP, or GIF.'];
                }
                $ext      = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
                $fileName = 'plan_' . md5(uniqid('', true)) . '.' . strtolower($ext);
                $uploadDir = ROOT . '/public/uploads/system-plans';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                if (move_uploaded_file($fileInfo['tmp_name'], $uploadDir . '/' . $fileName)) {
                    $imagePath = '/uploads/system-plans/' . $fileName;
                }
            }

            $result = Database::iud(
                "INSERT INTO system_plans
                    (title, description, category, image, goal, start_date, end_date,
                     short_term_goal_title, short_term_goal_days,
                     long_term_goal_title,  long_term_goal_days,
                     notes, created_at, updated_at)
                 VALUES
                    ('" . self::esc($title) . "', '$description', '$category',
                     " . ($imagePath ? "'" . self::esc($imagePath) . "'" : 'NULL') . ",
                     '$goal',
                     " . ($startDate ? "'$startDate'" : 'NULL') . ",
                     " . ($endDate   ? "'$endDate'"   : 'NULL') . ",
                     " . ($shortTermTitle !== '' ? "'$shortTermTitle'" : 'NULL') . ", $shortTermDays,
                     " . ($longTermTitle  !== '' ? "'$longTermTitle'"  : 'NULL') . ", $longTermDays,
                     '$notes', NOW(), NOW())"
            );

            $lastError = Database::$connection->error;
            if (!$result || $lastError) {
                throw new Exception('Database insert failed: ' . ($lastError ?: 'unknown'));
            }

            $planId = (int) Database::$connection->insert_id;
            if ($planId <= 0) {
                throw new Exception('Failed to create plan — no insert ID returned.');
            }

            // Tasks
            $taskPhases     = $input['taskPhase']          ?? [];
            $taskTitles     = $input['taskTitle']          ?? [];
            $taskTypes      = $input['taskType']           ?? [];
            $recurrences    = $input['recurrencePattern']  ?? [];

            if (is_array($taskTitles)) {
                foreach ($taskTitles as $i => $rawTitle) {
                    $taskTitle = trim((string) $rawTitle);
                    if ($taskTitle === '') continue;

                    $taskType   = self::esc((string) ($taskTypes[$i] ?? 'custom'));
                    $recurrence = self::esc((string) ($recurrences[$i] ?? ''));
                    $phase      = max(1, (int) ($taskPhases[$i] ?? 1));
                    $isRecurring = $recurrence !== '' ? 1 : 0;

                    Database::iud(
                        "INSERT INTO system_plan_tasks
                            (plan_id, title, task_type, phase, is_milestone, is_recurring,
                             recurrence_pattern, sort_order, created_at)
                         VALUES
                            ($planId, '" . self::esc($taskTitle) . "', '$taskType',
                             $phase, 0, $isRecurring,
                             " . ($isRecurring && $recurrence ? "'$recurrence'" : 'NULL') . ",
                             $i, NOW())"
                    );
                }
            }

            // Milestones (per-phase)
            for ($p = 1; $p <= 3; $p++) {
                $milestones = $input["phase{$p}Milestone"] ?? [];
                if (is_array($milestones)) {
                    foreach ($milestones as $j => $milestone) {
                        $mTitle = trim((string) $milestone);
                        if ($mTitle === '') continue;
                        Database::iud(
                            "INSERT INTO system_plan_tasks
                                (plan_id, title, task_type, phase, is_milestone, sort_order, created_at)
                             VALUES
                                ($planId, '" . self::esc($mTitle) . "', 'custom', $p, 1, $j, NOW())"
                        );
                    }
                }
            }

            Database::$connection->commit();
            return true;

        } catch (Exception $e) {
            Database::$connection->rollback();
            error_log('EXCEPTION in AdminRecoveryPlanCreateModel::create: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
