<div class="notif-bell-wrapper" id="notifBellWrapper" data-notif-endpoint="/common/notifications">
    <button class="notif-bell-btn" id="notifBellBtn" type="button" aria-label="Notifications">
        <i data-lucide="bell" width="15" height="15" stroke-width="1.5"></i>
        <span class="notif-badge" id="notifBadge" style="display:none;"></span>
    </button>

    <div class="notif-dropdown" id="notifDropdown" style="display:none;">
        <div class="notif-dropdown-header">
            <span class="notif-dropdown-title">Notifications</span>
            <button type="button" class="notif-mark-all-btn" id="notifMarkAllBtn">Mark all read</button>
        </div>
        <div class="notif-list" id="notifList">
            <p class="notif-empty">Loading…</p>
        </div>
    </div>
</div>
