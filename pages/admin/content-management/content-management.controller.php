<?php
require_once __DIR__ . '/content-management.model.php';

$adminUserId = (int)($user['id'] ?? 0);

// -- AJAX handlers --
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjax) {
    header('Content-Type: application/json');
    $action = (string)(Request::get('action') ?? '');

    switch ($action) {

        case 'get_report':
            $reportId = (int)(Request::get('report_id') ?? 0);
            if ($reportId <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid report ID']);
                exit;
            }
            $report = ContentManagementModel::getReport($reportId);
            echo $report
                ? json_encode(['success' => true, 'report' => $report])
                : json_encode(['error' => 'Report not found']);
            exit;

        case 'remove_post':
            $reportId = (int)(Request::post('report_id') ?? 0);
            $note     = trim((string)(Request::post('note') ?? ''));
            if ($reportId <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid report']);
                exit;
            }
            $ok = ContentManagementModel::removePost($reportId, $adminUserId, $note);
            echo json_encode(['success' => $ok, 'error' => $ok ? null : 'Could not process. Report may already be resolved.']);
            exit;

        case 'dismiss_report':
            $reportId = (int)(Request::post('report_id') ?? 0);
            $note     = trim((string)(Request::post('note') ?? ''));
            if ($reportId <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid report']);
                exit;
            }
            $ok = ContentManagementModel::dismissReport($reportId, $adminUserId, $note);
            echo json_encode(['success' => $ok, 'error' => $ok ? null : 'Could not process. Report may already be resolved.']);
            exit;
    }

    echo json_encode(['error' => 'Unknown action']);
    exit;
}

// -- Standard page load --
$filters = [
    'type'   => trim((string)(Request::get('type')   ?? 'all')),
    'reason' => trim((string)(Request::get('reason') ?? 'all')),
    'status' => trim((string)(Request::get('status') ?? 'all')),
];

$page   = max(1, (int)(Request::get('page') ?? 1));
$limit  = 20;
$offset = ($page - 1) * $limit;

$result          = ContentManagementModel::getReports($filters, $limit, $offset);
$reportedContent = $result['rows'];
$totalCount      = $result['total'];
$totalPages      = (int)ceil(max(1, $totalCount) / $limit);

$stats             = ContentManagementModel::getStats();
$totalReportsToday = $stats['today'];
$pendingReports    = $stats['pending'];
$actionsThisWeek   = $stats['actionsThisWeek'];
$activeBans        = 0;

$reportedContentPagination = [
    'currentPage' => $page,
    'totalPages'  => $totalPages,
    'totalRows'   => $totalCount,
    'fromRow'     => $totalCount > 0 ? $offset + 1 : 0,
    'toRow'       => min($offset + $limit, $totalCount),
    'offset'      => $offset,
    'perPage'     => $limit,
];
