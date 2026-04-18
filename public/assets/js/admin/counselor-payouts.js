var _pendingCounselorId = null;

function markPayoutPaid(counselorId) {
  _pendingCounselorId = counselorId;
  var overlay = document.getElementById('payoutConfirmOverlay');
  var text    = document.getElementById('payoutConfirmText');
  var err     = document.getElementById('payoutConfirmError');
  if (text) text.textContent = 'Mark this counselor\'s pending balance as paid? This action cannot be undone.';
  if (err)  { err.style.display = 'none'; err.textContent = ''; }
  if (overlay) overlay.style.display = 'flex';
}

function closePayoutConfirm() {
  _pendingCounselorId = null;
  var overlay = document.getElementById('payoutConfirmOverlay');
  if (overlay) overlay.style.display = 'none';
}

function confirmMarkPaid() {
  if (!_pendingCounselorId) return;

  var btn = document.getElementById('payoutConfirmBtn');
  var err = document.getElementById('payoutConfirmError');
  if (btn) btn.disabled = true;
  if (err) { err.style.display = 'none'; err.textContent = ''; }

  var formData = new FormData();
  formData.append('counselor_id', _pendingCounselorId);

  fetch('?ajax=mark_paid', {
    method: 'POST',
    body:   formData,
  })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (data.success) {
        var id = _pendingCounselorId;
        // Update status cell
        var statusCell = document.getElementById('payout-status-' + id);
        if (statusCell) {
          statusCell.innerHTML = '<span class="admin-table-badge admin-table-badge--success">Paid</span>';
        }
        // Update paid-at cell
        var paidAtCell = document.getElementById('payout-paid-at-' + id);
        if (paidAtCell) {
          var now = new Date();
          paidAtCell.textContent = now.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        }
        // Replace button
        var actionBtn = document.getElementById('payout-btn-' + id);
        if (actionBtn) {
          actionBtn.outerHTML = '<span style="color:var(--color-text-secondary);font-size:0.78rem;">Paid</span>';
        }
        closePayoutConfirm();
      } else {
        if (err) { err.textContent = data.error || 'Something went wrong.'; err.style.display = 'block'; }
        if (btn) btn.disabled = false;
      }
    })
    .catch(function () {
      if (err) { err.textContent = 'Network error. Please try again.'; err.style.display = 'block'; }
      if (btn) btn.disabled = false;
    });
}

// Close overlay on backdrop click
document.addEventListener('DOMContentLoaded', function () {
  var overlay = document.getElementById('payoutConfirmOverlay');
  if (overlay) {
    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) closePayoutConfirm();
    });
  }
});
