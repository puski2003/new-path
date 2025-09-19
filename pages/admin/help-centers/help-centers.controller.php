<?php
require_once __DIR__ . '/help-center.model.php';

$filters = [
    'centerStatus' => Request::get('centerStatus') ?? 'all',
    'type' => Request::get('type') ?? 'all',
    'centerCategory' => Request::get('centerCategory') ?? 'all',
];

$page = Pagination::sanitizePage(Request::get('page') ?? 1);
$perPage = 15;

$data = HelpCenterModel::getHelpCentersPaginated($filters, $page, $perPage);
$helpCenters = $data['items'];
$helpCentersPagination = $data['pagination'];
