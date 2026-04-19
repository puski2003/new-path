<?php
$session  ??= [];
$isUpcoming = !empty($isUpcoming);

$sessionTs  = !empty($session['sessionDatetime']) ? strtotime((string) $session['sessionDatetime']) : null;
$displayTime = $sessionTs ? date('D, M j \a\t g:i A', $sessionTs) : 'Schedule unavailable';
$clientName  = $session['userName'] ?? 'Client';
$clientAvatar = $session['userAvatar'] ?? '/assets/img/avatar.png';
$sessionType = $session['sessionType'] ?? 'video';
$status      = $session['status'] ?? ($isUpcoming ? 'scheduled' : 'completed');
$sessionNote=$session['sessionNotes'] ?? '';
$typeLabel = match ($sessionType) {
    'in_person' => 'In Person',
    'audio'     => 'Audio',
    'chat'      => 'Chat',
    default     => 'Video',
};
?>
<div class="counselor-session-card" data-session-id="<?= (int) ($session['sessionId'] ?? 0) ?>">
    <div class="counselor-session-info">
        <h4><?= htmlspecialchars($clientName) ?></h4>
        <span><?= htmlspecialchars($displayTime) ?></span>
        <div class="session-card-meta">
            <span class="session-type-pill"><?= htmlspecialchars($typeLabel) ?></span>
            <span class="plan-status status-<?= htmlspecialchars($status) ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $status))) ?></span>
        </div>
        <?php if ($isUpcoming): ?>
            <div class="session-action-row">
                <a class="btn-join" href="/counselor/sessions/workspace?session_id=<?= (int)($session['sessionId'] ?? 0) ?>">Join</a>
            </div>
        <?php else: ?>
            <div class="session-action-row">
                <button class="btn-join" type="button" onclick="showNotesPopup(<?= (int)$session['sessionId'] ?>)">View Notes</button>
                <div id="notesPopup-<?= (int)$session['sessionId'] ?>" class="notes-popup">
                    <div class="notes-popup-content">
                        <div class="notes-popup-close">
                            <span onclick="closeNotesPopup(<?= (int)$session['sessionId'] ?>)" style="cursor:pointer;">&times;</span>
                        </div>
                        <?php if(!empty($sessionNote)):?>
                            <div class="notes-popup-text">
                                <p><?= htmlspecialchars($sessionNote) ?></p>
                            </div>
                        <?php else:?>
                            <div class="notes-popup-text">
                                <p>No Session Notes</p>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </div>
                <button class="btn-warning" type="button">Report</button>
            </div>
        <?php endif; ?>
    </div>
    <img src="<?= htmlspecialchars($clientAvatar) ?>" alt="<?= htmlspecialchars($clientName) ?>" class="counselors-image" onerror="this.src='/assets/img/avatar.png'" />
</div>
<script>
    function showNotesPopup(id) {
    document.getElementById('notesPopup-' + id).style.display = 'block';
    }
    function closeNotesPopup(id) {
        document.getElementById('notesPopup-' + id).style.display = 'none';
    }

</script>