<?php

/**
 * /user/dashboard — entry point
 * 1. auth guard  → sets $user
 * 2. controller  → sets $data
 * 3. layout      → renders HTML
 */
require_once __DIR__ . '/../common/user.head.php';
require_once __DIR__ . '/dashboard.controller.php';
require_once __DIR__ . '/dashboard.layout.php';
