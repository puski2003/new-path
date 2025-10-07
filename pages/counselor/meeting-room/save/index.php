<?php

/**
 * Route: POST /counselor/meeting-room/save
 *
 * Saves the counselor's permanent Google Meet room link.
 */

require_once __DIR__ . '/../../common/counselor.head.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::redirect('/counselor/dashboard');
    exit;
}

$raw  = trim((string)($_POST['meeting_room_link'] ?? ''));
$link = filter_var($raw, FILTER_VALIDATE_URL) ? $raw : '';

$safeLink    = Database::$connection->real_escape_string($link);
$counselorId = (int)($user['counselorId'] ?? 0);

Database::iud(
    "UPDATE counselors
     SET meeting_room_link = " . ($safeLink !== '' ? "'$safeLink'" : 'NULL') . ", updated_at = NOW()
     WHERE counselor_id = $counselorId"
);

Response::redirect('/counselor/dashboard?room_saved=1');
