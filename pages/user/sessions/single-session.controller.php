<?php

require_once __DIR__ . '/extend/extend.model.php';

$sessionIdRaw = Request::get('id');
$sessionId = filter_var($sessionIdRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($sessionId === false) {
    Response::status(404);
    require ROOT . '/pages/404.php';
    exit;
}

$userId = (int)$user['id'];

if ($ajaxAction = Request::get('ajax')) {

    if ($ajaxAction === 'accept_extension') {
        $extId    = (int)(Request::post('extension_id') ?? 0);
        $duration = (int)(Request::post('duration_minutes') ?? 0);
        if ($extId <= 0 || $duration <= 0) {
            Response::redirect('/user/sessions?id=' . $sessionId . '&ext_error=' . urlencode('Invalid request.'));
            exit;
        }
        $result = ExtendModel::accept($extId, $userId, $duration);
        if (!$result['success']) {
            Response::redirect('/user/sessions?id=' . $sessionId . '&ext_error=' . urlencode($result['error']));
            exit;
        }
        $extRow  = Database::search(
            "SELECT ser.counselor_id, c.user_id AS counselor_user_id, s.session_id
             FROM session_extension_requests ser
             JOIN counselors c ON c.counselor_id = ser.counselor_id
             JOIN sessions   s ON s.session_id   = ser.session_id
             WHERE ser.extension_id = $extId LIMIT 1"
        );
        $extData = $extRow ? $extRow->fetch_assoc() : null;
        if ($extData) {
            $cUserId = (int)$extData['counselor_user_id'];
            $sId     = (int)$extData['session_id'];
            Database::setUpConnection();
            $t = Database::$connection->real_escape_string('Extension Request Accepted');
            $m = Database::$connection->real_escape_string('The client accepted the ' . $duration . '-minute extension. Awaiting payment.');
            $l = Database::$connection->real_escape_string('/counselor/sessions/workspace?session_id=' . $sId);
            Database::iud("INSERT INTO notifications (user_id, type, title, message, link) VALUES ($cUserId, 'extension_accepted', '$t', '$m', '$l')");
        }
        Response::redirect('/user/sessions/extend/checkout?extension_id=' . $extId);
        exit;
    }

    if ($ajaxAction === 'decline_extension') {
        $extId = (int)(Request::post('extension_id') ?? 0);
        if ($extId > 0 && ExtendModel::decline($extId, $userId)) {
            $extRow  = Database::search(
                "SELECT ser.counselor_id, c.user_id AS counselor_user_id, s.session_id
                 FROM session_extension_requests ser
                 JOIN counselors c ON c.counselor_id = ser.counselor_id
                 JOIN sessions   s ON s.session_id   = ser.session_id
                 WHERE ser.extension_id = $extId LIMIT 1"
            );
            $extData = $extRow ? $extRow->fetch_assoc() : null;
            if ($extData) {
                $cUserId = (int)$extData['counselor_user_id'];
                $sId     = (int)$extData['session_id'];
                Database::setUpConnection();
                $t = Database::$connection->real_escape_string('Extension Request Declined');
                $m = Database::$connection->real_escape_string('The client declined your session extension request.');
                $l = Database::$connection->real_escape_string('/counselor/sessions/workspace?session_id=' . $sId);
                Database::iud("INSERT INTO notifications (user_id, type, title, message, link) VALUES ($cUserId, 'extension_declined', '$t', '$m', '$l')");
            }
        }
        Response::redirect('/user/sessions?id=' . $sessionId);
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unknown action']);
    exit;
}

/* ── Page load ─────────────────────────────────────────── */
$sessionData = SessionsModel::getSessionById($userId, (int)$sessionId);
if ($sessionData === null) {
    Response::status(404);
    require ROOT . '/pages/404.php';
    exit;
}

$isUpcomingSession = in_array($sessionData['status'], ['scheduled', 'confirmed', 'in_progress'], true)
    && strtotime((string)$sessionData['sessionDateTime']) >= time();

// Auto-open review modal if redirected from the sessions list ?review=1
$autoOpenReview = Request::get('review') === '1' && !$isUpcomingSession && !$sessionData['hasReview'];
$autoOpenNoShow = Request::get('report') === '1'
    && !$isUpcomingSession
    && !$sessionData['hasDispute'];

// Show a success banner when redirected from booking payment
$justBooked = Request::get('booked') === '1';

// Check for a pending extension request (drives the modal auto-open)
$pendingExtension = null;
if ($sessionData['status'] === 'in_progress') {
    $pendingExtension = ExtendModel::getPendingForUser($userId, (int)$sessionId);
}

$pageTitle = 'Session Details';
$pageStyle = ['user/dashboard', 'user/sessions'];
