<?php

require_once __DIR__ . '/../common/admin.head.php';
Database::setUpConnection();
require_once __DIR__ . '/counselor-payouts.controller.php';
require_once __DIR__ . '/counselor-payouts.layout.php';
