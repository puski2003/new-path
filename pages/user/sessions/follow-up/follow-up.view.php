<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/../../common/user.html.head.php'; ?>
<body>
<main class="main-container">
    <?php $activePage = 'sessions'; require_once __DIR__ . '/../../common/user.sidebar.php'; ?>

    <section class="main-content">
        <img src="/assets/img/main-content-head.svg" alt="" class="main-header-bg-image" />

        <div class="main-content-header">
            <div class="main-content-header-text">
                <h2>Follow-up Thread</h2>
                <p>Post-session messages with your counselor.</p>
            </div>
        </div>

        <div class="main-content-body">
            <div class="followup-container">

                <!-- Back -->
                <div class="back-navigation" style="margin-bottom:var(--spacing-lg);">
                    <a href="/user/sessions" class="back-btn" title="Back to Sessions">
                        <i data-lucide="chevron-left" style="width:18px;height:18px;"></i>
                    </a>
                </div>

                <!-- Thread header card -->
                <div class="followup-header-card">
                    <img src="<?= htmlspecialchars($session['counselor_avatar'] ?? '/assets/img/avatar.png') ?>"
                         class="followup-counselor-avatar" alt="Counselor" />
                    <div class="followup-header-info">
                        <p class="followup-specialty"><?= htmlspecialchars($session['specialty'] ?? '') ?></p>
                        <h3 class="followup-counselor-name"><?= htmlspecialchars($session['counselor_name']) ?></h3>
                        <p class="followup-session-date">
                            Session on <?= date('M j, Y g:i A', $sessionTs) ?>
                        </p>
                    </div>
                    <div class="followup-thread-status">
                        <div class="followup-counters">
                            <div class="followup-counter">
                                <i data-lucide="message-square" style="width:14px;height:14px;"></i>
                                <span><?= $msgCount ?> message<?= $msgCount !== 1 ? 's' : '' ?></span>
                            </div>
                            <?php if (!$isExpired): ?>
                            <div class="followup-counter <?= $daysLeft <= 1 ? 'urgent' : '' ?>">
                                <i data-lucide="clock" style="width:14px;height:14px;"></i>
                                <span><?= $daysLeft ?> day<?= $daysLeft !== 1 ? 's' : '' ?> left</span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($isLocked): ?>
                            <span class="followup-badge locked-badge">
                                <i data-lucide="lock" style="width:12px;height:12px;"></i>
                                Thread Closed
                            </span>
                        <?php else: ?>
                            <span class="followup-badge open-badge">
                                <i data-lucide="unlock" style="width:12px;height:12px;"></i>
                                Open
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Messages area -->
                <div class="followup-messages <?= empty($messages) ? 'empty' : '' ?>">
                    <?php if (empty($messages)): ?>
                        <div class="followup-empty-state">
                            <i data-lucide="message-circle" style="width:40px;height:40px;color:var(--color-text-muted);display:block;margin:0 auto var(--spacing-md);"></i>
                            <p>No messages yet. Start the conversation below.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $msg):
                            $isMe    = (int)$msg['sender_id'] === $userId;
                            $avatar  = !empty($msg['sender_avatar']) ? $msg['sender_avatar'] : '/assets/img/avatar.png';
                            $timeStr = date('M j, g:i A', strtotime($msg['created_at']));
                        ?>
                        <div class="followup-message <?= $isMe ? 'message-mine' : 'message-theirs' ?>">
                            <?php if (!$isMe): ?>
                            <img src="<?= htmlspecialchars($avatar) ?>" class="message-avatar" alt="" />
                            <?php endif; ?>
                            <div class="message-bubble-wrap">
                                <span class="message-sender">
                                    <?= $isMe ? 'You' : htmlspecialchars($msg['sender_name']) ?>
                                </span>
                                <div class="message-bubble"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                                <span class="message-time"><?= $timeStr ?></span>
                            </div>
                            <?php if ($isMe): ?>
                            <img src="<?= htmlspecialchars($avatar) ?>" class="message-avatar" alt="" />
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Compose area / Locked state -->
                <?php if ($isLocked): ?>
                <div class="followup-locked">
                    <i data-lucide="lock" style="width:24px;height:24px;color:var(--color-text-muted);"></i>
                    <div>
                        <p class="followup-locked-title">Follow-up window closed</p>
                        <p class="followup-locked-sub">The 7-day follow-up period has ended.</p>
                    </div>
                    <a href="/user/counselors?tab=find" class="btn btn-primary" style="font-size:var(--font-size-sm);">
                        Book Another Session →
                    </a>
                </div>
                <?php else: ?>
                <form method="POST" class="followup-compose" id="fuForm">
                    <input type="hidden" name="session_id" value="<?= $sessionId ?>">
                    <?php if ($sendError): ?>
                        <p class="followup-error"><?= htmlspecialchars($sendError) ?></p>
                    <?php endif; ?>
                    <div class="followup-input-row">
                        <textarea name="message" id="fuTextarea" class="followup-textarea"
                                  placeholder="Write a follow-up message…"
                                  maxlength="1000" rows="3" required></textarea>
                        <button type="submit" class="btn btn-primary followup-send-btn" id="fuSendBtn">
                            <i data-lucide="send" style="width:16px;height:16px;"></i>
                        </button>
                    </div>
                    <p class="followup-hint" id="fuHint">
                        <?= $daysLeft ?> day<?= $daysLeft !== 1 ? 's' : '' ?> left
                    </p>
                </form>
                <?php endif; ?>

            </div>
        </div>
    </section>
</main>

<script>
lucide.createIcons();

const msgs      = document.querySelector('.followup-messages');
if (msgs) msgs.scrollTop = msgs.scrollHeight;

const form      = document.getElementById('fuForm');
const textarea  = document.getElementById('fuTextarea');
const sendBtn   = document.getElementById('fuSendBtn');
const hint      = document.getElementById('fuHint');
const sessionId = <?= $sessionId ?>;
const myAvatar  = <?= json_encode($user['profilePictureUrl'] ?? '/assets/img/avatar.png') ?>;
let lastMsgId   = <?= $lastMsgId ?>;
let fuLocked    = <?= $isLocked ? 'true' : 'false' ?>;

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function appendBubble(msg) {
    const div = document.createElement('div');
    div.className = 'followup-message ' + (msg.isMe ? 'message-mine' : 'message-theirs');
    const avatarHtml = '<img src="' + escHtml(msg.avatar) + '" class="message-avatar" alt="" />';
    const wrapHtml =
        '<div class="message-bubble-wrap">' +
            '<span class="message-sender">' + escHtml(msg.name) + '</span>' +
            '<div class="message-bubble">' + escHtml(msg.message).replace(/\n/g, '<br>') + '</div>' +
            '<span class="message-time">' + escHtml(msg.time) + '</span>' +
        '</div>';
    div.innerHTML = msg.isMe ? wrapHtml + avatarHtml : avatarHtml + wrapHtml;

    const empty = msgs.querySelector('.followup-empty-state');
    if (empty) { msgs.classList.remove('empty'); empty.remove(); }

    msgs.appendChild(div);
    msgs.scrollTop = msgs.scrollHeight;
}

/* ── Send ────────────────────────────────────────────────────── */
if (form) {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const text = textarea.value.trim();
        if (!text || fuLocked) return;

        sendBtn.disabled = true;

        const fd = new FormData();
        fd.append('session_id', sessionId);
        fd.append('message', text);

        fetch('/user/sessions/follow-up?session_id=' + sessionId + '&ajax=send', {
            method: 'POST',
            body: fd,
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.success) return;
            textarea.value = '';

            const bubble = {
                isMe: true,
                name: 'You',
                avatar: myAvatar,
                message: data.message.text || data.message.message || '',
                time: data.message.time,
            };
            appendBubble(bubble);

            if (data.message.id) lastMsgId = Math.max(lastMsgId, data.message.id);
            if (hint) hint.textContent = data.daysLeft + ' day' + (data.daysLeft !== 1 ? 's' : '') + ' left';
        })
        .finally(function () { sendBtn.disabled = false; });
    });
}

/* ── Poll ────────────────────────────────────────────────────── */
const followupPoller = window.NewPathPolling.createTask({
    interval: 4000,
    shouldRun: function () {
        return !fuLocked;
    },
    request: function () {
        return fetch('/user/sessions/follow-up?session_id=' + sessionId + '&ajax=poll&last_id=' + lastMsgId)
            .then(function (r) { return r.json(); });
    },
    onSuccess: function (data) {
        if (!data || !data.success) return;
        if (data.isLocked) fuLocked = true;
        (data.messages || []).forEach(function (m) {
            if (!m.isMe) {
                appendBubble(m);
            }
            lastMsgId = Math.max(lastMsgId, m.id || 0);
        });
    }
});

if (!fuLocked) {
    followupPoller.start();
}
</script>
<?php require_once __DIR__ . '/../../common/user.footer.php'; ?>
</body>
</html>
