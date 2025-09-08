<?php
require_once __DIR__ . '/application-view.model.php';

$applicationId = (int) (Request::get('id') ?? 0);
$application = ApplicationViewModel::getApplication($applicationId);
$error = $application ? '' : 'Application not found.';
