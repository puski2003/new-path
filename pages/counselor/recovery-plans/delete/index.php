<?php

require_once __DIR__ . '/../../common/counselor.head.php';
require_once __DIR__ . '/../../common/counselor.data.php';

// Require POST to prevent CSRF via GET-based deletion
if (!Request::isPost()) {
    Response::redirect('/counselor/recovery-plans');
}

$planId = (int) (Request::post('planId') ?? 0);
if ($planId > 0) {
    CounselorData::deletePlan((int) ($user['counselorId'] ?? 0), $planId);
}

Response::redirect('/counselor/recovery-plans');
