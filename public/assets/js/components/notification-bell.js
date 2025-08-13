(function () {
    var wrapper    = document.getElementById('notifBellWrapper');
    if (!wrapper) return; // bell not present on this page

    var btn        = document.getElementById('notifBellBtn');
    var dropdown   = document.getElementById('notifDropdown');
    var badge      = document.getElementById('notifBadge');
    var list       = document.getElementById('notifList');
    var markAllBtn = document.getElementById('notifMarkAllBtn');
    var endpoint   = wrapper.dataset.notifEndpoint || '/notifications';
    var isOpen     = false;
    var loaded     = false;
    var unreadCount = 0;

    // ------------------------------------------------------------------ open/close

    function positionDropdown() {
        var rect = btn.getBoundingClientRect();
        var dropW = 340;
        var vpW   = window.innerWidth;

        // Place above the bell button; if not enough room above, place below
        var spaceAbove = rect.top;
        var spaceBelow = vpW - rect.bottom;

        // Horizontal: align right edge of dropdown to right edge of button,
        // clamped so it doesn't overflow the viewport left edge.
        var left = rect.right - dropW;
        if (left < 8) left = 8;

        dropdown.style.left   = left + 'px';
        dropdown.style.width  = dropW + 'px';

        if (spaceAbove > 200) {
            // Place above
            dropdown.style.top    = 'auto';
            dropdown.style.bottom = (window.innerHeight - rect.top + 6) + 'px';
        } else {
            // Place below
            dropdown.style.top    = (rect.bottom + 6) + 'px';
            dropdown.style.bottom = 'auto';
        }
    }

    function openDropdown() {
        isOpen = true;
        dropdown.style.display = 'block';
        positionDropdown();
        dropdown.offsetHeight; // force reflow for CSS transition
        dropdown.classList.add('show');
        if (!loaded) fetchNotifications();
    }

    function closeDropdown() {
        isOpen = false;
        dropdown.classList.remove('show');
        setTimeout(function () {
            if (!isOpen) dropdown.style.display = 'none';
        }, 220);
    }

    // Reposition if window resizes while open
    window.addEventListener('resize', function () {
        if (isOpen) positionDropdown();
    });

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        isOpen ? closeDropdown() : openDropdown();
    });

    document.addEventListener('click', function (e) {
        if (isOpen && !wrapper.contains(e.target)) closeDropdown();
    });

    // ------------------------------------------------------------------ fetch

    function fetchNotifications() {
        loaded = true;
        fetch(endpoint + '?ajax=list')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) return;
                renderBadge(data.unread);
                renderList(data.notifications);
            })
            .catch(function () {
                list.textContent = 'Could not load notifications.';
            });
    }

    // ------------------------------------------------------------------ render

    function renderBadge(count) {
        unreadCount = Math.max(0, parseInt(count, 10) || 0);

        if (unreadCount > 0) {
            badge.textContent = '';
            badge.style.display = 'inline-flex';
        } else {
            badge.textContent = '';
            badge.style.display = 'none';
        }
    }

    function renderList(notifications) {
        while (list.firstChild) list.removeChild(list.firstChild);

        if (!notifications || notifications.length === 0) {
            var empty = document.createElement('p');
            empty.className = 'notif-empty';
            empty.textContent = "You're all caught up!";
            list.appendChild(empty);
            return;
        }

        notifications.forEach(function (n) {
            list.appendChild(buildItem(n));
        });
    }

    function buildItem(n) {
        var item = document.createElement('div');
        item.className = 'notif-item' + (n.isRead ? '' : ' notif-unread');
        item.dataset.id = n.id;

        var dot = document.createElement('span');
        dot.className = 'notif-dot';

        var body = document.createElement('div');
        body.className = 'notif-body';

        var title = document.createElement('p');
        title.className = 'notif-title';
        title.textContent = n.title;

        var msg = document.createElement('p');
        msg.className = 'notif-msg';
        msg.textContent = n.message;

        var time = document.createElement('span');
        time.className = 'notif-time';
        time.textContent = n.timeAgo;

        body.appendChild(title);
        body.appendChild(msg);
        body.appendChild(time);
        item.appendChild(dot);
        item.appendChild(body);

        item.addEventListener('click', function () {
            if (!n.isRead) {
                markOneRead(n.id, item);
                n.isRead = true;
            }
            if (n.link) {
                window.location.href = n.link;
            }
        });

        return item;
    }

    // ------------------------------------------------------------------ mark read

    function markOneRead(id, el) {
        var fd = new FormData();
        fd.append('notification_id', id);
        fetch(endpoint + '?ajax=mark_one_read', { method: 'POST', body: fd })
            .then(function () {
                el.classList.remove('notif-unread');
                var dot = el.querySelector('.notif-dot');
                if (dot) dot.style.opacity = '0';
                renderBadge(unreadCount - 1);
            })
            .catch(function () {});
    }

    markAllBtn.addEventListener('click', function () {
        fetch(endpoint + '?ajax=mark_read', { method: 'POST' })
            .then(function () {
                renderBadge(0);
                document.querySelectorAll('.notif-unread').forEach(function (el) {
                    el.classList.remove('notif-unread');
                    var dot = el.querySelector('.notif-dot');
                    if (dot) dot.style.opacity = '0';
                });
            })
            .catch(function () {});
    });

    // ------------------------------------------------------------------ polling

    var badgePoller = window.NewPathPolling.createTask({
        interval: 60000,
        request: function () {
            return fetch(endpoint + '?ajax=list')
                .then(function (r) { return r.json(); });
        },
        onSuccess: function (data) {
            if (!data || !data.success) return;
            renderBadge(data.unread);
            if (isOpen) renderList(data.notifications);
        }
    });

    badgePoller.start();
}());
