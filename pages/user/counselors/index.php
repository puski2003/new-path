<?php

/**
 * Counselors Route: /user/counselors
 */
require_once __DIR__ . '/../common/user.head.php';
require_once __DIR__ . '/counselors.model.php';

$counselorId = Request::get('id');

if ($counselorId !== null && $counselorId !== '') {
    require_once __DIR__ . '/single-counselor.controller.php';
    require_once __DIR__ . '/single-counselor.layout.php';
} else {
    require_once __DIR__ . '/counselors.controller.php';
    require_once __DIR__ . '/counselors.layout.php';
}
