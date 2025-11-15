<?php

class RecoveryPlansAdminModel
{
    private static function esc(string $value): string
    {
        Database::setUpConnection();
        return Database::$connection->real_escape_string($value);
    }

    public static function getTemplates(array $filters): array
    {
        $search   = trim((string) ($filters['search'] ?? ''));
        $category = trim((string) ($filters['category'] ?? 'all'));
        $where    = ['1=1'];

        if ($category !== '' && $category !== 'all') {
            $where[] = "category = '" . self::esc($category) . "'";
        }
        if ($search !== '') {
            $safeSearch = self::esc($search);
            $where[] = "title LIKE '%$safeSearch%'";
        }

        $rs = Database::search(
            "SELECT sp.plan_id, sp.title, sp.description,
                    COALESCE(sp.category, 'General') AS category,
                    sp.updated_at,
                    (SELECT COUNT(*) FROM recovery_plans rp
                     WHERE rp.source_plan_id = sp.plan_id) AS adoption_count
             FROM system_plans sp
             WHERE " . implode(' AND ', $where) . "
             ORDER BY sp.updated_at DESC"
        );

        $plans = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $plans[] = [
                'planId'        => (int)$row['plan_id'],
                'planName'      => $row['title']       ?? '',
                'description'   => $row['description'] ?? '',
                'category'      => $row['category']    ?? 'General',
                'adoptionCount' => (int)($row['adoption_count'] ?? 0),
                'createdBy'     => 'Admin',
                'lastUpdated'   => !empty($row['updated_at']) ? date('M j, Y', strtotime($row['updated_at'])) : '-',
            ];
        }

        return $plans;
    }

    public static function getQuestions(array $filters): array
    {
        $questions = [
            ['question' => 'What is your primary recovery goal right now?', 'questionType' => 'Text', 'rating' => 4.8, 'status' => 'Display', 'createdOn' => 'Jan 4, 2026'],
            ['question' => 'How often do cravings interrupt your routine?', 'questionType' => 'Scale', 'rating' => 4.6, 'status' => 'Display', 'createdOn' => 'Jan 6, 2026'],
            ['question' => 'Do you currently have a support system?', 'questionType' => 'Yes/No', 'rating' => 4.4, 'status' => 'Display', 'createdOn' => 'Jan 8, 2026'],
            ['question' => 'Which triggers affect you most?', 'questionType' => 'Multiple Choice', 'rating' => 4.3, 'status' => 'Hidden', 'createdOn' => 'Jan 10, 2026'],
        ];

        $search = strtolower(trim((string) ($filters['search'] ?? '')));
        $status = trim((string) ($filters['status'] ?? 'all'));

        return array_values(array_filter($questions, static function (array $question) use ($search, $status): bool {
            if ($search !== '' && !str_contains(strtolower($question['question']), $search)) {
                return false;
            }
            if ($status !== '' && $status !== 'all' && strcasecmp($question['status'], $status) !== 0) {
                return false;
            }
            return true;
        }));
    }

    public static function getStep2Questions(array $filters = []): array
    {
        $where = [];
        $params = [];

        $scaleType = $filters['scaleType'] ?? 'all';
        $path = $filters['path'] ?? 'all';
        $weight = $filters['weight'] ?? '';
        $status = $filters['status'] ?? 'all';

        if ($scaleType !== 'all') {
            $where[] = "s.scale_name = '" . self::esc($scaleType) . "'";
        }
        if ($path !== 'all') {
            $where[] = "q.path = '" . self::esc($path) . "'";
        }
        if ($weight !== '') {
            $where[] = "q.weight = " . (float) $weight;
        }
        if ($status !== 'all') {
            $where[] = "q.status = '" . self::esc($status) . "'";
        }

        $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
        $rs = Database::search(
            "SELECT q.id, q.question_text, q.weight, q.path, q.status, s.scale_name
             FROM onboarding_questions_step_2 q
             JOIN onboarding_question_scale s ON q.scale_id = s.id
             $whereClause
             ORDER BY q.display_order ASC"
        );

        $questions = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $questions[] = [
                'id' => (int) ($row['id'] ?? 0),
                'questionText' => $row['question_text'] ?? '',
                'scaleType' => $row['scale_name'] ?? '',
                'path' => $row['path'] ?? '',
                'weight' => (float) ($row['weight'] ?? 0),
                'status' => $row['status'] ?? 'ACTIVE',
            ];
        }

        return $questions;
    }

    public static function getStep3Questions(array $filters = []): array
    {
        $where = [];

        $module = $filters['module'] ?? 'all';
        $scaleType = $filters['scaleType'] ?? 'all';
        $weight = $filters['weight'] ?? '';
        $status = $filters['status'] ?? 'all';

        if ($module !== 'all') {
            $where[] = "m.display_name = '" . self::esc($module) . "'";
        }
        if ($scaleType !== 'all') {
            $where[] = "s.scale_name = '" . self::esc($scaleType) . "'";
        }
        if ($weight !== '') {
            $where[] = "q.weight = " . (float) $weight;
        }
        if ($status !== 'all') {
            $where[] = "q.status = '" . self::esc($status) . "'";
        }

        $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
        $rs = Database::search(
            "SELECT q.id, q.question_text, q.weight, q.status, s.scale_name, m.display_name as module_name
             FROM onboarding_questions_step_3 q
             JOIN onboarding_question_scale s ON q.scale_id = s.id
             JOIN addiction_type_module m ON q.module_id = m.id
             $whereClause
             ORDER BY m.module_key, q.display_order ASC"
        );

        $questions = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $questions[] = [
                'id' => (int) ($row['id'] ?? 0),
                'questionText' => $row['question_text'] ?? '',
                'scaleType' => $row['scale_name'] ?? '',
                'module' => $row['module_name'] ?? '',
                'weight' => (float) ($row['weight'] ?? 0),
                'status' => $row['status'] ?? 'ACTIVE',
            ];
        }

        return $questions;
    }

    public static function getTemplatesPaginated(array $filters, int $page = 1, int $perPage = 15): array
    {
        $safePage = Pagination::sanitizePage($page);
        $safePerPage = Pagination::sanitizePerPage($perPage, 15, 100);

        $allPlans = self::getTemplates($filters);
        $totalRows = count($allPlans);
        $meta = Pagination::meta($totalRows, $safePage, $safePerPage);

        $items = array_slice($allPlans, $meta['offset'], $meta['perPage']);

        return [
            'items' => $items,
            'pagination' => $meta,
        ];
    }

    public static function getQuestionsPaginated(array $filters, int $page = 1, int $perPage = 15): array
    {
        $safePage = Pagination::sanitizePage($page);
        $safePerPage = Pagination::sanitizePerPage($perPage, 15, 100);

        $allQuestions = self::getQuestions($filters);
        $totalRows = count($allQuestions);
        $meta = Pagination::meta($totalRows, $safePage, $safePerPage);

        $items = array_slice($allQuestions, $meta['offset'], $meta['perPage']);

        return [
            'items' => $items,
            'pagination' => $meta,
        ];
    }

    public static function getStep2QuestionsPaginated(array $filters, int $page = 1, int $perPage = 15): array
    {
        $safePage = Pagination::sanitizePage($page);
        $safePerPage = Pagination::sanitizePerPage($perPage, 15, 100);

        $allQuestions = self::getStep2Questions($filters);
        $totalRows = count($allQuestions);
        $meta = Pagination::meta($totalRows, $safePage, $safePerPage);

        $items = array_slice($allQuestions, $meta['offset'], $meta['perPage']);

        return [
            'items' => $items,
            'pagination' => $meta,
        ];
    }

    public static function getStep3QuestionsPaginated(array $filters, int $page = 1, int $perPage = 15): array
    {
        $safePage = Pagination::sanitizePage($page);
        $safePerPage = Pagination::sanitizePerPage($perPage, 15, 100);

        $allQuestions = self::getStep3Questions($filters);
        $totalRows = count($allQuestions);
        $meta = Pagination::meta($totalRows, $safePage, $safePerPage);

        $items = array_slice($allQuestions, $meta['offset'], $meta['perPage']);

        return [
            'items' => $items,
            'pagination' => $meta,
        ];
    }

    public static function getStep2QuestionById(int $id): ?array
    {
        $safeId = (int) $id;
        $rs = Database::search(
            "SELECT q.id, q.question_text, q.weight, q.path, q.status, q.scale_id, s.scale_name
             FROM onboarding_questions_step_2 q
             JOIN onboarding_question_scale s ON q.scale_id = s.id
             WHERE q.id = $safeId"
        );

        if ($rs && ($row = $rs->fetch_assoc())) {
            return [
                'id' => (int) $row['id'],
                'questionText' => $row['question_text'] ?? '',
                'scaleId' => (int) $row['scale_id'],
                'scaleType' => $row['scale_name'] ?? '',
                'path' => $row['path'] ?? '',
                'weight' => (float) $row['weight'],
                'status' => $row['status'] ?? 'ACTIVE',
            ];
        }

        return null;
    }

    public static function getStep3QuestionById(int $id): ?array
    {
        $safeId = (int) $id;
        $rs = Database::search(
            "SELECT q.id, q.question_text, q.weight, q.status, q.scale_id, q.module_id, s.scale_name, m.display_name as module_name
             FROM onboarding_questions_step_3 q
             JOIN onboarding_question_scale s ON q.scale_id = s.id
             JOIN addiction_type_module m ON q.module_id = m.id
             WHERE q.id = $safeId"
        );

        if ($rs && ($row = $rs->fetch_assoc())) {
            return [
                'id' => (int) $row['id'],
                'questionText' => $row['question_text'] ?? '',
                'scaleId' => (int) $row['scale_id'],
                'scaleType' => $row['scale_name'] ?? '',
                'moduleId' => (int) $row['module_id'],
                'module' => $row['module_name'] ?? '',
                'weight' => (float) $row['weight'],
                'status' => $row['status'] ?? 'ACTIVE',
            ];
        }

        return null;
    }

    public static function getAllScales(): array
    {
        $rs = Database::search("SELECT id, scale_name FROM onboarding_question_scale ORDER BY id ASC");

        $scales = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $scales[] = [
                'id' => (int) $row['id'],
                'name' => $row['scale_name'] ?? '',
            ];
        }

        return $scales;
    }

    public static function getAllModules(): array
    {
        $rs = Database::search("SELECT id, module_key, display_name FROM addiction_type_module ORDER BY module_key ASC");

        $modules = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $modules[] = [
                'id' => (int) $row['id'],
                'key' => $row['module_key'] ?? '',
                'name' => $row['display_name'] ?? '',
            ];
        }

        return $modules;
    }

    public static function updateStep2Status(int $id): bool
    {
        $safeId = (int) $id;
        $current = self::getStep2QuestionById($safeId);
        if (!$current) {
            return false;
        }

        $newStatus = $current['status'] === 'ACTIVE' ? 'DISABLED' : 'ACTIVE';
        return Database::iud(
            "UPDATE onboarding_questions_step_2 SET status = '$newStatus' WHERE id = $safeId"
        );
    }

    public static function updateStep3Status(int $id): bool
    {
        $safeId = (int) $id;
        $current = self::getStep3QuestionById($safeId);
        if (!$current) {
            return false;
        }

        $newStatus = $current['status'] === 'ACTIVE' ? 'DISABLED' : 'ACTIVE';
        return Database::iud(
            "UPDATE onboarding_questions_step_3 SET status = '$newStatus' WHERE id = $safeId"
        );
    }

    public static function updateStep2Question(int $id, array $data): bool
    {
        $safeId = (int) $id;
        $questionText = self::esc($data['questionText'] ?? '');
        $scaleId = (int) ($data['scaleId'] ?? 1);
        $path = self::esc($data['path'] ?? 'BOTH');
        $weight = (float) ($data['weight'] ?? 1.0);
        $status = self::esc($data['status'] ?? 'ACTIVE');

        return Database::iud(
            "UPDATE onboarding_questions_step_2 
             SET question_text = '$questionText', scale_id = $scaleId, path = '$path', weight = $weight, status = '$status' 
             WHERE id = $safeId"
        );
    }

    public static function updateStep3Question(int $id, array $data): bool
    {
        $safeId = (int) $id;
        $questionText = self::esc($data['questionText'] ?? '');
        $scaleId = (int) ($data['scaleId'] ?? 1);
        $moduleId = (int) ($data['moduleId'] ?? 1);
        $weight = (float) ($data['weight'] ?? 1.0);
        $status = self::esc($data['status'] ?? 'ACTIVE');

        return Database::iud(
            "UPDATE onboarding_questions_step_3 
             SET question_text = '$questionText', scale_id = $scaleId, module_id = $moduleId, weight = $weight, status = '$status' 
             WHERE id = $safeId"
        );
    }

    public static function createStep2Question(array $data): bool
    {
        $questionText = self::esc($data['questionText'] ?? '');
        $scaleId = (int) ($data['scaleId'] ?? 1);
        $path = self::esc($data['path'] ?? 'BOTH');
        $weight = (float) ($data['weight'] ?? 1.0);
        $status = self::esc($data['status'] ?? 'ACTIVE');

        $rs = Database::search("SELECT COALESCE(MAX(display_order), 0) as max_order FROM onboarding_questions_step_2");
        $nextOrder = 1;
        if ($rs && ($row = $rs->fetch_assoc()) && $row['max_order']) {
            $nextOrder = (int) $row['max_order'] + 1;
        }

        return Database::iud(
            "INSERT INTO onboarding_questions_step_2 (question_text, scale_id, path, weight, status, display_order) 
             VALUES ('$questionText', $scaleId, '$path', $weight, '$status', $nextOrder)"
        );
    }

    public static function createStep3Question(array $data): bool
    {
        $questionText = self::esc($data['questionText'] ?? '');
        $scaleId = (int) ($data['scaleId'] ?? 1);
        $moduleId = (int) ($data['moduleId'] ?? 1);
        $weight = (float) ($data['weight'] ?? 1.0);
        $status = self::esc($data['status'] ?? 'ACTIVE');

        $rs = Database::search("SELECT COALESCE(MAX(display_order), 0) as max_order FROM onboarding_questions_step_3 WHERE module_id = $moduleId");
        $nextOrder = 1;
        if ($rs && ($row = $rs->fetch_assoc()) && $row['max_order']) {
            $nextOrder = (int) $row['max_order'] + 1;
        }

        return Database::iud(
            "INSERT INTO onboarding_questions_step_3 (module_id, question_text, scale_id, weight, status, display_order) 
             VALUES ($moduleId, '$questionText', $scaleId, $weight, '$status', $nextOrder)"
        );
    }

    public static function deleteStep2Question(int $id): bool
    {
        $safeId = (int) $id;
        return Database::iud("DELETE FROM onboarding_questions_step_2 WHERE id = $safeId");
    }

    public static function deleteStep3Question(int $id): bool
    {
        $safeId = (int) $id;
        return Database::iud("DELETE FROM onboarding_questions_step_3 WHERE id = $safeId");
    }
}
