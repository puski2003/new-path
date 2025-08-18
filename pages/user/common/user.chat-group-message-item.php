<?php
$message = $message ?? [];
$isOwnMessage = !empty($message['isOwnMessage']);
$avatar = !empty($message['profilePicture']) ? $message['profilePicture'] : '/assets/img/avatar.png';
$senderName = (string)($message['senderName'] ?? 'User');
$content = (string)($message['content'] ?? '');
$memberRole = (string)($message['memberRole'] ?? 'member');
$timestamp = !empty($message['createdAt']) ? strtotime((string)$message['createdAt']) : false;
$formattedTime = $timestamp ? date('M j, g:i A', $timestamp) : '';
$isPinned = !empty($message['isPinned']);
?>
<div class="message-wrapper <?= $isOwnMessage ? 'own' : 'other' ?>">
    <?php if (!$isOwnMessage): ?>
        <div class="message-sender-info">
            <div class="message-avatar">
                <img src="<?= htmlspecialchars($avatar) ?>" alt="" />
            </div>
            <span class="message-sender-name"><?= htmlspecialchars($senderName) ?></span>
            <?php if ($memberRole === 'leader'): ?>
                <span class="member-role-badge leader">Leader</span>
            <?php elseif ($memberRole === 'moderator'): ?>
                <span class="member-role-badge moderator">Mod</span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <div class="message-bubble <?= $isPinned ? 'pinned-message' : '' ?>">
        <?php if ($isPinned): ?>
            <span class="pinned-badge">&#128204;</span>
        <?php endif; ?>
        <?= nl2br(htmlspecialchars($content)) ?>
    </div>
    <span class="message-time"><?= htmlspecialchars($formattedTime) ?></span>
</div>
