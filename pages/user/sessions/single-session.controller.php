<?php

$sessionIdRaw = Request::get('id');
$sessionId = filter_var($sessionIdRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($sessionId === false) {
    Response::status(404);
    require ROOT . '/pages/404.php';
    exit;
}

$sessionData = SessionsModel::getSessionById((int)$user['id'], (int)$sessionId);
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

$pageTitle = 'Session Details';
$pageStyle = ['user/dashboard', 'user/sessions'];
