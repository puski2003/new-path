<?php
require_once __DIR__ . '/create.model.php';

$errorMessage = null;

if (Request::isPost()) {
    $required = ['name', 'category'];
    foreach ($required as $field) {
        if (trim((string) ($_POST[$field] ?? '')) === '') {
            $errorMessage = 'Please fill in all required fields.';
            break;
        }
    }

    if ($errorMessage === null && SupportGroupsCreateModel::create($_POST, (int) $user['id'])) {
        Response::redirect('/admin/support-groups?created=1');
    } elseif ($errorMessage === null) {
        $errorMessage = 'Failed to create support group.';
    }
}
