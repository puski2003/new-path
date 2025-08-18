<?php
$message = $message ?? [];
$isOwnMessage = !empty($message['isOwnMessage']);
$avatar = !empty($message['profilePicture']) ? $message['profilePicture'] : '/assets/img/avatar.png';
$senderName = (string)($message['senderName'] ?? 'User');
$content = (string)($message['content'] ?? '');
$timestamp = !empty($message['createdAt']) ? strtotime((string)$message['createdAt']) : false;
$formattedTime = $timestamp ? date('M j, g:i A', $timestamp) : '';
?>
<div class="message-wrapper <?= $isOwnMessage ? 'own' : 'other' ?>">
    <?php if (!$isOwnMessage): ?>
        <div class="message-sender-info">
            <div class="message-avatar">
                <img src="<?= htmlspecialchars($avatar) ?>" alt="" />
            </div>
            <span class="message-sender-name"><?= htmlspecialchars($senderName) ?></span>
        </div>
    <?php endif; ?>
    <div class="message-bubble"><?= nl2br(htmlspecialchars($content)) ?></div>
    <span class="message-time"><?= htmlspecialchars($formattedTime) ?></span>
</div>
