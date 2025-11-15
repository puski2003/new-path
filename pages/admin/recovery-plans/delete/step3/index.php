<?php
require_once __DIR__ . '/../../recovery-plans.model.php';

$questionId = (int) (Request::get('id') ?? 0);
$referer = $_SERVER['HTTP_REFERER'] ?? '/admin/recovery-plans?tab=onboarding';

if ($questionId > 0) {
    RecoveryPlansAdminModel::deleteStep3Question($questionId);
}

Response::redirect($referer);