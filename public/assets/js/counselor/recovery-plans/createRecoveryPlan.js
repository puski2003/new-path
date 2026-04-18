document.addEventListener('DOMContentLoaded', function () {
    initializeDates();
    setupAIGeneration();

    const form = document.getElementById('createPlan-form') || document.getElementById('updatePlan-form');
    if (form) {
        form.addEventListener('submit', function (e) {
            clearErrors();
            if (!validateForm()) e.preventDefault();
        });
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// AI Plan Generation — calls the real Gemini backend endpoint
// ─────────────────────────────────────────────────────────────────────────────

function setupAIGeneration() {
    const btn = document.getElementById('generatePlanBtn');
    if (btn) btn.addEventListener('click', generatePlanFromAI);
}

async function generatePlanFromAI() {
    const promptInput = document.getElementById('aiPrompt');
    const prompt      = promptInput ? promptInput.value.trim() : '';

    if (!prompt) {
        showNotification('Please describe the recovery plan you want to create.', 'error');
        return;
    }

    const btn         = document.getElementById('generatePlanBtn');
    const originalHTML = btn.innerHTML;

    btn.innerHTML = '<i data-lucide="loader-circle" style="width:14px;height:14px;margin-right:6px;animation:spin 1s linear infinite;" stroke-width="1.5"></i>Generating…';
    btn.disabled  = true;
    if (typeof lucide !== 'undefined') lucide.createIcons();

    try {
        const res = await fetch('/counselor/recovery-plans/ai-generate', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ prompt }),
        });

        const data = await res.json();

        if (!res.ok || !data.ok) {
            throw new Error(data.error || 'Failed to generate plan');
        }

        fillFormWithPlan(data.plan);
        if (promptInput) promptInput.value = '';
        showNotification('Plan generated! Review and adjust as needed.', 'success');
    } catch (err) {
        console.error('AI generation error:', err);
        showNotification(err.message || 'Failed to generate plan. Please try again.', 'error');
    } finally {
        btn.innerHTML = originalHTML;
        btn.disabled  = false;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Fill form with generated plan data
// ─────────────────────────────────────────────────────────────────────────────

function fillFormWithPlan(plan) {
    setVal('title',                  plan.title        || '');
    setVal('planGoal',               plan.goal         || '');
    setVal('description',            plan.description  || '');
    setVal('startDate',              plan.startDate    || '');
    setVal('endDate',                plan.endDate      || '');
    setVal('targetCompletionDate',   plan.endDate      || '');
    setVal('notes',                  plan.notes        || '');
    setVal('customNotes',            plan.notes        || '');

    // Goals
    setVal('shortTermGoalTitle', plan.shortTermGoalTitle || '');
    setVal('shortTermGoalDays',  plan.shortTermGoalDays  || '');
    setVal('longTermGoalTitle',  plan.longTermGoalTitle  || '');
    setVal('longTermGoalDays',   plan.longTermGoalDays   || '');

    // Phases
    if (plan.phases) {
        for (let phaseNum = 1; phaseNum <= 3; phaseNum++) {
            const phaseData = plan.phases[String(phaseNum)] || plan.phases[phaseNum];
            const container = document.getElementById('phase-' + phaseNum + '-tasks');
            if (!container || !phaseData) continue;

            container.innerHTML = '';

            normalisePhaseItems(phaseData.tasks).forEach(function (task) {
                const title      = typeof task === 'string' ? task : (task.title      || '');
                const type       = typeof task === 'object' ? (task.type       || 'custom') : 'custom';
                const recurrence = typeof task === 'object' ? (task.recurrence || '')       : '';
                addTaskWithValue(phaseNum, title, type, recurrence);
            });

            normalisePhaseItems(phaseData.milestones).forEach(function (m) {
                addMilestoneWithValue(phaseNum, m);
            });
        }
    }
}

function normalisePhaseItems(value) {
    if (Array.isArray(value)) {
        return value;
    }

    if (typeof value === 'string') {
        return value
            .split(/\n+/)
            .map(function (item) { return item.replace(/^\s*[-*•]?\s*/, '').trim(); })
            .filter(Boolean);
    }

    return [];
}

function setVal(id, value) {
    const el = document.getElementById(id);
    if (el) el.value = value;
}

// ─────────────────────────────────────────────────────────────────────────────
// Task & milestone management
// ─────────────────────────────────────────────────────────────────────────────

function addTask(phaseNum) {
    addTaskWithValue(phaseNum, '', 'custom', '');
}

function addTaskWithValue(phaseNum, title, type, recurrence) {
    type       = type       || 'custom';
    recurrence = recurrence || '';

    const taskTypes    = ['custom', 'journal', 'meditation', 'session', 'exercise'];
    const recurrences  = { '': 'One-time', 'daily': 'Daily', 'weekly': 'Weekly', 'bi-weekly': 'Bi-weekly' };

    const typeOptions = taskTypes.map(function (t) {
        return '<option value="' + t + '"' + (t === type ? ' selected' : '') + '>' + capitalise(t) + '</option>';
    }).join('');

    const recurrenceOptions = Object.entries(recurrences).map(function ([v, l]) {
        return '<option value="' + v + '"' + (v === recurrence ? ' selected' : '') + '>' + l + '</option>';
    }).join('');

    const container = document.getElementById('phase-' + phaseNum + '-tasks');
    if (!container) return;

    const escaped = title.replace(/"/g, '&quot;');
    container.insertAdjacentHTML('beforeend',
        '<div class="task-item">' +
            '<input type="hidden" name="taskPhase[]" value="' + phaseNum + '" />' +
            '<input type="text" name="taskTitle[]" value="' + escaped + '" placeholder="Task description" />' +
            '<select name="taskType[]">' + typeOptions + '</select>' +
            '<select name="recurrencePattern[]">' + recurrenceOptions + '</select>' +
            '<span class="remove-btn" onclick="this.parentElement.remove()">×</span>' +
        '</div>'
    );
}

function addMilestone(phaseNum) {
    addMilestoneWithValue(phaseNum, '');
}

function addMilestoneWithValue(phaseNum, value) {
    const container = document.getElementById('phase-' + phaseNum + '-tasks');
    if (!container) return;

    const escaped = (value || '').replace(/"/g, '&quot;');
    container.insertAdjacentHTML('beforeend',
        '<div class="milestone-item">' +
            '<span style="color:var(--color-primary);flex-shrink:0;">⭐</span>' +
            '<input type="text" name="phase' + phaseNum + 'Milestone[]" value="' + escaped + '" placeholder="Milestone" />' +
            '<span class="remove-btn" onclick="this.parentElement.remove()">×</span>' +
        '</div>'
    );
}

// ─────────────────────────────────────────────────────────────────────────────
// Client selection (create form only)
// ─────────────────────────────────────────────────────────────────────────────

function showClientDropdown() {
    const select    = document.getElementById('assignedTo');
    const button    = document.querySelector('.add-client-btn');
    const clientBox = document.getElementById('selectedClient');

    if (!select) return;
    select.style.display = 'block';
    if (button) button.style.display = 'none';

    select.addEventListener('change', function () {
        if (!this.value) return;
        const text = this.options[this.selectedIndex].text;
        if (clientBox) {
            clientBox.innerHTML = '<span>' + escapeHtml(text) + '</span>'
                + ' <span class="remove-btn" onclick="removeClient()">×</span>';
            clientBox.style.display = 'flex';
        }
        select.style.display = 'none';
    }, { once: true });
}

function removeClient() {
    const select    = document.getElementById('assignedTo');
    const clientBox = document.getElementById('selectedClient');
    const button    = document.querySelector('.add-client-btn');

    if (select)    { select.value = ''; select.style.display = 'none'; }
    if (clientBox) clientBox.style.display = 'none';
    if (button)    button.style.display = 'inline-flex';
}

// ─────────────────────────────────────────────────────────────────────────────
// Form validation
// ─────────────────────────────────────────────────────────────────────────────

function validateForm() {
    let valid = true;

    const title     = document.getElementById('title');
    const startDate = document.getElementById('startDate');
    const endDate   = document.getElementById('endDate') || document.getElementById('targetCompletionDate');

    if (title && !title.value.trim()) {
        showError(title, 'Plan title is required.');
        valid = false;
    }

    if (startDate && !startDate.value) {
        showError(startDate, 'Start date is required.');
        valid = false;
    }

    if (endDate && !endDate.value) {
        showError(endDate, 'End date is required.');
        valid = false;
    }

    if (startDate && endDate && startDate.value && endDate.value) {
        if (new Date(startDate.value) > new Date(endDate.value)) {
            showError(endDate, 'End date must be after start date.');
            valid = false;
        }
    }

    return valid;
}

function showError(input, message) {
    input.classList.add('error-border');
    if (input.parentNode.querySelector('.rp-field-error')) return;
    const err = document.createElement('small');
    err.className   = 'rp-field-error';
    err.textContent = message;
    err.style.cssText = 'color:#f43a3a;font-size:12px;margin-top:4px;display:block;';
    input.parentNode.appendChild(err);
}

function clearErrors() {
    document.querySelectorAll('.rp-field-error').forEach(function (el) { el.remove(); });
    document.querySelectorAll('.error-border').forEach(function (el) { el.classList.remove('error-border'); });
}

// ─────────────────────────────────────────────────────────────────────────────
// Utilities
// ─────────────────────────────────────────────────────────────────────────────

function initializeDates() {
    const startDate = document.getElementById('startDate');
    const endDate   = document.getElementById('endDate');

    if (startDate && !startDate.value) {
        startDate.value = formatDate(new Date());
    }

    if (endDate && !endDate.value) {
        const d = new Date();
        d.setMonth(d.getMonth() + 3);
        endDate.value = formatDate(d);
    }
}

function formatDate(date) {
    return date.toISOString().split('T')[0];
}

function capitalise(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function showNotification(message, type) {
    if (window.NewPathToast) {
        window.NewPathToast.show(message, type === 'success' ? 'success' : 'error');
    }
}

function exportPDF() {
    alert('PDF export will be implemented with a backend service.');
}

// Inject keyframe for loading icon once
(function () {
    if (document.getElementById('rp-anim-style')) return;
    const s = document.createElement('style');
    s.id = 'rp-anim-style';
    s.textContent = '@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}';
    document.head.appendChild(s);
})();
