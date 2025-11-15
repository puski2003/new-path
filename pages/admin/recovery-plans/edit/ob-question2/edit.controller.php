<?php
require_once __DIR__ . '/edit.model.php';

$questionId = (int) (Request::get('id') ?? Request::post('questionId') ?? 0);
$editQuestion = EditObQuestion2Model::getQuestion($questionId);
$scales = EditObQuestion2Model::getScales();

$error = '';

if (!$editQuestion) {
    $error = 'Question not found.';
} elseif (Request::isPost()) {
    $result = EditObQuestion2Model::update($questionId, $_POST);

    if (!empty($result['ok'])) {
        Response::redirect('/admin/recovery-plans?tab=onboarding&alertType=' . urlencode($result['type']) . '&alertMessage=' . urlencode($result['message']));
    }

    $error = (string) ($result['message'] ?? 'Update failed.');
    $editQuestion = EditObQuestion2Model::getQuestion($questionId);
}