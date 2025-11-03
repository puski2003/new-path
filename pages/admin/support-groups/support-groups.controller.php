<?php
require_once __DIR__ . '/support-groups.model.php';
$filters = ['type' => Request::get('type') ?? 'all', 'status' => Request::get('status') ?? 'all', 'search' => Request::get('search') ?? ''];
$page = Pagination::sanitizePage(Request::get('page') ?? 1);
$perPage = 15;

$groupsResult = SupportGroupsModel::getGroupsPaginated($filters, $page, $perPage);
$groups = $groupsResult['items'];
$groupsPagination = $groupsResult['pagination'];
$appointments = SupportGroupsModel::getUpcomingSessions();
