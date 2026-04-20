<?php

require_once __DIR__ . '/../../common/counselor.head.php';

$counselorId     = (int) ($user['counselorId'] ?? 0);
$counselorUserId = (int) ($user['id'] ?? 0);
$sessionId       = (int) (Request::get('session_id') ?? Request::post('session_id') ?? 0);

if ($sessionId <= 0) {
    Response::redirect('/counselor/sessions');
}

const FOLLOWUP_MAX_MESSAGES = 5;
const FOLLOWUP_WINDOW_DAYS  = 7;

/* ── Fetch session + client info ─────────────────────────── */
$rs = Database::search(
    "SELECT s.session_id, s.user_id, s.counselor_id, s.session_datetime, s.status, s.updated_at,
            COALESCE(u.display_name, u.username, 'Client') AS client_name,
            u.profile_picture AS client_avatar
     FROM sessions s
     JOIN users u ON u.user_id = s.user_id
     WHERE s.session_id   = $sessionId
       AND s.counselor_id = $counselorId
       AND s.status       = 'completed'
     LIMIT 1"
);

if (!$rs || $rs->num_rows === 0) {
    Response::redirect('/counselor/sessions');
}
$session = $rs->fetch_assoc();

$completedTs = !empty($session['updated_at']) ? strtotime($session['updated_at']) : strtotime($session['session_datetime']);
$sessionTs   = strtotime($session['session_datetime']);
$expiresTs   = $completedTs + (FOLLOWUP_WINDOW_DAYS * 86400);
$daysLeft    = max(0, (int) ceil(($expiresTs - time()) / 86400));
$isExpired   = time() > $expiresTs;

/* ── Fetch messages ──────────────────────────────────────── */
$messages = [];
$msgCount = 0;
$msgsRs   = Database::search(
    "SELECT sm.message_id, sm.sender_id, sm.message, sm.created_at,
            COALESCE(u.display_name, u.username) AS sender_name,
            u.profile_picture AS sender_avatar
     FROM session_messages sm
     JOIN users u ON u.user_id = sm.sender_id
     WHERE sm.session_id = $sessionId
     ORDER BY sm.created_at ASC"
);
if ($msgsRs) {
    while ($row = $msgsRs->fetch_assoc()) {
        $messages[] = $row;
    }
    $msgCount = count($messages);
}
$isLocked = $isExpired || $msgCount >= FOLLOWUP_MAX_MESSAGES;

/* ── AJAX send ───────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Request::get('ajax') === 'send') {
    header('Content-Type: application/json');
    $msg = trim((string) (Request::post('message') ?? ''));

    if ($isLocked) {
        echo json_encode(['success' => false, 'error' => 'Thread is closed']);
        exit;
    }
    if ($msg === '' || strlen($msg) > 1000) {
        echo json_encode(['success' => false, 'error' => 'Invalid message']);
        exit;
    }

    Database::setUpConnection();
    $safeMsg      = Database::$connection->real_escape_string($msg);
    $clientUserId = (int) $session['user_id'];

    Database::iud("INSERT INTO session_messages (session_id, sender_id, message) VALUES ($sessionId, $counselorUserId, '$safeMsg')");

    if ($clientUserId > 0) {
        $t = Database::$connection->real_escape_string('New follow-up reply');
        $m = Database::$connection->real_escape_string('Your counselor replied to your follow-up thread.');
        $l = Database::$connection->real_escape_string("/user/sessions/follow-up?session_id=$sessionId");
        Database::iud("INSERT INTO notifications (user_id, type, title, message, link) VALUES ($clientUserId, 'followup_reply', '$t', '$m', '$l')");
    }

    echo json_encode([
        'success' => true,
        'message' => [
            'isMe'    => true,
            'sender'  => 'You',
            'avatar'  => $user['profilePictureUrl'] ?? '/assets/img/avatar.png',
            'text'    => $msg,
            'time'    => date('M j, g:i A'),
        ],
        'msgCount' => $msgCount + 1,
        'daysLeft' => $daysLeft,
    ]);
    exit;
}

/* ── Form POST fallback ──────────────────────────────────── */
$sendError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = trim((string) (Request::post('message') ?? ''));

    if ($isLocked) {
        $sendError = 'This follow-up thread is closed.';
    } elseif ($msg === '' || strlen($msg) > 1000) {
        $sendError = 'Message must be between 1 and 1,000 characters.';
    } else {
        Database::setUpConnection();
        $safeMsg      = Database::$connection->real_escape_string($msg);
        $clientUserId = (int) $session['user_id'];

        Database::iud("INSERT INTO session_messages (session_id, sender_id, message) VALUES ($sessionId, $counselorUserId, '$safeMsg')");

        if ($clientUserId > 0) {
            $t = Database::$connection->real_escape_string('New follow-up reply');
            $m = Database::$connection->real_escape_string('Your counselor replied to your follow-up thread.');
            $l = Database::$connection->real_escape_string("/user/sessions/follow-up?session_id=$sessionId");
            Database::iud("INSERT INTO notifications (user_id, type, title, message, link) VALUES ($clientUserId, 'followup_reply', '$t', '$m', '$l')");
        }

        Response::redirect("/counselor/sessions/follow-up?session_id=$sessionId");
    }
}

/* ── Layout vars ─────────────────────────────────────────── */
$pageTitle          = 'Follow-up Thread';
$pageStyle          = ['counselor/followUp'];
$pageHeaderTitle    = 'Follow-up Thread';
$pageHeaderSubtitle = 'Post-session messages with your client';
$pageScripts        = ['/assets/js/components/followup-thread.js'];
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../../common/counselor.html.head.php'; ?>
<body>
<main class="main-container theme-counselor">
    <?php require __DIR__ . '/../../common/counselor.sidebar.php'; ?>

    <section class="main-content">
        <?php require __DIR__ . '/../../common/counselor.page-header.php'; ?>

        <div class="main-content-body">
            <div class="inner-body-content">
                <div class="body-column">

                    <div class="cc-back-row">
                        <a href="/counselor/sessions" class="cc-back-btn">
                            <i data-lucide="chevron-left"></i> Back to Schedule
                        </a>
                    </div>

                    <!-- Thread header card -->
                    <div class="followup-container">
                        <div class="followup-header-card">
                            <img src="<?= htmlspecialchars($session['client_avatar'] ?? '/assets/img/avatar.png') ?>"
                                 class="followup-client-avatar" alt="Client" />
                            <div class="followup-header-info">
                                <p class="followup-role-label">Client</p>
                                <h3 class="followup-client-name"><?= htmlspecialchars($session['client_name']) ?></h3>
                                <p class="followup-session-date">Session on <?= date('M j, Y g:i A', $sessionTs) ?></p>
                            </div>
                            <div class="followup-thread-status">
                                <div class="followup-counters">
                                    <div class="followup-counter <?= $isLocked ? 'locked' : '' ?>">
                                        <i data-lucide="message-square" style="width:13px;height:13px;"></i>
                                        <span><?= $msgCount ?>/<?= FOLLOWUP_MAX_MESSAGES ?> messages</span>
                                    </div>
                                    <?php if (!$isExpired): ?>
                                    <div class="followup-counter <?= $daysLeft <= 1 ? 'urgent' : '' ?>">
                                        <i data-lucide="clock" style="width:13px;height:13px;"></i>
                                        <span><?= $daysLeft ?> day<?= $daysLeft !== 1 ? 's' : '' ?> left</span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php if ($isLocked): ?>
                                    <span class="followup-badge locked-badge">
                                        <i data-lucide="lock" style="width:11px;height:11px;"></i> Thread Closed
                                    </span>
                                <?php else: ?>
                                    <span class="followup-badge open-badge">
                                        <i data-lucide="unlock" style="width:11px;height:11px;"></i> Open
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Messages -->
                        <div class="followup-messages <?= empty($messages) ? 'empty' : '' ?>" id="fuMessages">
                            <?php if (empty($messages)): ?>
                                <div class="followup-empty-state">
                                    <i data-lucide="message-circle" style="width:38px;height:38px;display:block;margin:0 auto var(--spacing-sm);"></i>
                                    <p>No messages yet. The client hasn't started this thread.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($messages as $msg):
                                    $isMe   = (int) $msg['sender_id'] === $counselorUserId;
                                    $avatar = !empty($msg['sender_avatar']) ? $msg['sender_avatar'] : '/assets/img/avatar.png';
                                ?>
                                <div class="followup-message <?= $isMe ? 'message-mine' : '' ?>">
                                    <?php if (!$isMe): ?>
                                    <img src="<?= htmlspecialchars($avatar) ?>" class="message-avatar" alt="" />
                                    <?php endif; ?>
                                    <div class="message-bubble-wrap">
                                        <span class="message-sender"><?= $isMe ? 'You' : htmlspecialchars($msg['sender_name']) ?></span>
                                        <div class="message-bubble"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                                        <span class="message-time"><?= date('M j, g:i A', strtotime($msg['created_at'])) ?></span>
                                    </div>
                                    <?php if ($isMe): ?>
                                    <img src="<?= htmlspecialchars($avatar) ?>" class="message-avatar" alt="" />
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Compose / Locked -->
                        <?php if ($isLocked): ?>
                        <div class="followup-locked">
                            <i data-lucide="lock" style="width:22px;height:22px;flex-shrink:0;"></i>
                            <div>
                                <p class="followup-locked-title">Follow-up window closed</p>
                                <p class="followup-locked-sub">
                                    <?= $isExpired ? 'The 7-day follow-up period has ended.' : 'The 5-message limit has been reached.' ?>
                                </p>
                            </div>
                        </div>
                        <?php else: ?>
                        <?php if ($sendError): ?>
                            <p class="followup-error"><?= htmlspecialchars($sendError) ?></p>
                        <?php endif; ?>
                        <form method="POST" class="followup-compose" id="fuForm">
                            <input type="hidden" name="session_id" value="<?= $sessionId ?>">
                            <div class="followup-input-row">
                                <textarea name="message" id="fuTextarea" class="followup-textarea"
                                          placeholder="Write a reply…" maxlength="1000" rows="3" required></textarea>
                                <button type="submit" class="btn btn-primary followup-send-btn" id="fuSendBtn">
                                    <i data-lucide="send" style="width:16px;height:16px;"></i>
                                </button>
                            </div>
                            <p class="followup-hint">
                                <?= FOLLOWUP_MAX_MESSAGES - $msgCount ?> message<?= (FOLLOWUP_MAX_MESSAGES - $msgCount) !== 1 ? 's' : '' ?> remaining
                                · <?= $daysLeft ?> day<?= $daysLeft !== 1 ? 's' : '' ?> left
                            </p>
                        </form>
                        <?php endif; ?>

                    </div><!-- /.followup-container -->
                </div>
            </div>
        </div>
    </section>
</main>

<script>
lucide.createIcons();

// Scroll to bottom on load
const msgs = document.getElementById('fuMessages');
if (msgs) msgs.scrollTop = msgs.scrollHeight;

// AJAX submit
const form     = document.getElementById('fuForm');
const textarea = document.getElementById('fuTextarea');
const sendBtn  = document.getElementById('fuSendBtn');
const sessionId = <?= $sessionId ?>;

if (form) {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const text = textarea.value.trim();
        if (!text) return;

        sendBtn.disabled = true;

        const fd = new FormData();
        fd.append('session_id', sessionId);
        fd.append('message', text);

        fetch('/counselor/sessions/follow-up?session_id=' + sessionId + '&ajax=send', {
            method: 'POST',
            body: fd,
        })
        .then(r => r.json())
        .then(function (data) {
            if (!data.success) return;

            textarea.value = '';

            // Append message to UI
            const myAvatar = <?= json_encode($user['profilePictureUrl'] ?? '/assets/img/avatar.png') ?>;
            const bubble = `<div class="followup-message message-mine">
                <div class="message-bubble-wrap">
                    <span class="message-sender">You</span>
                    <div class="message-bubble">${escHtml(data.message.text)}</div>
                    <span class="message-time">${escHtml(data.message.time)}</span>
                </div>
                <img src="${escHtml(myAvatar)}" class="message-avatar" alt="" />
            </div>`;

            // Remove empty state if present
            const empty = msgs.querySelector('.followup-empty-state');
            if (empty) { msgs.classList.remove('empty'); empty.remove(); }

            msgs.insertAdjacentHTML('beforeend', bubble);
            msgs.scrollTop = msgs.scrollHeight;

            // Update hint
            const hint = form.querySelector('.followup-hint');
            const remaining = 5 - data.msgCount;
            if (hint) hint.textContent = remaining + ' message' + (remaining !== 1 ? 's' : '') + ' remaining · ' + data.daysLeft + ' day' + (data.daysLeft !== 1 ? 's' : '') + ' left';

            if (data.msgCount >= 5) location.reload();
        })
        .finally(function () { sendBtn.disabled = false; });
    });
}

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
</script>
<?php require __DIR__ . '/../../common/counselor.footer.php'; ?>
</body>
</html>
