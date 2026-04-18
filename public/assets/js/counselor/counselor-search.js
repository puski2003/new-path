(function () {
    document.addEventListener('DOMContentLoaded', function () {

        // ── Clients page ─────────────────────────────────────────────
        var clientsInput = document.querySelector('.search-input[data-filter="clients"]');
        if (clientsInput) {
            clientsInput.addEventListener('input', function () {
                filterClients(this.value);
            });
        }

        // ── Sessions page ─────────────────────────────────────────────
        var sessionsInput = document.querySelector('.search-input[data-filter="sessions"]');
        if (sessionsInput) {
            sessionsInput.addEventListener('input', function () {
                filterSessions(this.value);
            });
        }
    });

    // ── Clients: filter .cc-client-row by .cc-client-name ────────────
    function filterClients(q) {
        var query = q.trim().toLowerCase();
        var rows = document.querySelectorAll('.cc-client-row');
        var visible = 0;

        rows.forEach(function (row) {
            var nameEl = row.querySelector('.cc-client-name');
            var text = nameEl ? nameEl.textContent.toLowerCase() : '';
            var match = !query || text.includes(query);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        // original empty state (no clients at all)
        var staticEmpty = document.querySelector('.cc-empty');
        // dynamic "no results" message
        var noResults = document.getElementById('clientsNoResults');

        if (!query) {
            if (staticEmpty) staticEmpty.style.display = '';
            if (noResults) noResults.style.display = 'none';
        } else if (rows.length > 0 && visible === 0) {
            if (staticEmpty) staticEmpty.style.display = 'none';
            if (!noResults) {
                noResults = document.createElement('div');
                noResults.id = 'clientsNoResults';
                noResults.className = 'cc-empty';
                noResults.innerHTML = '<i data-lucide="search-x" stroke-width="1"></i>'
                    + '<p>No clients match "<strong></strong>"</p>';
                var container = document.querySelector('.cc-clients-container');
                if (container) container.after(noResults);
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
            noResults.querySelector('strong').textContent = q.trim();
            noResults.style.display = '';
        } else {
            if (staticEmpty) staticEmpty.style.display = 'none';
            if (noResults) noResults.style.display = 'none';
        }
    }

    // ── Sessions: filter cards per-tab, show per-tab "no results" ────
    function filterSessions(q) {
        var query = q.trim().toLowerCase();
        var tabs = document.querySelectorAll('.toggle-section');

        tabs.forEach(function (tab) {
            var cards = tab.querySelectorAll('.counselor-session-card');
            var originalEmpty = tab.querySelector('.counselor-empty-state:not(.session-no-results)');
            var visible = 0;

            cards.forEach(function (card) {
                var nameEl = card.querySelector('h4');
                var text = nameEl ? nameEl.textContent.toLowerCase() : '';
                var match = !query || text.includes(query);
                card.style.display = match ? '' : 'none';
                if (match) visible++;
            });

            var noResults = tab.querySelector('.session-no-results');

            if (!query) {
                if (originalEmpty) originalEmpty.style.display = '';
                if (noResults) noResults.style.display = 'none';
            } else if (cards.length > 0 && visible === 0) {
                if (originalEmpty) originalEmpty.style.display = 'none';
                if (!noResults) {
                    noResults = document.createElement('div');
                    noResults.className = 'counselor-empty-state session-no-results';
                    noResults.innerHTML = '<p>No sessions match "<strong></strong>"</p>';
                    tab.appendChild(noResults);
                }
                noResults.querySelector('strong').textContent = q.trim();
                noResults.style.display = '';
            } else {
                if (originalEmpty) originalEmpty.style.display = '';
                if (noResults) noResults.style.display = 'none';
            }
        });
    }
}());
