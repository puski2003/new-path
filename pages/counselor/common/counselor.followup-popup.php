<?php
/**
 * Follow-up thread popup — included on the counselor sessions page.
 * Mirrors the community chat popup pattern.
 */
$followupSessions   = $followupSessions ?? [];
$activeThreadCount  = count(array_filter($followupSessions, fn($s) => $s['msgCount'] > 0 && !$s['isLocked']));
?>

<button class="followup-toggle-btn" id="followupToggleBtn" aria-label="Open follow-up threads">
    <i data-lucide="message-square" stroke-width="2"></i>
    <?php if ($activeThreadCount > 0): ?>
    <span class="fu-notif-badge"><?= $activeThreadCount > 99 ? '99+' : $activeThreadCount ?></span>
    <?php endif; ?>
</button>

<div class="followup-popup" id="followupPopup">

    <!-- Header -->
    <div class="fu-popup-header">
        <h4>Follow-up Threads</h4>
        <button class="fu-close-btn" id="followupCloseBtn" aria-label="Close">
            <i data-lucide="x" stroke-width="2"></i>
        </button>
    </div>

    <!-- Session list -->
    <div class="fu-session-list" id="followupSessionList">
        <?php if (empty($followupSessions)): ?>
        <div class="fu-popup-empty">
            <i data-lucide="message-square" stroke-width="1.5"></i>
            <h4>No follow-up threads</h4>
            <p>Threads open after completed sessions.</p>
        </div>
        <?php else: ?>
            <?php foreach ($followupSessions as $fs): ?>
            <div class="fu-session-item"
                 data-session-id="<?= (int) $fs['sessionId'] ?>"
                 data-name="<?= htmlspecialchars($fs['clientName']) ?>"
                 data-avatar="<?= htmlspecialchars($fs['clientAvatar']) ?>"
                 data-date="<?= htmlspecialchars($fs['sessionDate']) ?>">
                <img src="<?= htmlspecialchars($fs['clientAvatar']) ?>" alt="" />
                <div class="fu-session-item-info">
                    <p class="fu-session-item-name"><?= htmlspecialchars($fs['clientName']) ?></p>
                    <p class="fu-session-item-meta"><?= htmlspecialchars($fs['sessionDate']) ?></p>
                </div>
                <?php if ($fs['isLocked']): ?>
                    <span class="fu-locked-label">Closed</span>
                <?php elseif ($fs['msgCount'] > 0): ?>
                    <span class="fu-count-badge"><?= $fs['msgCount'] ?></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Thread view (hidden until a session is selected) -->
    <div class="fu-thread-view" id="followupThreadView">

        <div class="fu-thread-header">
            <button class="fu-back-btn" id="followupBackBtn" aria-label="Back to list">
                <i data-lucide="arrow-left" stroke-width="2"></i>
            </button>
            <div class="fu-thread-person">
                <img id="followupThreadAvatar" src="/assets/img/avatar.png" alt="" />
                <div>
                    <h4 id="followupThreadName">Client</h4>
                    <span id="followupThreadMeta">Follow-up thread</span>
                </div>
            </div>
        </div>

        <div class="fu-messages" id="followupMessages">
            <div class="fu-loading">Loading…</div>
        </div>

        <div id="followupCompose" class="fu-compose">
            <input type="text" id="followupInput" class="" placeholder="Type a message…" maxlength="1000" />
            <button class="fu-send-btn" id="followupSendBtn" aria-label="Send">
                <i data-lucide="send" stroke-width="2"></i>
            </button>
        </div>

    </div>

</div>
