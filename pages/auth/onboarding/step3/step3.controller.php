<?php

/**
 * Step 3 Onboarding Controller - Dynamic Questions
 */
$error = null;
$genericQuestions = [];
$moduleQuestions = [];
$selectedAddictions = [];
$selectedModuleIds = [];

$token = $_COOKIE['jwt'] ?? '';
$payload = Auth::decode($token);

if ($payload) {
    Database::setUpConnection();
    $userId = $payload['id'];

    $rsEval = Database::search("SELECT addictions FROM onboarding_evaluation WHERE user_id = $userId");
    $evaluation = $rsEval->fetch_assoc();

    if ($evaluation && !empty($evaluation['addictions'])) {
        $selectedAddictions = json_decode($evaluation['addictions'], true) ?? [];

        if (!empty($selectedAddictions)) {
            $escapedModKeys = array_map(function($key) {
                return Database::$connection->real_escape_string($key);
            }, $selectedAddictions);
            $modKeysList = implode("','", $escapedModKeys);

            $rsModules = Database::search("SELECT id, module_key, display_name FROM addiction_type_module WHERE module_key IN ('$modKeysList')");
            while ($row = $rsModules->fetch_assoc()) {
                $selectedModuleIds[] = (int)$row['id'];
            }
        }
    }

    if (!empty($selectedModuleIds)) {
        $moduleIdList = implode(',', $selectedModuleIds);
        $rsModuleQ = Database::search("
            SELECT q.id, q.question_text, q.weight, q.scale_id, q.display_order, m.module_key, m.display_name
            FROM onboarding_questions_step_3 q
            JOIN addiction_type_module m ON q.module_id = m.id
            WHERE q.module_id IN ($moduleIdList) AND q.status = 'ACTIVE'
            ORDER BY m.display_name, q.display_order
        ");
        while ($row = $rsModuleQ->fetch_assoc()) {
            $moduleQuestions[$row['module_key']][] = $row;
        }
    }

    $rsGeneric = Database::search("
        SELECT id, question_text, weight, scale_id, display_order
        FROM onboarding_questions_step_2
        WHERE status = 'ACTIVE'
        ORDER BY display_order
    ");
    while ($row = $rsGeneric->fetch_assoc()) {
        $genericQuestions[] = $row;
    }
}

if (Request::isPost()) {
    $action = Request::post('action') ?? 'submit';

    require_once __DIR__ . '/step3.model.php';
    $token = $_COOKIE['jwt'] ?? '';
    $payload = Auth::decode($token);

    if ($payload) {
        $userId = $payload['id'];

        if ($action === 'skip') {
            if (Step3Model::saveAssessmentAnswers($userId, null, [])) {
                Response::redirect('/auth/onboarding/step4');
            } else {
                $error = 'Failed to skip assessment. Please try again.';
            }
        } else {
            $answers = $_POST['answers'] ?? [];

            if (empty($answers)) {
                $error = 'Please answer at least one question or skip the assessment.';
            } else {
                $answersJson = json_encode($answers);

                $allQuestions = [];
                foreach ($genericQuestions as $q) {
                    $allQuestions[$q['id']] = $q['weight'];
                }
                foreach ($moduleQuestions as $moduleQs) {
                    foreach ($moduleQs as $q) {
                        $allQuestions[$q['id']] = $q['weight'];
                    }
                }

                if (Step3Model::saveAssessmentAnswers($userId, $answersJson, $allQuestions)) {
                    Response::redirect('/auth/onboarding/step4');
                } else {
                    $error = 'Failed to save assessment. Please try again.';
                }
            }
        }
    } else {
        Response::redirect('/auth/login/user');
    }
}