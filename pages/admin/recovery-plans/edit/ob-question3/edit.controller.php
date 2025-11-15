<?php
require_once __DIR__ . '/edit.model.php';

$questionId = (int) (Request::get('id') ?? Request::post('questionId') ?? 0);
$editQuestion = EditObQuestion3Model::getQuestion($questionId);
$scales = EditObQuestion3Model::getScales();
$modules = EditObQuestion3Model::getModules();

$error = '';

if (!$editQuestion) {
    $error = 'Question not found.';
} elseif (Request::isPost()) {
    $result = EditObQuestion3Model::update($questionId, $_POST);

    if (!empty($result['ok'])) {
        Response::redirect('/admin/recovery-plans?tab=onboarding&alertType=' . urlencode($result['type']) . '&alertMessage=' . urlencode($result['message']));
    }

    $error = (string) ($result['message'] ?? 'Update failed.');
    $editQuestion = EditObQuestion3Model::getQuestion($questionId);
}