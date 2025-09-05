<?php

const FOLLOWUP_WINDOW_DAYS  = 7;

$userId = (int) $user['id'];

/* ── AJAX handlers (follow-up popup) ────────────────────── */
if ($ajaxAction = Request::get('ajax')) {
    header('Content-Type: application/json');

    switch ($ajaxAction) {

        case 'get_messages':
            $sessionId = (int) Request::get('session_id');
            $check = Database::search(
                "SELECT s.session_id, s.updated_at, s.session_datetime, s.counselor_id
                 FROM sessions s
                 WHERE s.session_id = $sessionId AND s.user_id = $userId AND s.status = 'completed'
                 LIMIT 1"
            );
            if (!$check || $check->num_rows === 0) {
                echo json_encode(['success' => false, 'error' => 'Not found']);
                exit;
            }
            $sess        = $check->fetch_assoc();
            $completedTs = !empty($sess['updated_at']) ? strtotime($sess['updated_at']) : strtotime($sess['session_datetime']);
            $isLocked    = time() > $completedTs + FOLLOWUP_WINDOW_DAYS * 86400;

            $msgsRs = Database::search(
                "SELECT sm.sender_id, sm.message, sm.created_at,
                        COALESCE(u.display_name, u.username) AS sender_name,
                        u.profile_picture AS sender_avatar
                 FROM session_messages sm
                 JOIN users u ON u.user_id = sm.sender_id
                 WHERE sm.session_id = $sessionId
                 ORDER BY sm.created_at ASC"
            );
            $msgs     = [];
            $msgCount = 0;
            while ($msgsRs && ($row = $msgsRs->fetch_assoc())) {
                $isMe   = (int) $row['sender_id'] === $userId;
                $msgs[] = [
                    'isMe'    => $isMe,
                    'name'    => $isMe ? 'You' : $row['sender_name'],
                    'avatar'  => $row['sender_avatar'] ?: '/assets/img/avatar.png',
                    'message' => $row['message'],
                    'time'    => date('M j, g:i A', strtotime($row['created_at'])),
                ];
                $msgCount++;
            }

            // isLocked is already set to time-based check above
            $daysLeft = max(0, (int) ceil(($completedTs + FOLLOWUP_WINDOW_DAYS * 86400 - time()) / 86400));

            echo json_encode([
                'success'  => true,
                'messages' => $msgs,
                'isLocked' => $isLocked,
                'msgCount' => $msgCount,
                'daysLeft' => $daysLeft,
            ]);
            exit;

        case 'send_message':
            $sessionId = (int) Request::post('session_id');
            $msg       = trim((string) (Request::post('message') ?? ''));

            if ($sessionId <= 0 || $msg === '' || strlen($msg) > 1000) {
                echo json_encode(['success' => false, 'error' => 'Invalid data']);
                exit;
            }

            $check = Database::search(
                "SELECT s.session_id, s.counselor_id, s.updated_at, s.session_datetime,
                        COUNT(sm.message_id) AS msg_count
                 FROM sessions s
                 LEFT JOIN session_messages sm ON sm.session_id = s.session_id
                 WHERE s.session_id = $sessionId AND s.user_id = $userId AND s.status = 'completed'
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
            $safeMsg = Database::$connection->real_escape_string($msg);
            Database::iud("INSERT INTO session_messages (session_id, sender_id, message) VALUES ($sessionId, $userId, '$safeMsg')");

            // Notify counselor
            $counselorUserId = 0;
            $cuRs = Database::search("SELECT u.user_id FROM counselors c JOIN users u ON u.user_id = c.user_id WHERE c.counselor_id = " . (int) $sess['counselor_id'] . " LIMIT 1");
            if ($cuRs) {
                $cuRow = $cuRs->fetch_assoc();
                $counselorUserId = (int) ($cuRow['user_id'] ?? 0);
            }

            if ($counselorUserId > 0) {
                $t = Database::$connection->real_escape_string('New follow-up message');
                $m = Database::$connection->real_escape_string('Your client sent a follow-up message.');
                $l = Database::$connection->real_escape_string("/counselor/sessions/follow-up?session_id=$sessionId");
                Database::iud("INSERT INTO notifications (user_id, type, title, message, link) VALUES ($counselorUserId, 'followup_message', '$t', '$m', '$l')");
            }

            $myAvatar = $user['profilePictureUrl'] ?? '/assets/img/avatar.png';
            $daysLeft = max(0, (int) ceil(($completedTs + FOLLOWUP_WINDOW_DAYS * 86400 - time()) / 86400));

            echo json_encode([
                'success'  => true,
                'message'  => [
                    'isMe'    => true,
                    'name'    => 'You',
                    'avatar'  => $myAvatar,
                    'message' => $msg,
                    'time'    => date('M j, g:i A'),
                ],
                'msgCount' => $msgCount + 1,
                'daysLeft' => $daysLeft,
            ]);
            exit;

        case 'submit_review':
            $sessionId  = (int) Request::post('session_id');
            $rating     = (int) Request::post('rating');
            $reviewText = trim((string) (Request::post('review') ?? ''));

            if ($sessionId <= 0 || $rating < 1 || $rating > 5) {
                echo json_encode(['success' => false, 'error' => 'Invalid data']);
                exit;
            }

            $ok = SessionsModel::submitReview($userId, $sessionId, $rating, $reviewText);
            echo json_encode(['success' => $ok, 'error' => $ok ? null : 'Could not submit review. It may have already been submitted.']);
            exit;

        case 'request_reschedule':
            $sessionId = (int) Request::post('session_id');
            $reason    = trim((string) (Request::post('reason') ?? ''));

            if ($sessionId <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid session']);
                exit;
            }

            $ok = SessionsModel::requestReschedule($userId, $sessionId, $reason);
            echo json_encode(['success' => $ok, 'error' => $ok ? null : 'Could not send request. There may already be a pending request for this session.']);
            exit;

        case 'report_no_show':
            $sessionId   = (int) Request::post('session_id');
            $description = trim((string) (Request::post('description') ?? ''));

            if ($sessionId <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid session']);
                exit;
            }

            $ok = SessionsModel::reportNoShow($userId, $sessionId, $description);
            echo json_encode(['success' => $ok, 'error' => $ok ? null : 'Could not submit report. You may have already reported this session.']);
            exit;
    }

    echo json_encode(['success' => false, 'error' => 'Unknown action']);
    exit;
}

/* ── Page data ───────────────────────────────────────────── */
$upcomingPage  = filter_var(Request::get('upage'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
$historyPage   = filter_var(Request::get('hpage'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
$reportsPage   = filter_var(Request::get('rpage'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
$requestedTab  = (string)(Request::get('tab') ?? 'upcoming');
$activeTab     = in_array($requestedTab, ['upcoming', 'history', 'reports'], true) ? $requestedTab : 'upcoming';

$upcomingData  = SessionsModel::getSessionsByType($userId, 'upcoming', $upcomingPage, 5);
$historyData   = SessionsModel::getSessionsByType($userId, 'history',  $historyPage,  5);
$reportsData   = SessionsModel::getReportItems($userId, $reportsPage, 5);

$upcomingSessions     = $upcomingData['items'];
$historySessions      = $historyData['items'];
$reportItems          = $reportsData['items'];
$upcomingCurrentPage  = (int) $upcomingData['page'];
$upcomingTotalPages   = (int) $upcomingData['totalPages'];
$upcomingTotal        = (int) $upcomingData['total'];
$historyCurrentPage   = (int) $historyData['page'];
$historyTotalPages    = (int) $historyData['totalPages'];
$historyTotal         = (int) $historyData['total'];
$reportsCurrentPage   = (int) $reportsData['page'];
$reportsTotalPages    = (int) $reportsData['totalPages'];
$reportsTotal         = (int) $reportsData['total'];

// Completed sessions for the follow-up popup
$followupSessions = [];
$rs = Database::search(
    "SELECT s.session_id, s.session_datetime, s.updated_at,
            COALESCE(cu.display_name, cu.username, 'Counselor') AS counselor_name,
            cu.profile_picture AS counselor_avatar,
            COUNT(sm.message_id) AS msg_count
     FROM sessions s
     JOIN counselors c ON c.counselor_id = s.counselor_id
     JOIN users cu ON cu.user_id = c.user_id
     LEFT JOIN session_messages sm ON sm.session_id = s.session_id
     WHERE s.user_id = $userId AND s.status = 'completed'
     GROUP BY s.session_id
     ORDER BY s.session_datetime DESC
     LIMIT 20"
);
while ($rs && ($row = $rs->fetch_assoc())) {
    $completedTs = !empty($row['updated_at']) ? strtotime($row['updated_at']) : strtotime($row['session_datetime']);
    $daysLeft    = max(0, (int) ceil(($completedTs + FOLLOWUP_WINDOW_DAYS * 86400 - time()) / 86400));
    $isLocked    = time() > $completedTs + FOLLOWUP_WINDOW_DAYS * 86400;
    $followupSessions[] = [
        'sessionId'       => (int) $row['session_id'],
        'counselorName'   => $row['counselor_name'],
        'counselorAvatar' => $row['counselor_avatar'] ?: '/assets/img/avatar.png',
        'sessionDate'     => date('M j, Y', strtotime($row['session_datetime'])),
        'msgCount'        => (int) $row['msg_count'],
        'daysLeft'        => $daysLeft,
        'isLocked'        => $isLocked,
    ];
}

$pageTitle = 'Sessions';
$pageStyle = ['user/dashboard', 'user/sessions'];
