<?php
require_once __DIR__ . '/create.model.php';

$errorMessage = null;

if (Request::isPost()) {
    $result = AdminRecoveryPlanCreateModel::create($_POST, $_FILES);
    if (is_array($result) && isset($result['error'])) {
        $errorMessage = $result['error'];
    } elseif ($result === true) {
        Response::redirect('/admin/recovery-plans?created=1');
    } else {
        $errorMessage = 'Failed to create recovery plan.';
    }
}
