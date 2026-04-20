<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/../sessions.model.php';
require_once __DIR__ . '/view.model.php';

$sessionId = (int) Request::get('id');

if ($sessionId <= 0) {
    Response::status(404);
    require ROOT . '/pages/404.php';
    exit;
}

$userId = (int) $user['id'];

if (Request::get('ajax') === 'accept_extension') {
    require_once __DIR__ . '/../extend/extend.model.php';
    $extId    = (int)(Request::post('extension_id') ?? 0);
    $duration = (int)(Request::post('duration_minutes') ?? 0);
    if ($extId <= 0 || $duration <= 0) {
        Response::redirect('/user/sessions/view?id=' . $sessionId . '&ext_error=' . urlencode('Invalid request.'));
        exit;
    }
    $result = ExtendModel::accept($extId, $userId, $duration);
    if (!$result['success']) {
        Response::redirect('/user/sessions/view?id=' . $sessionId . '&ext_error=' . urlencode($result['error']));
        exit;
    }
    ViewModel::notifyExtensionAccepted($extId, $duration);
    Response::redirect('/user/sessions/extend/checkout?extension_id=' . $extId);
    exit;
}

if (Request::get('ajax') === 'decline_extension') {
    require_once __DIR__ . '/../extend/extend.model.php';
    $extId = (int)(Request::post('extension_id') ?? 0);
    if ($extId > 0 && ExtendModel::decline($extId, $userId)) {
        ViewModel::notifyExtensionDeclined($extId);
    }
    Response::redirect('/user/sessions/view?id=' . $sessionId);
    exit;
}

$sessionData = ViewModel::getSessionById($userId, $sessionId);
if ($sessionData === null) {
    Response::status(404);
    require ROOT . '/pages/404.php';
    exit;
}

$isUpcomingSession = in_array($sessionData['status'], ['scheduled', 'confirmed', 'in_progress'], true)
    && strtotime((string)$sessionData['sessionDateTime']) >= time();

$autoOpenReview = Request::get('review') === '1' && !$isUpcomingSession && !$sessionData['hasReview'];
$autoOpenNoShow = Request::get('report') === '1'
    && !$isUpcomingSession
    && !$sessionData['hasDispute'];

$justBooked = Request::get('booked') === '1';

$pendingExtension = null;
if ($sessionData['status'] === 'in_progress') {
    require_once __DIR__ . '/../extend/extend.model.php';
    $pendingExtension = ExtendModel::getPendingForUser($userId, $sessionId);
}

$pageTitle = 'Session Details';
$pageStyle = ['user/dashboard', 'user/sessions'];

require_once __DIR__ . '/view.view.php';