<?php

const FOLLOWUP_WINDOW_DAYS  = 7;

$counselorId     = (int) ($user['counselorId'] ?? 0);
$counselorUserId = (int) ($user['id'] ?? 0);

/* ── AJAX handlers (follow-up popup) ────────────────────────── */
if ($ajaxAction = Request::get('ajax')) {
    header('Content-Type: application/json');

    switch ($ajaxAction) {

        /* Return all completed sessions available for follow-up threads */
        case 'get_thread_sessions':
            $rs = Database::search(
                "SELECT s.session_id, s.session_datetime, s.updated_at,
                        COALESCE(u.display_name, u.username, 'Client') AS client_name,
                        u.profile_picture AS client_avatar,
                        COUNT(sm.message_id) AS msg_count
                 FROM sessions s
                 JOIN users u ON u.user_id = s.user_id
                 LEFT JOIN session_messages sm ON sm.session_id = s.session_id
                 WHERE s.counselor_id = $counselorId AND s.status = 'completed'
                 GROUP BY s.session_id
                 ORDER BY s.session_datetime DESC
                 LIMIT 30"
            );
            $items = [];
            while ($rs && ($row = $rs->fetch_assoc())) {
                $completedTs = !empty($row['updated_at']) ? strtotime($row['updated_at']) : strtotime($row['session_datetime']);
                $daysLeft    = max(0, (int) ceil(($completedTs + FOLLOWUP_WINDOW_DAYS * 86400 - time()) / 86400));
                $isLocked    = time() > $completedTs + FOLLOWUP_WINDOW_DAYS * 86400;
                $items[] = [
                    'sessionId'   => (int) $row['session_id'],
                    'clientName'  => $row['client_name'],
                    'clientAvatar'=> $row['client_avatar'] ?: '/assets/img/avatar.png',
                    'sessionDate' => date('M j, Y', strtotime($row['session_datetime'])),
                    'msgCount'    => (int) $row['msg_count'],
                    'daysLeft'    => $daysLeft,
                    'isLocked'    => $isLocked,
                ];
            }
            echo json_encode(['success' => true, 'sessions' => $items]);
            exit;

        /* Load messages for a specific session thread */
        case 'get_messages':
            $sessionId = (int) Request::get('session_id');
            $check = Database::search(
                "SELECT s.session_id, s.updated_at, s.session_datetime
                 FROM sessions s
                 WHERE s.session_id = $sessionId AND s.counselor_id = $counselorId AND s.status = 'completed'
                 LIMIT 1"
            );
            if (!$check || $check->num_rows === 0) {
                echo json_encode(['success' => false, 'error' => 'Not found']);
                exit;
            }
            $sess        = $check->fetch_assoc();
            $completedTs = !empty($sess['updated_at']) ? strtotime($sess['updated_at']) : strtotime($sess['session_datetime']);
            $isLocked    = (time() > $completedTs + FOLLOWUP_WINDOW_DAYS * 86400);

            $msgsRs = Database::search(
                "SELECT sm.message_id, sm.sender_id, sm.message, sm.created_at,
                        COALESCE(u.display_name, u.username) AS sender_name,
                        u.profile_picture AS sender_avatar
                 FROM session_messages sm
                 JOIN users u ON u.user_id = sm.sender_id
                 WHERE sm.session_id = $sessionId
                 ORDER BY sm.created_at ASC"
            );
            $msgs      = [];
            $msgCount  = 0;
            $lastMsgId = 0;
            while ($msgsRs && ($row = $msgsRs->fetch_assoc())) {
                $isMe   = (int) $row['sender_id'] === $counselorUserId;
                $avatar = $row['sender_avatar'] ?: '/assets/img/avatar.png';
                $msgs[] = [
                    'id'      => (int) $row['message_id'],
                    'isMe'    => $isMe,
                    'name'    => $isMe ? 'You' : $row['sender_name'],
                    'avatar'  => $avatar,
                    'message' => $row['message'],
                    'time'    => date('M j, g:i A', strtotime($row['created_at'])),
                ];
                $lastMsgId = max($lastMsgId, (int) $row['message_id']);
                $msgCount++;
            }

            $daysLeft = max(0, (int) ceil(($completedTs + FOLLOWUP_WINDOW_DAYS * 86400 - time()) / 86400));

            echo json_encode([
                'success'   => true,
                'messages'  => $msgs,
                'isLocked'  => $isLocked,
                'msgCount'  => $msgCount,
                'daysLeft'  => $daysLeft,
                'lastMsgId' => $lastMsgId,
            ]);
            exit;

        /* Poll for messages newer than last_id — used by 4-second poller */
        case 'poll_messages':
            $sessionId = (int) Request::get('session_id');
            $lastId    = (int) Request::get('last_id');

            $check = Database::search(
                "SELECT s.session_id, s.updated_at, s.session_datetime
                 FROM sessions s
                 WHERE s.session_id = $sessionId AND s.counselor_id = $counselorId AND s.status = 'completed'
                 LIMIT 1"
            );
            if (!$check || $check->num_rows === 0) {
                echo json_encode(['success' => false, 'error' => 'Not found']);
                exit;
            }
            $sess        = $check->fetch_assoc();
            $completedTs = !empty($sess['updated_at']) ? strtotime($sess['updated_at']) : strtotime($sess['session_datetime']);
            $isLocked    = time() > $completedTs + FOLLOWUP_WINDOW_DAYS * 86400;

            $newRs = Database::search(
                "SELECT sm.message_id, sm.sender_id, sm.message, sm.created_at,
                        COALESCE(u.display_name, u.username) AS sender_name,
                        u.profile_picture AS sender_avatar
                 FROM session_messages sm
                 JOIN users u ON u.user_id = sm.sender_id
                 WHERE sm.session_id = $sessionId AND sm.message_id > $lastId
                 ORDER BY sm.created_at ASC"
            );
            $newMsgs   = [];
            $newLastId = $lastId;
            while ($newRs && ($row = $newRs->fetch_assoc())) {
                $isMe   = (int) $row['sender_id'] === $counselorUserId;
                $avatar = $row['sender_avatar'] ?: '/assets/img/avatar.png';
                $newMsgs[] = [
                    'id'      => (int) $row['message_id'],
                    'isMe'    => $isMe,
                    'name'    => $isMe ? 'You' : $row['sender_name'],
                    'avatar'  => $avatar,
                    'message' => $row['message'],
                    'time'    => date('M j, g:i A', strtotime($row['created_at'])),
                ];
                $newLastId = max($newLastId, (int) $row['message_id']);
            }

            echo json_encode([
                'success'   => true,
                'messages'  => $newMsgs,
                'isLocked'  => $isLocked,
                'lastMsgId' => $newLastId,
            ]);
            exit;

        /* Send a message to a session thread */
        case 'send_message':
            $sessionId = (int) Request::post('session_id');
            $msg       = trim((string) (Request::post('message') ?? ''));

            if ($sessionId <= 0 || $msg === '' || strlen($msg) > 1000) {
                echo json_encode(['success' => false, 'error' => 'Invalid data']);
                exit;
            }

            // Verify session + lock state
            $check = Database::search(
                "SELECT s.session_id, s.user_id, s.updated_at, s.session_datetime,
                        COUNT(sm.message_id) AS msg_count
                 FROM sessions s
                 LEFT JOIN session_messages sm ON sm.session_id = s.session_id
                 WHERE s.session_id = $sessionId AND s.counselor_id = $counselorId AND s.status = 'completed'
                 GROUP BY s.session_id
                 LIMIT 1"
            );
            if (!$check || $check->num_rows === 0) {
                echo json_encode(['success' => false, 'error' => 'Not found']);
                exit;
            }
            $sess        = $check->fetch_assoc();
            $completedTs = !empty($sess['updated_at']) ? strtotime($sess['updated_at']) : strtotime($sess['session_datetime']);
            $msgCount    = (int) $sess['msg_count'];

            if (time() > $completedTs + FOLLOWUP_WINDOW_DAYS * 86400) {
                echo json_encode(['success' => false, 'error' => 'Thread is closed']);
                exit;
            }

            Database::setUpConnection();
            $safeMsg       = Database::$connection->real_escape_string($msg);
            $clientUserId  = (int) $sess['user_id'];

            Database::iud("INSERT INTO session_messages (session_id, sender_id, message) VALUES ($sessionId, $counselorUserId, '$safeMsg')");
            $newMsgId = (int) Database::$connection->insert_id;

            // Notify client
            if ($clientUserId > 0) {
                $notifTitle = Database::$connection->real_escape_string('New follow-up reply');
                $notifMsg   = Database::$connection->real_escape_string('Your counselor replied to your follow-up thread.');
                $notifLink  = Database::$connection->real_escape_string("/user/sessions/follow-up?session_id=$sessionId");
                Database::iud("INSERT INTO notifications (user_id, type, title, message, link) VALUES ($clientUserId, 'followup_reply', '$notifTitle', '$notifMsg', '$notifLink')");
            }

            $timeStr  = date('M j, g:i A');
            $myAvatar = $user['profilePictureUrl'] ?? '/assets/img/avatar.png';
        echo json_encode([
            'success' => true,
            'message' => [
                'id'      => $newMsgId,
                'isMe'    => true,
                'name'    => 'You',
                'avatar'  => $myAvatar,
                'message' => $msg,
                'time'    => $timeStr,
            ],
            'msgCount' => $msgCount + 1,
            'daysLeft' => max(0, (int) ceil(($completedTs + FOLLOWUP_WINDOW_DAYS * 86400 - time()) / 86400)),
        ]);
        exit;

    case 'get_reschedule_requests':
            $items = CounselorSessionsModel::getPendingRescheduleRequests($counselorId);
            echo json_encode(['success' => true, 'requests' => $items]);
            exit;

        case 'get_no_show_disputes':
            $disputes = CounselorSessionsModel::getNoShowDisputes($counselorId);
            echo json_encode(['success' => true, 'disputes' => $disputes]);
            exit;

        case 'review_reschedule':
            $requestId = (int) Request::post('request_id');
            $action    = trim((string) (Request::post('action') ?? ''));
            $note      = trim((string) (Request::post('note') ?? ''));

            if ($requestId <= 0 || !in_array($action, ['approve', 'reject'], true)) {
                echo json_encode(['success' => false, 'error' => 'Invalid data']);
                exit;
            }

            if ($action === 'approve') {
                $ok = CounselorSessionsModel::approveReschedule($counselorId, $requestId, $counselorUserId, $note);
            } else {
                $ok = CounselorSessionsModel::rejectReschedule($counselorId, $requestId, $note);
            }

            echo json_encode(['success' => $ok, 'error' => $ok ? null : 'Request not found or already reviewed.']);
            exit;
    }

    echo json_encode(['success' => false, 'error' => 'Unknown action']);
    exit;
}

/* ── Page data ───────────────────────────────────────────────── */
$sessions = CounselorSessionsModel::getAll($counselorId);

// Build tab lists
$today         = date('Y-m-d');
$tabToday      = [];
$tabUpcoming   = [];
$tabCompleted  = [];
$tabCancelled  = [];

foreach ($sessions as $s) {
    $dt     = !empty($s['sessionDatetime']) ? strtotime((string) $s['sessionDatetime']) : false;
    $status = $s['status'] ?? '';

    if (!$dt) continue;

    $sessionDate = date('Y-m-d', $dt);

    if (in_array($status, ['cancelled', 'no_show'], true)) {
        $tabCancelled[] = $s;
    } elseif ($status === 'completed') {
        $tabCompleted[] = $s;
    } elseif ($sessionDate === $today && in_array($status, ['scheduled', 'confirmed', 'in_progress'], true)) {
        $tabToday[] = $s;
    } elseif ($sessionDate > $today && in_array($status, ['scheduled', 'confirmed'], true)) {
        $tabUpcoming[] = $s;
    } else {
        $tabCompleted[] = $s;
    }
}

// Completed sessions with thread data for the follow-up popup
$followupSessions = [];
$rs = Database::search(
    "SELECT s.session_id, s.session_datetime, s.updated_at,
            COALESCE(u.display_name, u.username, 'Client') AS client_name,
            u.profile_picture AS client_avatar,
            COUNT(sm.message_id) AS msg_count
     FROM sessions s
     JOIN users u ON u.user_id = s.user_id
     LEFT JOIN session_messages sm ON sm.session_id = s.session_id
     WHERE s.counselor_id = $counselorId AND s.status = 'completed'
     GROUP BY s.session_id
     ORDER BY s.session_datetime DESC
     LIMIT 30"
);
while ($rs && ($row = $rs->fetch_assoc())) {
    $completedTs = !empty($row['updated_at']) ? strtotime($row['updated_at']) : strtotime($row['session_datetime']);
    $daysLeft    = max(0, (int) ceil(($completedTs + FOLLOWUP_WINDOW_DAYS * 86400 - time()) / 86400));
    $isLocked    = time() > $completedTs + FOLLOWUP_WINDOW_DAYS * 86400;
    $followupSessions[] = [
        'sessionId'    => (int) $row['session_id'],
        'clientName'   => $row['client_name'],
        'clientAvatar' => $row['client_avatar'] ?: '/assets/img/avatar.png',
        'sessionDate'  => date('M j, Y', strtotime($row['session_datetime'])),
        'msgCount'     => (int) $row['msg_count'],
        'daysLeft'     => $daysLeft,
        'isLocked'     => $isLocked,
    ];
}

$searchPlaceholder  = 'Search sessions';
$searchFilterType   = 'sessions';
$pageHeaderTitle    = 'Schedule';
$pageHeaderSubtitle = 'Your session calendar and history';
