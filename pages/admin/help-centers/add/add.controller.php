<?php
require_once __DIR__ . '/add.model.php';

$error = '';
$success = '';
if (Request::isPost()) {
    foreach (['name', 'type', 'category', 'description'] as $field) {
        if (trim((string) ($_POST[$field] ?? '')) === '') {
            $error = 'Please fill in all required fields.';
            break;
        }
    }

    if ($error === '' && AddHelpCenterModel::create($_POST, (int) $user['id'])) {
        Response::redirect('/admin/resources?tab=help-centers');
    }
}
