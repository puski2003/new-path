<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = "Counselors";
$pageStyle = ["user/dashboard", "user/counselors", "user/sessions"];
require_once __DIR__ . '/../common/user.html.head.php';
?>

<body>
    <main class="main-container">
        <?php
        $activePage = 'counselors';
        require_once __DIR__ . '/../common/user.sidebar.php';
        ?>

        <section class="main-content">
            <img src="/assets/img/main-content-head.svg"
                alt="Main Content Head background"
                class="main-header-bg-image" />

            <div class="main-content-header">
                <div class="main-content-header-text">
                    <h2>Counselors</h2>
                    <p>Your path to guidance starts here.</p>
                </div>

                <?php if ($activeTab === 'find'):
                    $placeholder = "Search counselors...";
                    require __DIR__ . '/../common/user.searchbar.php';
                endif; ?>

                <div style="width: 25%"></div>
                <img src="/assets/img/counselor-header.svg"
                    alt="Counselors"
                    class="counselors-image" />
            </div>

            <div class="main-content-body">

                <!-- Tabs -->
                <div class="sessions-tabs" style="border-bottom: 1px solid var(--color-border-primary);">
                    <a href="/user/counselors?tab=my"
                       class="tab-btn <?= $activeTab === 'my' ? 'active' : '' ?>">
                        My Counselors
                    </a>
                    <a href="/user/counselors?tab=find"
                       class="tab-btn <?= $activeTab === 'find' ? 'active' : '' ?>">
                        Find a Counselor
                    </a>
                </div>

                <?php if ($activeTab === 'my'): ?>
                <!-- ── MY COUNSELORS TAB ──────────────────────────────────── -->
                <div class="counselor-cards-container">
                    <?php if (empty($myCounselors)): ?>
                        <div class="no-results-message" style="margin-top: var(--spacing-2xl);">
                            <i data-lucide="user-x" style="width:48px;height:48px;color:var(--color-text-muted);display:block;margin:0 auto var(--spacing-md);"></i>
                            <p>You haven't completed a session with any counselor yet.</p>
                            <div style="display: flex;justify-content: center;"><a href="/user/counselors?tab=find" class="btn btn-primary">Find a Counselor</a></div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($myCounselors as $mc):
                            $lastDate = $mc['last_session_at'] ? date('M j, Y', strtotime($mc['last_session_at'])) : '—';
                            $threadOpen = $mc['open_session_id'] !== null;
                        ?>
                        <div class="my-counselor-card">
                            <div class="counselor-avatar">
                                <img src="<?= htmlspecialchars($mc['profile_picture']) ?>"
                                     alt="<?= htmlspecialchars($mc['name']) ?>" />
                            </div>
                            <div class="counselor-info">
                                <span class="counselor-specialty"><?= htmlspecialchars($mc['specialty']) ?></span>
                                <h3 class="counselor-name"><?= htmlspecialchars($mc['name']) ?></h3>
                                <?php if ($mc['title']): ?>
                                    <p class="counselor-schedule"><?= htmlspecialchars($mc['title']) ?></p>
                                <?php endif; ?>
                                <div class="my-counselor-meta">
                                    <span class="meta-pill">
                                        <i data-lucide="calendar" style="width:12px;height:12px;"></i>
                                        Last: <?= htmlspecialchars($lastDate) ?>
                                    </span>
                                    <span class="meta-pill">
                                        <i data-lucide="check-circle" style="width:12px;height:12px;"></i>
                                        <?= $mc['sessions_count'] ?> session<?= $mc['sessions_count'] !== 1 ? 's' : '' ?>
                                    </span>
                                    <?php if (!empty($mc['hasFreeCredit'])): ?>
                                    <span class="meta-pill meta-pill--free">
                                        <i data-lucide="gift" style="width:12px;height:12px;"></i>
                                        Free Session
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="counselor-actions">
                                <?php if ($threadOpen): ?>
                                    <a href="/user/sessions/follow-up?session_id=<?= $mc['open_session_id'] ?>"
                                       class="btn btn-primary" style="font-size:var(--font-size-xs);">
                                        <i data-lucide="message-square" style="width:14px;height:14px;margin-right:4px;"></i>
                                        Send Follow-up
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled
                                            style="font-size:var(--font-size-xs);opacity:0.5;cursor:not-allowed;"
                                            title="Follow-up window is closed (7 days after session)">
                                        <i data-lucide="message-square" style="width:14px;height:14px;margin-right:4px;"></i>
                                        Follow-up Closed
                                    </button>
                                <?php endif; ?>
                                <a href="/user/counselors?id=<?= $mc['counselor_id'] ?>"
                                   class="btn <?= !empty($mc['hasFreeCredit']) ? 'btn-primary' : 'btn-secondary' ?>"
                                   style="font-size:var(--font-size-xs);">
                                    <i data-lucide="<?= !empty($mc['hasFreeCredit']) ? 'gift' : 'calendar-plus' ?>" style="width:14px;height:14px;margin-right:4px;"></i>
                                    <?= !empty($mc['hasFreeCredit']) ? 'Book Free' : 'Book Again' ?>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php else: ?>
                <!-- ── FIND A COUNSELOR TAB ──────────────────────────────── -->
                <div class="counselor-cards-container">
                    <?php if (Request::get('cancelled') === '1'): ?>
                    <div class="error-message">
                        Payment cancelled. No charge was made — please select a time slot to try again.
                    </div>
                    <?php endif; ?>

                    <div class="">
                        <button class="btn btn-bg-light-green filter-button" onclick="toggleFilters()">
                            <i data-lucide="filter"  width="16" height="16" stroke-width="1" class="filter-icon"></i>
                            <span>Filter</span>
                        </button>
                    </div>

                    <!-- Filter Panel -->
                    <div id="filterPanel" class="filter-panel" style="display: none;">
                        <form action="/user/counselors" method="GET" class="filter-form-inline">
                            <input type="hidden" name="tab" value="find" />
                            <input type="hidden" name="q" id="searchQueryHidden" value="<?= htmlspecialchars($searchQuery) ?>" />

                            <div class="filter-group">
                                <label for="specialty">Specialty</label>
                                <select name="specialty" id="specialty" class="filter-select">
                                    <option value="">All Specialties</option>
                                    <?php foreach ($specialties as $spec): ?>
                                        <option value="<?= htmlspecialchars($spec) ?>" <?= ($selectedSpecialty === $spec) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($spec) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="minExperience">Min Experience</label>
                                <input type="number" name="minExperience" id="minExperience"
                                    value="<?= htmlspecialchars($selectedMinExperience) ?>" placeholder="Years" min="0" max="50" class="filter-input" />
                            </div>

                            <div class="filter-group">
                                <label for="maxPrice">Max Price (Rs)</label>
                                <input type="number" name="maxPrice" id="maxPrice"
                                    value="<?= htmlspecialchars($selectedMaxPrice) ?>" placeholder="Any" min="0" step="500" class="filter-input" />
                            </div>

                            <div class="filter-group">
                                <label for="minRating">Min Rating</label>
                                <select name="minRating" id="minRating" class="filter-select">
                                    <option value="">Any</option>
                                    <option value="4" <?= ($selectedMinRating == '4') ? 'selected' : '' ?>>4+ Stars</option>
                                    <option value="3" <?= ($selectedMinRating == '3') ? 'selected' : '' ?>>3+ Stars</option>
                                </select>
                            </div>

                            <div class="filter-actions">
                                <button type="submit" class="btn btn-primary">Apply</button>
                                <a href="/user/counselors?tab=find" class="btn btn-secondary">Clear</a>
                            </div>
                        </form>
                    </div>

                    <!-- Counselor Cards -->
                    <?php if (empty($counselors)): ?>
                        <div class="no-results-message">
                            <p>No counselors found. Try adjusting your search or filters.</p>
                            <a href="/user/counselors?tab=find" class="btn btn-primary">View All Counselors</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($counselors as $counselor): ?>
                            <?php require __DIR__ . '/../common/user.counselor-card.php'; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php
                            $queryParams = http_build_query([
                                'tab' => 'find',
                                'q' => $searchQuery,
                                'specialty' => $selectedSpecialty,
                                'minExperience' => $selectedMinExperience,
                                'maxPrice' => $selectedMaxPrice,
                                'minRating' => $selectedMinRating,
                            ]);
                            ?>
                            <?php if ($currentPage > 1): ?>
                                <a href="/user/counselors?page=<?= $currentPage - 1 ?>&<?= $queryParams ?>"
                                    class="pagination-btn pagination-prev">
                                    <i data-lucide="arrow-left" stroke-width="1"></i>
                                </a>
                            <?php else: ?>
                                <button class="pagination-btn pagination-prev" disabled>
                                    <i data-lucide="arrow-left" stroke-width="1"></i>
                                </button>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php if ($i == $currentPage): ?>
                                    <button class="pagination-btn pagination-number active"><?= $i ?></button>
                                <?php else: ?>
                                    <a href="/user/counselors?page=<?= $i ?>&<?= $queryParams ?>"
                                        class="pagination-btn pagination-number"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($currentPage < $totalPages): ?>
                                <a href="/user/counselors?page=<?= $currentPage + 1 ?>&<?= $queryParams ?>"
                                    class="pagination-btn pagination-next">
                                    <i data-lucide="arrow-right" stroke-width="1"></i>
                                </a>
                            <?php else: ?>
                                <button class="pagination-btn pagination-next" disabled>
                                    <i data-lucide="arrow-right" stroke-width="1"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </div>
        </section>
    </main>

    <script>
        function toggleFilters() {
            var fp = document.getElementById('filterPanel');
            if (fp) fp.style.display = fp.style.display === 'none' ? 'flex' : 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
            var searchInput = document.querySelector('.search-input');
            var searchButton = document.querySelector('.search-button');

            if (searchInput && searchButton) {
                searchInput.value = <?= json_encode($searchQuery) ?>;

                searchButton.addEventListener('click', function() { performSearch(); });
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') performSearch();
                });
            }

            function performSearch() {
                var query = searchInput.value;
                var url = '/user/counselors?tab=find&q=' + encodeURIComponent(query);
                var specialty = document.getElementById('specialty');
                if (specialty && specialty.value) url += '&specialty=' + encodeURIComponent(specialty.value);
                var minExp = document.getElementById('minExperience');
                if (minExp && minExp.value) url += '&minExperience=' + minExp.value;
                var maxPrice = document.getElementById('maxPrice');
                if (maxPrice && maxPrice.value) url += '&maxPrice=' + maxPrice.value;
                var minRating = document.getElementById('minRating');
                if (minRating && minRating.value) url += '&minRating=' + minRating.value;
                window.location.href = url;
            }

            <?php if (!empty($selectedSpecialty) || !empty($selectedMinExperience) || !empty($selectedMaxPrice) || !empty($selectedMinRating)): ?>
                var fp = document.getElementById('filterPanel');
                if (fp) fp.style.display = 'flex';
            <?php endif; ?>
        });
    </script>
    <?php
    $pageScripts = [
        '/assets/js/user/counselors/counselors.js',
    ];
    require_once __DIR__ . '/../common/user.footer.php';
    ?>
</body>
</html>
