<?php
require_once __DIR__ . '/create.model.php';

$scales = CreateObQuestion2Model::getScales();
$error = '';

if (Request::isPost()) {
    $result = CreateObQuestion2Model::create($_POST);

    if (!empty($result['ok'])) {
        Response::redirect('/admin/recovery-plans?tab=onboarding&alertType=' . urlencode($result['type']) . '&alertMessage=' . urlencode($result['message']));
    }

    $error = (string) ($result['message'] ?? 'Create failed.');
}