<?php
/**
 * /user/sessions/follow-up — Post-session follow-up thread
 * GET  ?session_id=X  → view thread
 * POST               → send a message
 */
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/follow-up.model.php';

$userId    = (int)$user['id'];
$sessionId = (int)(Request::get('session_id') ?? Request::post('session_id') ?? 0);

if ($sessionId <= 0) {
    Response::redirect('/user/sessions');
}

const FOLLOWUP_WINDOW_DAYS = 7;

$session = FollowUpModel::getSession($sessionId, $userId);
if (!$session) {
    Response::redirect('/user/sessions');
}

// Use updated_at as completion timestamp (it is stamped when status → completed).
// Fall back to session_datetime for legacy rows that pre-date this field.
$completedTs = !empty($session['updated_at']) ? strtotime($session['updated_at']) : strtotime($session['session_datetime']);
$sessionTs   = strtotime($session['session_datetime']); // kept for display only
$expiresTs   = $completedTs + (FOLLOWUP_WINDOW_DAYS * 86400);
$daysLeft    = max(0, (int)ceil(($expiresTs - time()) / 86400));
$isExpired   = time() > $expiresTs;
$isLocked    = $isExpired;

$messages  = FollowUpModel::getMessages($sessionId);
$msgCount  = count($messages);
$lastMsgId = 0;
foreach ($messages as $m) {
    $lastMsgId = max($lastMsgId, (int)$m['message_id']);
}

/* ── AJAX poll ───────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && Request::get('ajax') === 'poll') {
    header('Content-Type: application/json');
    $lastId = (int)(Request::get('last_id') ?? 0);

    $rows      = FollowUpModel::pollMessages($sessionId, $lastId);
    $newMsgs   = [];
    $newLastId = $lastId;
    foreach ($rows as $row) {
        $isMe      = (int)$row['sender_id'] === $userId;
        $avatar    = $row['sender_avatar'] ?: '/assets/img/avatar.png';
        $newMsgs[] = [
            'id'      => (int)$row['message_id'],
            'isMe'    => $isMe,
            'name'    => $isMe ? 'You' : $row['sender_name'],
            'avatar'  => $avatar,
            'message' => $row['message'],
            'time'    => date('M j, g:i A', strtotime($row['created_at'])),
        ];
        $newLastId = max($newLastId, (int)$row['message_id']);
    }
    echo json_encode(['success' => true, 'messages' => $newMsgs, 'isLocked' => $isLocked, 'lastMsgId' => $newLastId]);
    exit;
}

/* ── AJAX send ───────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Request::get('ajax') === 'send') {
    header('Content-Type: application/json');
    $msg = trim((string)(Request::post('message') ?? ''));

    if ($isLocked) {
        echo json_encode(['success' => false, 'error' => 'Thread is closed']);
        exit;
    }
    if ($msg === '' || strlen($msg) > 1000) {
        echo json_encode(['success' => false, 'error' => 'Invalid message']);
        exit;
    }

    $counselorUserId = (int)($session['counselor_user_id'] ?? 0);
    $newMsgId = FollowUpModel::sendMessage($sessionId, $userId, $msg, $counselorUserId);

    $myAvatar = $user['profilePictureUrl'] ?? '/assets/img/avatar.png';
    echo json_encode([
        'success'  => true,
        'message'  => [
            'id'      => $newMsgId,
            'isMe'    => true,
            'sender'  => 'You',
            'avatar'  => $myAvatar,
            'text'    => $msg,
            'time'    => date('M j, g:i A'),
        ],
        'msgCount' => $msgCount + 1,
        'daysLeft' => $daysLeft,
    ]);
    exit;
}

/* ── Form POST fallback ──────────────────────────────────────── */
$sendError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = trim(Request::post('message') ?? '');

    if ($isLocked) {
        $sendError = 'This follow-up thread is closed.';
    } elseif (strlen($msg) < 1 || strlen($msg) > 1000) {
        $sendError = 'Message must be between 1 and 1000 characters.';
    } else {
        $counselorUserId = (int)($session['counselor_user_id'] ?? 0);
        FollowUpModel::sendMessage($sessionId, $userId, $msg, $counselorUserId);
        Response::redirect("/user/sessions/follow-up?session_id=$sessionId");
    }
}

/* ── Layout vars ─────────────────────────────────────────────── */
$pageTitle   = 'Follow-up Thread';
$pageStyle   = ['user/sessions', 'user/follow-up'];
$pageScripts = ['/assets/js/components/followup-thread.js'];

require_once __DIR__ . '/follow-up.view.php';
