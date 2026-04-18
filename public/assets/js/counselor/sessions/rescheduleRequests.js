(function () {
    'use strict';

    var overlay      = document.getElementById('rescheduleReviewOverlay');
    var reviewNote   = document.getElementById('rescheduleReviewNote');
    var reviewMeta   = document.getElementById('rescheduleReviewMeta');
    var reviewReason = document.getElementById('rescheduleReviewReason');
    var reviewError  = document.getElementById('rescheduleReviewError');
    var approveBtn   = document.getElementById('approveRescheduleBtn');
    var rejectBtn    = document.getElementById('rejectRescheduleBtn');
    var badge        = document.getElementById('rescheduleBadge');
    var listEl       = document.getElementById('reschedule-requests-list');

    var currentRequestId = 0;

    // ── Load requests when tab is shown ──────────────────────────────
    var tabBtn = document.getElementById('btn-reschedule');
    if (tabBtn) {
        tabBtn.addEventListener('click', loadRequests);
    }

    function loadRequests() {
        if (!listEl) return;
        fetch('/counselor/sessions?ajax=get_reschedule_requests')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) return;
                renderRequests(data.requests || []);
            })
            .catch(function () {});
    }

    function renderRequests(requests) {
        while (listEl.firstChild) listEl.removeChild(listEl.firstChild);

        // Update badge
        if (badge) {
            if (requests.length > 0) {
                badge.textContent = requests.length;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }

        if (requests.length === 0) {
            var empty = document.createElement('p');
            empty.className = 'empty-state-text';
            empty.textContent = 'No pending reschedule requests.';
            listEl.appendChild(empty);
            return;
        }

        requests.forEach(function (req) {
            listEl.appendChild(buildRequestCard(req));
        });
    }

    function buildRequestCard(req) {
        var card = document.createElement('div');
        card.className = 'reschedule-request-card';
        card.dataset.requestId = req.requestId;

        var avatar = document.createElement('img');
        avatar.src = req.clientAvatar;
        avatar.alt = req.clientName;
        avatar.className = 'counselors-image';

        var info = document.createElement('div');
        info.className = 'counselor-session-info';

        var name = document.createElement('h4');
        name.textContent = req.clientName;

        var date = document.createElement('span');
        date.textContent = req.sessionDate;

        var meta = document.createElement('div');
        meta.className = 'session-card-meta';

        var typePill = document.createElement('span');
        typePill.className = 'session-type-pill';
        typePill.textContent = req.sessionType === 'in_person' ? 'In Person' : req.sessionType.charAt(0).toUpperCase() + req.sessionType.slice(1);

        var requestedAt = document.createElement('span');
        requestedAt.className = 'plan-status';
        requestedAt.textContent = 'Requested ' + req.requestedAt;

        meta.appendChild(typePill);
        meta.appendChild(requestedAt);

        var actions = document.createElement('div');
        actions.className = 'session-action-row';

        var reviewBtn = document.createElement('button');
        reviewBtn.className = 'btn-join';
        reviewBtn.type = 'button';
        reviewBtn.textContent = 'Review';
        reviewBtn.addEventListener('click', function () {
            openReviewModal(req);
        });

        actions.appendChild(reviewBtn);

        info.appendChild(name);
        info.appendChild(date);
        info.appendChild(meta);

        if (req.reason) {
            var reasonEl = document.createElement('p');
            reasonEl.style.cssText = 'font-size:var(--font-size-sm);color:var(--color-text-secondary);margin:4px 0 0;';
            reasonEl.textContent = '"' + req.reason + '"';
            info.appendChild(reasonEl);
        }

        info.appendChild(actions);
        card.appendChild(info);
        card.appendChild(avatar);
        return card;
    }

    // ── Review modal ──────────────────────────────────────────────────
    function openReviewModal(req) {
        currentRequestId = req.requestId;
        if (reviewNote) reviewNote.value = '';
        if (reviewError) reviewError.style.display = 'none';
        if (reviewMeta) reviewMeta.textContent = req.clientName + ' · ' + req.sessionDate;
        if (reviewReason) reviewReason.textContent = req.reason ? '"' + req.reason + '"' : 'No reason provided.';
        if (overlay) {
            overlay.style.display = 'flex';
            overlay.offsetHeight;
            overlay.classList.add('show');
        }
    }

    function closeReviewModal() {
        if (!overlay) return;
        overlay.classList.remove('show');
        setTimeout(function () { overlay.style.display = 'none'; }, 300);
    }

    [
        document.getElementById('closeRescheduleReview'),
        document.getElementById('closeRescheduleReview2'),
    ].forEach(function (btn) {
        if (btn) btn.addEventListener('click', closeReviewModal);
    });
    if (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) closeReviewModal();
        });
    }

    function submitReview(action) {
        if (currentRequestId <= 0) return;
        var note = reviewNote ? reviewNote.value.trim() : '';

        if (approveBtn) approveBtn.disabled = true;
        if (rejectBtn) rejectBtn.disabled = true;
        if (reviewError) reviewError.style.display = 'none';

        var fd = new FormData();
        fd.append('request_id', currentRequestId);
        fd.append('action', action);
        fd.append('note', note);

        fetch('/counselor/sessions?ajax=review_reschedule', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    closeReviewModal();
                    // Remove card from list
                    var card = listEl ? listEl.querySelector('[data-request-id="' + currentRequestId + '"]') : null;
                    if (card) card.remove();
                    // Decrement badge
                    if (badge && badge.style.display !== 'none') {
                        var count = parseInt(badge.textContent, 10) - 1;
                        if (count <= 0) {
                            badge.style.display = 'none';
                            if (listEl && listEl.children.length === 0) {
                                var empty = document.createElement('p');
                                empty.className = 'empty-state-text';
                                empty.textContent = 'No pending reschedule requests.';
                                listEl.appendChild(empty);
                            }
                        } else {
                            badge.textContent = count;
                        }
                    }
                } else {
                    if (reviewError) {
                        reviewError.textContent = data.error || 'Could not complete the action.';
                        reviewError.style.display = 'block';
                    }
                    if (approveBtn) approveBtn.disabled = false;
                    if (rejectBtn) rejectBtn.disabled = false;
                }
            })
            .catch(function () {
                if (reviewError) {
                    reviewError.textContent = 'Network error. Please try again.';
                    reviewError.style.display = 'block';
                }
                if (approveBtn) approveBtn.disabled = false;
                if (rejectBtn) rejectBtn.disabled = false;
            });
    }

    if (approveBtn) approveBtn.addEventListener('click', function () { submitReview('approve'); });
    if (rejectBtn)  rejectBtn.addEventListener('click',  function () { submitReview('reject'); });

    // Pre-load badge count on page load
    fetch('/counselor/sessions?ajax=get_reschedule_requests')
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.success) return;
            var count = (data.requests || []).length;
            if (badge && count > 0) {
                badge.textContent = count;
                badge.style.display = 'inline-block';
            }
        })
        .catch(function () {});
}());
