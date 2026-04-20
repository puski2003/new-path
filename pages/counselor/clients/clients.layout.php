<?php
$activePage         = 'clients';
$pageHeaderTitle    = 'Clients';
$pageHeaderSubtitle = 'Your client directory';
?>
<!DOCTYPE html>
<html lang="en"> 
<?php $pageTitle = 'Clients'; $pageStyle = ['counselor/clients']; require __DIR__ . '/../common/counselor.html.head.php'; ?>
<body>
<main class="main-container theme-counselor">
    <?php require __DIR__ . '/../common/counselor.sidebar.php'; ?>
    <section class="main-content">
        <?php require __DIR__ . '/../common/counselor.page-header.php'; ?>

        <div class="main-content-body">

            <div class="cc-toolbar">
                <?php require __DIR__ . '/../common/counselor.searchbar.php'; ?>
                <select id="clientFilter" style="height:40px;">
                    <option value="">All Sessions</option>
                    <option value="0">0 sessions</option>
                    <option value="1-5">1 - 5 sessions</option>
                    <option value="6-10">6 - 10 sessions</option>
                    <option value="11-20">11 -20sessions</option>
                    <option value="20+">20+ sessions</option>
                </select>
            </div>

            <?php if (!empty($clients)): ?>
                <div class="cc-clients-container">
                    <?php foreach ($clients as $client): ?>
                        <?php require __DIR__ . '/../common/counselor.client-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="cc-empty">
                    <i data-lucide="users" stroke-width="1"></i>
                    <p>No clients yet.</p>
                    <p>Clients will appear here once they book a session with you.</p>
                </div>
            <?php endif; ?>

        </div>
    </section>
</main>
<script>
    
    lucide.createIcons();

    const searchInput  = document.getElementById('clientSearch');
    const clientFilter = document.getElementById('clientFilter');

    function applyFilters() {
        const query        = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const sessionRange = clientFilter.value;
        const rows         = document.querySelectorAll('.cc-client-row');

        rows.forEach(row => {
            const name     = (row.dataset.name ?? '').toLowerCase();
            const sessions = parseInt(row.dataset.sessions ?? '0');

            const nameMatch = name.includes(query);

            let sessionMatch = true;
            if (sessionRange === '0')     sessionMatch = sessions === 0;
            if (sessionRange === '1-5')   sessionMatch = sessions >= 1  && sessions <= 5;
            if (sessionRange === '6-10')  sessionMatch = sessions >= 6  && sessions <= 10;
            if (sessionRange === '11-20') sessionMatch = sessions >= 11 && sessions <= 20;
            if (sessionRange === '20+')   sessionMatch = sessions > 20;

            row.style.display = (nameMatch && sessionMatch) ? '' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('input', applyFilters);
    clientFilter.addEventListener('change', applyFilters);
</script>
</body>
</html>
