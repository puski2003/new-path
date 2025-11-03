<?php
require_once __DIR__ . '/system-security.model.php';
$filters = ['action' => Request::get('action') ?? 'all', 'startDate' => Request::get('startDate') ?? '', 'endDate' => Request::get('endDate') ?? ''];
$auditLogs = SystemSecurityModel::getLogs($filters);
$loginAttemptsChart = SystemSecurityModel::getLoginAttemptsChart();
