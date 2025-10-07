<?php

$counselorId = (int)($user['counselorId'] ?? 0);
$sessionId   = (int)(Request::get('session_id') ?? 0);

/* ── AJAX handlers ──────────────────────────────────── */
if ($ajaxAction = Request::get('ajax')) {
    header('Content-Type: application/json');

    if ($ajaxAction === 'save_notes' && $sessionId > 0) {
        $notes = trim((string)(Request::post('notes') ?? ''));
        $ok    = WorkspaceModel::savePrivateNotes($counselorId, $sessionId, $notes);
        echo json_encode(['success' => $ok]);
        exit;
    }

    if ($ajaxAction === 'mark_completed' && $sessionId > 0) {
        $ok = WorkspaceModel::markSessionCompleted($counselorId, $sessionId);
        echo json_encode([
            'success' => $ok,
            'status' => $ok ? 'completed' : null,
            'label' => $ok ? 'Completed' : null,
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Unknown action']);
    exit;
}

/* ── Page load ──────────────────────────────────────── */
if ($sessionId <= 0) {
    Response::redirect('/counselor/sessions');
    exit;
}

$session = WorkspaceModel::getSession($counselorId, $sessionId);
if (!$session) {
    Response::redirect('/counselor/sessions');
    exit;
}

// Load client profile: plan summary, session stats, progress
$clientProfile = CounselorData::getClientProfile($counselorId, $session['userId']);

// Convenience derived values for the layout
$sessionTs   = !empty($session['sessionDatetime']) ? strtotime($session['sessionDatetime']) : null;
$displayTime = $sessionTs ? date('D, M j \a\t g:i A', $sessionTs) : 'Time unavailable';

$typeLabel = match ($session['sessionType']) {
    'in_person' => 'In Person',
    'audio'     => 'Audio',
    'chat'      => 'Chat',
    default     => 'Video',
};

$typeIcon = match ($session['sessionType']) {
    'audio'     => 'mic',
    'chat'      => 'message-circle',
    'in_person' => 'map-pin',
    default     => 'video',
};
