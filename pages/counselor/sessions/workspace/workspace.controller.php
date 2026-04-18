<?php

require_once __DIR__ . '/../../../user/sessions/extend/extend.model.php';

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

    // ── Extension: counselor sends request ───────────────
    if ($ajaxAction === 'request_extension' && $sessionId > 0) {
        // Verify session belongs to this counselor and is active
        $sessRs = Database::search(
            "SELECT s.user_id, c.consultation_fee
             FROM sessions s
             JOIN counselors c ON c.counselor_id = s.counselor_id
             WHERE s.session_id  = $sessionId
               AND s.counselor_id = $counselorId
               AND s.status NOT IN ('completed','cancelled','no_show')
             LIMIT 1"
        );
        $sessRow = $sessRs ? $sessRs->fetch_assoc() : null;
        if (!$sessRow) {
            echo json_encode(['success' => false, 'error' => 'Session not found or already ended.']);
            exit;
        }

        $clientUserId = (int)$sessRow['user_id'];
        $hourlyRate   = (float)$sessRow['consultation_fee'];
        $duration     = (int)(Request::post('duration_minutes') ?? 0);
        $allOptions   = ExtendModel::buildOptions($hourlyRate);
        $options      = array_values(array_filter($allOptions, fn($o) => (int)$o['duration_minutes'] === $duration));
        if (empty($options)) {
            echo json_encode(['success' => false, 'error' => 'Invalid duration selected.']);
            exit;
        }
        $extId = ExtendModel::createRequest($sessionId, $counselorId, $clientUserId, $options);

        if ($extId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Could not create extension request.']);
            exit;
        }

        // In-app notification for client
        require_once __DIR__ . '/../../../../core/NotificationService.php';
        Database::setUpConnection();
        $nTitle = Database::$connection->real_escape_string('Session Extension Request');
        $nMsg   = Database::$connection->real_escape_string('Your counselor would like to extend your current session. Please review the options.');
        $nLink  = Database::$connection->real_escape_string('/user/sessions?id=' . $sessionId);
        Database::iud(
            "INSERT INTO notifications (user_id, type, title, message, link)
             VALUES ($clientUserId, 'extension_request', '$nTitle', '$nMsg', '$nLink')"
        );

        // Email to client
        require_once __DIR__ . '/../../../../core/Mailer.php';
        $clientRs = Database::search(
            "SELECT email, COALESCE(display_name, CONCAT(first_name,' ',last_name), username, 'Client') AS name
             FROM users WHERE user_id = $clientUserId LIMIT 1"
        );
        $clientRow = $clientRs ? $clientRs->fetch_assoc() : null;
        if ($clientRow && !empty($clientRow['email'])) {
            $clientName = $clientRow['name'];
            $optRows = '';
            foreach ($options as $opt) {
                $optRows .= "<tr>
                    <td style='padding:8px 12px;border-bottom:1px solid #eee;'>{$opt['duration_minutes']} minutes</td>
                    <td style='padding:8px 12px;border-bottom:1px solid #eee;'>LKR " . number_format($opt['fee'], 2) . "</td>
                </tr>";
            }
            $emailHtml = "
                <div style='font-family:Montserrat,sans-serif;max-width:520px;margin:auto;padding:32px;'>
                    <h2 style='color:#2c3e50;margin-bottom:8px;'>Session Extension Request</h2>
                    <p style='color:#555;'>Hi " . htmlspecialchars($clientName) . ", your counselor has requested to extend your current session.</p>
                    <table style='width:100%;border-collapse:collapse;margin:20px 0;background:#f9f9f9;border-radius:8px;overflow:hidden;'>
                        <thead>
                            <tr style='background:#4CAF50;color:#fff;'>
                                <th style='padding:10px 12px;text-align:left;'>Duration</th>
                                <th style='padding:10px 12px;text-align:left;'>Fee</th>
                            </tr>
                        </thead>
                        <tbody>$optRows</tbody>
                    </table>
                    <p style='color:#555;'>This request will expire in <strong>10 minutes</strong>. Please respond promptly.</p>
                    <a href='/user/sessions?id=$sessionId'
                       style='display:inline-block;padding:12px 28px;background:#4CAF50;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;margin-top:8px;'>
                        View &amp; Respond
                    </a>
                    <p style='color:#999;font-size:0.85rem;margin-top:24px;'>Thank you for choosing NewPath.</p>
                </div>";
            Mailer::send($clientRow['email'], 'NewPath — Session Extension Request', $emailHtml, $clientName);
        }

        echo json_encode(['success' => true, 'extensionId' => $extId]);
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

// Fetch all extension requests for this session
$extensionRequests = [];
$extRs = Database::search(
    "SELECT extension_id, extension_options, status,
            selected_duration_minutes, selected_fee,
            DATE_SUB(expires_at, INTERVAL 10 MINUTE) AS sent_at,
            responded_at
     FROM session_extension_requests
     WHERE session_id = $sessionId AND counselor_id = $counselorId
     ORDER BY extension_id DESC"
);
if ($extRs) {
    while ($row = $extRs->fetch_assoc()) {
        $opt = (json_decode((string)$row['extension_options'], true) ?: [[]])[0] ?? [];
        $extensionRequests[] = [
            'extensionId'     => (int)$row['extension_id'],
            'durationMinutes' => (int)($row['selected_duration_minutes'] ?: ($opt['duration_minutes'] ?? 0)),
            'fee'             => (float)($row['selected_fee'] ?: ($opt['fee'] ?? 0)),
            'status'          => $row['status'],
            'sentAt'          => $row['sent_at'],
            'respondedAt'     => $row['responded_at'],
        ];
    }
}
