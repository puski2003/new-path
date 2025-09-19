<?php
require_once __DIR__ . '/edit.model.php';

$helpCenterId = (int) (Request::get('id') ?? Request::post('helpCenterId') ?? 0);
$helpCenter = EditHelpCenterModel::getCenter($helpCenterId);
$error = '';
$success = '';

if (!$helpCenter) {
    $error = 'Help center not found.';
} elseif (Request::isPost()) {
    if (EditHelpCenterModel::update($helpCenterId, $_POST)) {
        $success = 'Help center updated successfully.';
        $helpCenter = EditHelpCenterModel::getCenter($helpCenterId);
    }
}
