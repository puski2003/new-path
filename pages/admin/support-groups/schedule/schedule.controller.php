<?php
require_once __DIR__ . '/schedule.model.php';

$errorMessage = null;
$groups = SupportGroupScheduleModel::getGroupsForDropdown();

if (Request::isPost()) {
    $required = ['group_id', 'title', 'session_datetime', 'session_type'];
    foreach ($required as $field) {
        if (trim((string) ($_POST[$field] ?? '')) === '') {
            $errorMessage = 'Please fill in all required fields.';
            break;
        }
    }

    if ($_POST['session_type'] === 'video' && empty(trim((string) ($_POST['meeting_link'] ?? '')))) {
        $errorMessage = 'Meeting link is required for video sessions.';
    }

    if ($_POST['session_type'] === 'in_person' && empty(trim((string) ($_POST['meeting_location'] ?? '')))) {
        $errorMessage = 'Meeting location is required for in-person sessions.';
    }

    if ($errorMessage === '' && SupportGroupScheduleModel::createSession($_POST, (int) $user['id'])) {
        Response::redirect('/admin/support-groups?scheduled=1');
    } elseif ($errorMessage === '') {
        $errorMessage = 'Failed to create session.';
    }
}
