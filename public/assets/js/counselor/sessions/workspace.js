(function () {
    'use strict';

    var root = document.querySelector('.main-container.theme-counselor[data-session-id]');
    if (!root) return;

    var sessionId = parseInt(root.getAttribute('data-session-id'), 10) || 0;
    var textarea = document.getElementById('privateNotesInput');
    var notesStatus = document.getElementById('notesSaveStatus');
    var saveNotesBtn = document.getElementById('saveNotesBtn');
    var completeBtn = document.getElementById('markCompletedBtn');
    var statusBadge = document.getElementById('sessionStatusBadge');
    var completeOverlay = document.getElementById('completeSessionOverlay');
    var completeError = document.getElementById('completeSessionError');
    var confirmCompleteBtn = document.getElementById('confirmCompleteSessionBtn');
    var closeCompleteBtn = document.getElementById('closeCompleteSessionModal');
    var cancelCompleteBtn = document.getElementById('cancelCompleteSessionBtn');

    if (sessionId <= 0 || !textarea || !notesStatus || !saveNotesBtn) return;

    function showNotesStatus(message, kind) {
        notesStatus.textContent = message;
        notesStatus.className = 'ws-save-status' + (kind ? ' ' + kind : '');
    }

    function clearNotesStatus() {
        showNotesStatus('', '');
    }

    function saveNotes() {
        var body = 'notes=' + encodeURIComponent(textarea.value);
        fetch('/counselor/sessions/workspace?ajax=save_notes&session_id=' + sessionId, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                showNotesStatus(data.success ? 'Saved' : 'Error saving', data.success ? 'ws-save-ok' : 'ws-save-err');
                window.setTimeout(clearNotesStatus, 3000);
            })
            .catch(function () {
                showNotesStatus('Error', 'ws-save-err');
            });
    }

    function openCompleteModal() {
        if (!completeOverlay) return;
        if (completeError) {
            completeError.textContent = '';
            completeError.style.display = 'none';
        }
        if (confirmCompleteBtn) confirmCompleteBtn.disabled = false;
        if (cancelCompleteBtn) cancelCompleteBtn.disabled = false;
        if (closeCompleteBtn) closeCompleteBtn.disabled = false;
        completeOverlay.style.display = 'flex';
        completeOverlay.offsetHeight;
        completeOverlay.classList.add('show');
    }

    function closeCompleteModal() {
        if (!completeOverlay) return;
        completeOverlay.classList.remove('show');
        window.setTimeout(function () {
            if (!completeOverlay.classList.contains('show')) {
                completeOverlay.style.display = 'none';
            }
        }, 250);
    }

    function setCompleteModalError(message) {
        if (!completeError) return;
        completeError.textContent = message;
        completeError.style.display = 'block';
    }

    function markSessionCompleted() {
        if (!confirmCompleteBtn || !statusBadge || !completeBtn) return;

        confirmCompleteBtn.disabled = true;
        if (cancelCompleteBtn) cancelCompleteBtn.disabled = true;
        if (closeCompleteBtn) closeCompleteBtn.disabled = true;
        if (completeError) completeError.style.display = 'none';

        fetch('/counselor/sessions/workspace?ajax=mark_completed&session_id=' + sessionId, {
            method: 'POST'
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    throw new Error('Unable to update session');
                }

                statusBadge.textContent = data.label || 'Completed';
                statusBadge.className = 'plan-status status-' + (data.status || 'completed');
                completeBtn.innerHTML = '<i data-lucide="check-circle" stroke-width="2" width="16" height="16"></i> Completed';
                completeBtn.disabled = true;
                closeCompleteModal();

                if (window.lucide && typeof window.lucide.createIcons === 'function') {
                    window.lucide.createIcons();
                }
            })
            .catch(function () {
                setCompleteModalError('Unable to mark the session as completed right now.');
                confirmCompleteBtn.disabled = false;
                if (cancelCompleteBtn) cancelCompleteBtn.disabled = false;
                if (closeCompleteBtn) closeCompleteBtn.disabled = false;
            });
    }

    saveNotesBtn.addEventListener('click', saveNotes);
    textarea.addEventListener('blur', saveNotes);

    if (completeBtn && completeOverlay && confirmCompleteBtn) {
        completeBtn.addEventListener('click', openCompleteModal);
        confirmCompleteBtn.addEventListener('click', markSessionCompleted);

        [closeCompleteBtn, cancelCompleteBtn].forEach(function (button) {
            if (button) button.addEventListener('click', closeCompleteModal);
        });

        completeOverlay.addEventListener('click', function (event) {
            if (event.target === completeOverlay) {
                closeCompleteModal();
            }
        });
    }
}());
