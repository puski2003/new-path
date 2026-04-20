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
                <?php if ($session['isReported']): ?>
                    <button disabled class="btn-warning" type="button" >Reported</button>
                <?php else: ?>
                    <button class="btn-warning" type="button" onclick="showReportPopup(<?= (int)$session['sessionId'] ?>)">Report</button>
                <?php endif; ?>
                <div id="reportPopup-<?= (int)$session['sessionId'] ?>" class="report-popup">
                    <div class="report-popup-content">
                        <div class="report-popup-close">
                            <span onclick="closeReportPopup(<?= (int)$session['sessionId'] ?>)" style="cursor:pointer;">&times;</span>
                        </div>
                        <div class="report-popup-form">
                            <label for="reason">Reason</label>
                            <select name="reason" id="reportTitle-<?= (int)$session['sessionId'] ?>">
                                <option value="">Select reason</option>
                                <option value="no_show">No Show</option> 
                                <option value="other">Other</option>
                            </select>
                            <textarea id="reportDesc-<?= (int)$session['sessionId'] ?>" placeholder="Report Description"></textarea>                            
                            <button class="btn-danger" type="button" onclick="sendReport(<?=(int)$counselorUserId  ?>,<?=(int)$session['sessionId'] ?>)">Send Report</button>
                        </div>
                    </div>
                </div>
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

    function showReportPopup(id) {
    document.getElementById('reportPopup-' + id).style.display = 'block';
    }
    
    function closeReportPopup(id) {
        document.getElementById('reportPopup-' + id).style.display = 'none';
    }
    function sendReport(counselorUserId, sessionId){
        const reason = document.getElementById('reportTitle-' + sessionId).value;
        const desc  = document.getElementById('reportDesc-' + sessionId).value;

        if (!reason) {
            alert('Reason is required');
            return;
        }

        if (!desc) {
            alert('Description is required');
            return;
        }
    
        if (desc.length < 10) {
            alert('Description must be at least 10 characters');
            return;
        }
        console.log(`Valid = send to backend ${reason} ${desc}`);
        const body =
            'ajax=send_report' +
            '&counselorUser_id=' + encodeURIComponent(counselorUserId) +
            '&session_id=' + encodeURIComponent(sessionId) +
            '&reason=' + encodeURIComponent(reason) +
            '&description=' + encodeURIComponent(desc);

        fetch('/counselor/sessions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: body
        })
        .then(async function (res) {
            const text = await res.text();
            console.log("RAW RESPONSE:", text);
            
            return JSON.parse(text);

        })
        .then(function (data) {
            if (data.success) {
                alert('Report sent successfully');

                document.getElementById('reportTitle-' + sessionId).value = '';
                document.getElementById('reportDesc-' + sessionId).value = '';

                closeReportPopup(sessionId);
                window.location.reload();
            } else {
                alert('Failed to send report');
            }
        })
        .catch(function () {
            alert('Network error');
        });
    }
    
        

                        

</script>