<?php
require_once __DIR__ . '/create.model.php';

$scales = CreateObQuestion3Model::getScales();
$modules = CreateObQuestion3Model::getModules();
$error = '';

if (Request::isPost()) {
    $result = CreateObQuestion3Model::create($_POST);

    if (!empty($result['ok'])) {
        Response::redirect('/admin/recovery-plans?tab=onboarding&alertType=' . urlencode($result['type']) . '&alertMessage=' . urlencode($result['message']));
    }

    $error = (string) ($result['message'] ?? 'Create failed.');
}