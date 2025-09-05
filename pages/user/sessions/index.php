<?php
/**
 * Sessions Route: /user/sessions
 */
require_once __DIR__ . '/../common/user.head.php';
require_once __DIR__ . '/sessions.model.php';

$sessionId = Request::get('id');

if ($sessionId !== null && $sessionId !== '') {
    require_once __DIR__ . '/single-session.controller.php';
    require_once __DIR__ . '/single-session.layout.php';
} else {
    require_once __DIR__ . '/sessions.controller.php';
    require_once __DIR__ . '/sessions.layout.php';
}

