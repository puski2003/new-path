<?php
require_once __DIR__ . '/../common/admin.head.php';
require_once __DIR__ . '/sessions.controller.php';

// AJAX requests exit in the controller; only layout needs to load for normal requests.
require_once __DIR__ . '/sessions.layout.php';
