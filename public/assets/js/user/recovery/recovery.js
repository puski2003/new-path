document.addEventListener('DOMContentLoaded', function () {
  const moodItems = document.querySelectorAll('.mood-item');
  moodItems.forEach(function (item) {
    item.addEventListener('click', function () {
      moodItems.forEach(function (m) { m.classList.remove('active'); });
      item.classList.add('active');
    });
  });

  const saveReflectionBtn = document.querySelector('.save-reflection-btn');
  const reflectionTextarea = document.querySelector('.reflection-textarea');
  const recentReflections = document.querySelector('.recent-reflections');

  if (saveReflectionBtn && reflectionTextarea && recentReflections) {
    saveReflectionBtn.addEventListener('click', function () {
      const text = reflectionTextarea.value.trim();
      const activeMood = document.querySelector('.mood-item.active .mood-emoji');
      const mood = activeMood ? activeMood.textContent : '';

      if (!text) {
        showNotification('Please write something before saving', 'error');
        return;
      }

      const newReflection = document.createElement('div');
      newReflection.className = 'reflection-item';
      newReflection.innerHTML = '<span class="reflection-date">Today ' + mood + ': ' + escapeHtml(text) + '</span>';

      const recentTitle = recentReflections.querySelector('.recent-title');
      if (recentTitle) {
        recentTitle.insertAdjacentElement('afterend', newReflection);
      } else {
        recentReflections.prepend(newReflection);
      }

      reflectionTextarea.value = '';
      showNotification('Reflection saved successfully!', 'success');
    });
  }

  bindClick('.view-analytics-btn', function () {
    showNotification('Opening detailed analytics dashboard...', 'info');
  });

  bindClick('.view-all-progress-btn', function () {
    showNotification('Opening comprehensive progress tracker...', 'info');
  });


  bindClickAll('.review-btn', function () {
    showNotification('Recovery plan review opened', 'info');
  });

  bindClickAll('.create-plan-btn', function () {
    showNotification('Recovery plan opened', 'info');
  });

  bindClick('.join-session-btn', function () {
    showNotification('Joining session...', 'success');
  });

  bindClick('.request-feedback-btn', function () {
    showNotification('Feedback request sent to counselor', 'success');
  });

  // Fallback: if buttons/forms are wired to the current page as placeholders,
  // provide visible feedback instead of silent "no-op" reloads.
  wireRecoveryPlaceholders();

  animateProgressBars();
  initTasksPagination();
  initTaskCompletion();
  initCopingTools();
});

function initTasksPagination() {
  var TASKS_PER_PAGE = 4;
  var cards = Array.from(document.querySelectorAll('.tasks-list .task-card'));
  var pagination = document.getElementById('tasksPagination');
  var prevBtn = document.getElementById('tasksPrev');
  var nextBtn = document.getElementById('tasksNext');
  var pageInfo = document.getElementById('tasksPageInfo');

  if (!pagination || cards.length <= TASKS_PER_PAGE) return;

  var currentPage = 1;
  var totalPages = Math.ceil(cards.length / TASKS_PER_PAGE);

  function render() {
    var start = (currentPage - 1) * TASKS_PER_PAGE;
    var end = start + TASKS_PER_PAGE;
    cards.forEach(function (card, i) {
      card.style.display = (i >= start && i < end) ? '' : 'none';
    });
    pageInfo.textContent = currentPage + ' / ' + totalPages;
    prevBtn.disabled = currentPage === 1;
    nextBtn.disabled = currentPage === totalPages;
  }

  pagination.style.display = 'flex';
  prevBtn.addEventListener('click', function () { if (currentPage > 1) { currentPage--; render(); } });
  nextBtn.addEventListener('click', function () { if (currentPage < totalPages) { currentPage++; render(); } });
  render();
}

function bindClick(selector, handler) {
  const el = document.querySelector(selector);
  if (el) el.addEventListener('click', handler);
}

function bindClickAll(selector, handler) {
  const els = document.querySelectorAll(selector);
  els.forEach(function (el) {
    el.addEventListener('click', handler);
  });
}

function wireRecoveryPlaceholders() {
  const currentPath = window.location.pathname;

  document.querySelectorAll('.recovery-container a.btn').forEach(function (link) {
    link.addEventListener('click', function (e) {
      const href = link.getAttribute('href') || '';
      if (!href || href === '#' || href === currentPath || href === '/user/recovery') {
        e.preventDefault();
        showNotification('This action is not wired yet in PHP. I can implement this route next.', 'info');
      }
    });
  });

  document.querySelectorAll('.recovery-container form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      const action = form.getAttribute('action') || '';
      if (!action || action === currentPath || action === '/user/recovery') {
        e.preventDefault();
        showNotification('This form action is not wired yet in PHP. I can implement it next.', 'info');
      }
    });
  });
}

function animateProgressBars() {
  const progressBars = document.querySelectorAll('.progress-fill');
  progressBars.forEach(function (bar) {
    const width = bar.style.width;
    bar.style.width = '0%';
    setTimeout(function () {
      bar.style.width = width;
    }, 300);
  });
}

function showNotification(message, type) {
  if (window.NewPathToast) {
    window.NewPathToast.show(message, type || "info");
  }
}

function showProgressModal(defaultType) {
  let modal = document.querySelector('.progress-modal.dynamic-progress-modal');
  if (!modal) {
    modal = createProgressModal();
    document.body.appendChild(modal);
  }

  const typeSelect = modal.querySelector('#progress-type');
  if (defaultType && typeSelect) typeSelect.value = defaultType;

  modal.classList.add('active');
}

function createProgressModal() {
  const modal = document.createElement('div');
  modal.className = 'progress-modal dynamic-progress-modal';
  modal.innerHTML = [
    '<div class="modal-content">',
    '  <div class="modal-header">',
    '    <h3 class="modal-title">Log Your Progress</h3>',
    '    <button class="modal-close">&times;</button>',
    '  </div>',
    '  <form class="log-form">',
    '    <div class="form-group">',
    '      <label class="form-label">Progress Type</label>',
    '      <select class="form-select" id="progress-type">',
    '        <option value="mood">Mood Check-in</option>',
    '        <option value="urge">Urge/Craving</option>',
    '        <option value="milestone">Milestone</option>',
    '        <option value="setback">Setback</option>',
    '      </select>',
    '    </div>',
    '    <div class="form-group">',
    '      <label class="form-label">Notes</label>',
    '      <textarea class="form-textarea" id="progress-notes" placeholder="How are you feeling today?"></textarea>',
    '    </div>',
    '    <div class="progress-actions">',
    '      <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>',
    '      <button type="submit" class="btn btn-primary">Save Progress</button>',
    '    </div>',
    '  </form>',
    '</div>'
  ].join('');

  const closeBtn = modal.querySelector('.modal-close');
  const cancelBtn = modal.querySelector('.modal-cancel');
  const form = modal.querySelector('.log-form');

  if (closeBtn) closeBtn.addEventListener('click', function () { modal.classList.remove('active'); });
  if (cancelBtn) cancelBtn.addEventListener('click', function () { modal.classList.remove('active'); });

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      modal.classList.remove('active');
      form.reset();
      showNotification('Progress logged successfully!', 'success');
    });
  }

  modal.addEventListener('click', function (e) {
    if (e.target === modal) modal.classList.remove('active');
  });

  return modal;
}

function initCopingTools() {
  // Open modals
  document.querySelectorAll('.tool-item[data-tool]').forEach(function (item) {
    item.addEventListener('click', function () {
      var modal = document.getElementById('modal-' + item.dataset.tool);
      if (modal) { modal.classList.add('open'); modal.setAttribute('aria-hidden', 'false'); }
    });
  });

  // Close modals
  document.querySelectorAll('.coping-modal-close').forEach(function (btn) {
    btn.addEventListener('click', function () { closeModal(btn.closest('.coping-modal')); });
  });
  document.querySelectorAll('.coping-modal').forEach(function (modal) {
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(modal); });
  });

  function closeModal(modal) {
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
  }

  // ── Urge Surfing Timer ────────────────────────────────────────────
  var urgeTotal = 15 * 60;
  var urgeLeft  = urgeTotal;
  var urgeTimer = null;
  var urgeInstructions = [
    'Focus on the sensation. Observe it without acting on it.',
    'Notice where you feel it in your body. Just watch it.',
    'Urges are like waves — they rise and fall. You are surfing it.',
    'You are halfway there. The wave is starting to recede.',
    'Almost done. You did not act on the urge. That is strength.',
  ];

  var urgeLabel = document.getElementById('urgeTimerLabel');
  var urgeInstEl = document.getElementById('urgeInstruction');
  var urgeStartBtn = document.getElementById('urgeStartBtn');
  var urgeResetBtn = document.getElementById('urgeResetBtn');

  function urgeFormat(s) {
    var m = Math.floor(s / 60);
    var sec = s % 60;
    return (m < 10 ? '0' : '') + m + ':' + (sec < 10 ? '0' : '') + sec;
  }

  function urgeUpdateInstruction() {
    var idx = Math.floor((1 - urgeLeft / urgeTotal) * urgeInstructions.length);
    idx = Math.min(idx, urgeInstructions.length - 1);
    if (urgeInstEl) urgeInstEl.textContent = urgeInstructions[idx];
  }

  if (urgeStartBtn) {
    urgeStartBtn.addEventListener('click', function () {
      if (urgeTimer) return;
      urgeStartBtn.style.display = 'none';
      if (urgeResetBtn) urgeResetBtn.style.display = '';
      urgeTimer = setInterval(function () {
        urgeLeft--;
        if (urgeLabel) urgeLabel.textContent = urgeFormat(urgeLeft);
        urgeUpdateInstruction();
        if (urgeLeft <= 0) {
          clearInterval(urgeTimer);
          urgeTimer = null;
          if (urgeLabel) urgeLabel.textContent = 'Done!';
          if (urgeInstEl) urgeInstEl.textContent = 'You rode out the urge. Great work.';
        }
      }, 1000);
    });
  }

  if (urgeResetBtn) {
    urgeResetBtn.addEventListener('click', function () {
      clearInterval(urgeTimer);
      urgeTimer = null;
      urgeLeft = urgeTotal;
      if (urgeLabel) urgeLabel.textContent = urgeFormat(urgeLeft);
      if (urgeInstEl) urgeInstEl.textContent = urgeInstructions[0];
      urgeStartBtn.style.display = '';
      urgeResetBtn.style.display = 'none';
    });
  }

  // Reset timer when modal closes
  var urgeModal = document.getElementById('modal-urge-surfing');
  if (urgeModal) {
    urgeModal.addEventListener('click', function (e) {
      if (e.target === urgeModal || e.target.classList.contains('coping-modal-close')) {
        if (urgeResetBtn) urgeResetBtn.click();
      }
    });
  }

  // ── Grounding Exercise ────────────────────────────────────────────
  var groundingSteps = [
    { num: '5', prompt: 'Name <strong>5 things</strong> you can <strong>see</strong> right now.' },
    { num: '4', prompt: 'Name <strong>4 things</strong> you can <strong>touch</strong> or feel.' },
    { num: '3', prompt: 'Name <strong>3 things</strong> you can <strong>hear</strong>.' },
    { num: '2', prompt: 'Name <strong>2 things</strong> you can <strong>smell</strong>.' },
    { num: '1', prompt: 'Name <strong>1 thing</strong> you can <strong>taste</strong>.' },
  ];
  var groundingIdx = 0;

  var groundingNum    = document.getElementById('groundingNum');
  var groundingPrompt = document.getElementById('groundingPrompt');
  var groundingNext   = document.getElementById('groundingNextBtn');
  var groundingDots   = document.querySelectorAll('.grounding-dot');

  function groundingRender() {
    var step = groundingSteps[groundingIdx];
    if (groundingNum) groundingNum.textContent = step.num;
    if (groundingPrompt) groundingPrompt.innerHTML = step.prompt;
    groundingDots.forEach(function (d, i) {
      d.classList.toggle('active', i <= groundingIdx);
    });
    if (groundingNext) {
      groundingNext.textContent = groundingIdx < groundingSteps.length - 1 ? 'Next' : 'Finish';
    }
  }

  if (groundingNext) {
    groundingNext.addEventListener('click', function () {
      if (groundingIdx < groundingSteps.length - 1) {
        groundingIdx++;
        groundingRender();
      } else {
        groundingIdx = 0;
        groundingRender();
        var modal = document.getElementById('modal-grounding');
        if (modal) { modal.classList.remove('open'); modal.setAttribute('aria-hidden', 'true'); }
        showNotification('Grounding complete. You are present.', 'success');
      }
    });
  }

  var groundingModal = document.getElementById('modal-grounding');
  if (groundingModal) {
    groundingModal.addEventListener('click', function (e) {
      if (e.target === groundingModal || e.target.classList.contains('coping-modal-close')) {
        groundingIdx = 0;
        groundingRender();
      }
    });
  }
}

function initTaskCompletion() {
  document.querySelectorAll('.task-complete-form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();

      var taskId = form.querySelector('input[name="taskId"]').value;
      var card   = form.closest('.task-card');

      fetch('/user/recovery/task/complete-ajax', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'taskId=' + encodeURIComponent(taskId),
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (!data.success) {
            window.location.href = '/user/recovery?taskBlocked=1';
            return;
          }

          // Entire plan just finished — go straight to celebration page
          if (data.planCompleted && data.planId) {
            window.location.href = '/user/recovery/plan-completed?planId=' + data.planId;
            return;
          }

          setTaskCardCompleted(card, true);

          showUndoToast('Task marked complete.', 5000, function () {
            fetch('/user/recovery/task/uncomplete', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: 'taskId=' + encodeURIComponent(taskId),
            })
              .then(function (r) { return r.json(); })
              .then(function (undoData) {
                if (undoData.success) {
                  setTaskCardCompleted(card, false);
                  showNotification('Task restored to pending.', 'info');
                }
              })
              .catch(function () {
                showNotification('Could not undo. Please refresh.', 'error');
              });
          });
        })
        .catch(function () {
          form.submit();
        });
    });
  });
}

function setTaskCardCompleted(card, completed) {
  var iconEl   = card.querySelector('.task-icon');
  var statusEl = card.querySelector('.task-status-text');
  var formEl   = card.querySelector('.task-complete-form');

  if (completed) {
    if (statusEl) card.dataset.originalStatus = statusEl.textContent.trim();

    if (iconEl) {
      iconEl.className = 'task-icon completed';
      iconEl.innerHTML = '<i data-lucide="check-circle" stroke-width="2"></i>';
    }
    if (statusEl) statusEl.textContent = 'Completed';
    if (formEl)   formEl.style.display = 'none';
  } else {
    if (iconEl) {
      iconEl.className = 'task-icon pending';
      iconEl.innerHTML = '<i data-lucide="circle" stroke-width="2"></i>';
    }
    if (statusEl) statusEl.textContent = card.dataset.originalStatus || '';
    if (formEl)   formEl.style.display = '';
  }

  if (window.lucide) lucide.createIcons();
}

function showUndoToast(message, duration, onUndo) {
  var existing = document.querySelector('.undo-task-toast');
  if (existing) existing.remove();

  // Ensure slide-up animation exists
  if (!document.getElementById('undo-toast-style')) {
    var style = document.createElement('style');
    style.id = 'undo-toast-style';
    style.textContent =
      '@keyframes undoToastIn{from{transform:translate(-50%,20px);opacity:0}to{transform:translate(-50%,0);opacity:1}}' +
      '@keyframes undoToastOut{from{transform:translate(-50%,0);opacity:1}to{transform:translate(-50%,20px);opacity:0}}';
    document.head.appendChild(style);
  }

  var toast = document.createElement('div');
  toast.className = 'undo-task-toast';
  toast.innerHTML =
    '<span style="flex:1;font-size:14px;font-weight:500;">' + escapeHtml(message) + '</span>' +
    '<button type="button" class="undo-task-toast__btn">Undo</button>' +
    '<div style="position:absolute;bottom:0;left:0;right:0;height:3px;background:rgba(255,255,255,0.25);border-radius:0 0 10px 10px;overflow:hidden;">' +
    '  <div class="undo-task-toast__bar" style="height:100%;width:100%;background:rgba(255,255,255,0.7);transition:width ' + duration + 'ms linear;"></div>' +
    '</div>';

  toast.style.cssText = [
    'position:fixed',
    'bottom:28px',
    'left:50%',
    'transform:translateX(-50%)',
    'display:flex',
    'align-items:center',
    'gap:14px',
    'min-width:320px',
    'max-width:460px',
    'padding:14px 18px 20px',
    'border-radius:10px',
    'box-shadow:0 10px 30px rgba(0,0,0,0.2)',
    'z-index:9999',
    'background:#10b981',
    'color:#fff',
    'animation:undoToastIn 0.25s ease',
  ].join(';');

  var undoBtn = toast.querySelector('.undo-task-toast__btn');
  undoBtn.style.cssText = [
    'background:rgba(255,255,255,0.2)',
    'border:1px solid rgba(255,255,255,0.5)',
    'color:#fff',
    'padding:5px 14px',
    'border-radius:6px',
    'font-size:13px',
    'font-weight:600',
    'cursor:pointer',
    'white-space:nowrap',
    'flex-shrink:0',
    'transition:background 0.15s',
  ].join(';');

  document.body.appendChild(toast);

  // Trigger countdown bar (needs a frame gap to transition from 100% → 0%)
  var fill = toast.querySelector('.undo-task-toast__bar');
  requestAnimationFrame(function () {
    requestAnimationFrame(function () {
      fill.style.width = '0%';
    });
  });

  var dismissed = false;

  function dismiss() {
    if (dismissed) return;
    dismissed = true;
    clearTimeout(timer);
    toast.style.animation = 'undoToastOut 0.25s ease forwards';
    setTimeout(function () { if (toast.parentNode) toast.remove(); }, 250);
  }

  var timer = setTimeout(dismiss, duration);

  undoBtn.addEventListener('click', function () {
    dismiss();
    onUndo();
  });
}

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

