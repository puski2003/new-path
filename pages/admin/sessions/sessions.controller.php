<?php
require_once __DIR__ . '/sessions.model.php';

$adminUserId = (int)($user['id'] ?? 0);

// -- AJAX handlers --
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjax) {
    header('Content-Type: application/json');
    $action = (string)(Request::get('action') ?? '');

    switch ($action) {

        case 'meeting_details':
            $sessionId = (int)(Request::get('session_id') ?? 0);
            if ($sessionId <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid session ID']);
                exit;
            }
            $details = AdminSessionsModel::getMeetingDetails($sessionId);
            echo $details
                ? json_encode($details)
                : json_encode(['error' => 'No meeting data available for this session.']);
            exit;

        case 'get_no_show_disputes':
            $p      = max(1, (int)(Request::get('page') ?? 1));
            $result = AdminSessionsModel::getNoShowDisputes($p, 20);
            echo json_encode(['success' => true] + $result);
            exit;

        case 'mark_refunded':
            $disputeId = (int)(Request::post('dispute_id') ?? 0);
            $note      = trim((string)(Request::post('note') ?? ''));
            if ($disputeId <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid dispute']);
                exit;
            }
            $ok = AdminSessionsModel::markRefunded($disputeId, $adminUserId, $note);
            echo json_encode(['success' => $ok, 'error' => $ok ? null : 'Could not process. Dispute may already be resolved.']);
            exit;

        case 'reject_dispute':
            $disputeId = (int)(Request::post('dispute_id') ?? 0);
            $note      = trim((string)(Request::post('note') ?? ''));
            if ($disputeId <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid dispute']);
                exit;
            }
            $ok = AdminSessionsModel::rejectDispute($disputeId, $adminUserId, $note);
            echo json_encode(['success' => $ok, 'error' => $ok ? null : 'Could not process. Dispute may already be resolved.']);
            exit;
    }

    echo json_encode(['error' => 'Unknown action']);
    exit;
}

// -- Standard page load --
$activeTab = in_array(Request::get('tab'), ['sessions', 'no_show'], true)
    ? Request::get('tab')
    : 'sessions';

$search  = trim((string)(Request::get('search') ?? ''));
$page    = max(1, (int)(Request::get('page') ?? 1));
$limit   = 20;
$offset  = ($page - 1) * $limit;

$result     = AdminSessionsModel::getSessions($search, $limit, $offset);
$sessions   = $result['rows'];
$totalCount = $result['total'];
$totalPages = (int)ceil($totalCount / $limit);
