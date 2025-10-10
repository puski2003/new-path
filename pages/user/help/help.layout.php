<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/../common/user.html.head.php'; ?>

<body>
    <main class="main-container">
        <?php $activePage = 'help-center';
        require_once __DIR__ . '/../common/user.sidebar.php'; ?>

        <section class="main-content">
            <img src="/assets/img/main-content-head.svg"
                alt="Main Content Head background"
                class="main-header-bg-image" />

            <div class="main-content-header">
                <div class="main-content-header-text">
                    <h2>Help Center</h2>
                    <p>Find support and get answers to your questions.</p>
                </div>

                <div style="width: 25%"></div>
                <img src="/assets/img/recovery-head.svg"
                    alt="Help Center"
                    class="help-image" />
            </div>

            <div class="main-content-body">
                <div class="help-container">
                    <div class="help-search-section">
                        <div class="search-container">
                            <div class="search-bar">
                                <i data-lucide="search" class="search-icon" stroke-width="1"></i>
                                <input type="text" class="search-input" placeholder="Search for topics or services..." id="helpSearch">
                            </div>
                            <div class="filter-controls">
                                <select class="filter-dropdown" id="categoryFilter">
                                    <option value="all">All Categories</option>
                                    <?php
                                    $allServices = array_merge($helpServices, $emergencyServices);
                                    $categories = array_unique(array_column($allServices, 'category'));
                                    sort($categories);
                                    foreach ($categories as $cat):
                                        $label = ucwords(str_replace('-', ' ', $cat));
                                    ?>
                                    <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select class="filter-dropdown" id="typeFilter">
                                    <option value="all">All Types</option>
                                    <?php
                                    $typeLabels = ['hotline' => 'Hotlines', 'chat' => 'Live Chat', 'appointment' => 'Appointments', 'resources' => 'Resources'];
                                    $types = array_unique(array_column($allServices, 'type'));
                                    sort($types);
                                    foreach ($types as $t):
                                        $tLabel = $typeLabels[$t] ?? ucwords(str_replace('-', ' ', $t));
                                    ?>
                                    <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($tLabel) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($emergencyServices)): ?>
                    <div class="help-section emergency-section">
                        <div class="section-header">
                            <i data-lucide="alert-triangle" class="section-icon emergency-icon" stroke-width="1"></i>
                            <h3 class="section-title">Emergency Services</h3>
                        </div>
                        <div class="emergency-grid">
                            <?php foreach ($emergencyServices as $emergency): ?>
                            <div class="emergency-card">
                                <div class="emergency-info">
                                    <h4><?= htmlspecialchars($emergency['title']) ?></h4>
                                    <p><?= htmlspecialchars($emergency['description']) ?></p>
                                    <?php if (!empty($emergency['phoneNumber'])): ?>
                                        <span class="emergency-number"><?= htmlspecialchars($emergency['phoneNumber']) ?></span>
                                    <?php else: ?>
                                        <span class="emergency-status"><?= htmlspecialchars($emergency['availability']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <button
                                    class="btn btn-primary emergency-btn service-contact-btn"
                                    type="button"
                                    data-phone="<?= htmlspecialchars($emergency['phoneNumber'], ENT_QUOTES) ?>"
                                    data-email="<?= htmlspecialchars($emergency['email'], ENT_QUOTES) ?>"
                                    data-website="<?= htmlspecialchars($emergency['website'], ENT_QUOTES) ?>"
                                    data-title="<?= htmlspecialchars($emergency['title'], ENT_QUOTES) ?>"
                                    data-service-id="<?= $emergency['id'] ?>"
                                ><?= htmlspecialchars($emergency['contactLabel']) ?></button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="help-section support-services">
                        <h3 class="section-title">Support Services</h3>
                        <div class="services-grid" id="servicesGrid">
                            <?php foreach ($helpServices as $service): ?>
                                <?php require __DIR__ . '/../common/user.help-service-card.php'; ?>
                            <?php endforeach; ?>
                            <?php require __DIR__ . '/../common/user.help-service-empty-state.php'; ?>
                        </div>
                    </div>

                    <div class="help-section quick-actions">
                        <h3 class="section-title">Quick Actions</h3>
                        <div class="actions-grid">
                            <div class="action-card">
                                <i data-lucide="calendar" class="action-icon" stroke-width="1"></i>
                                <h4>Schedule Session</h4>
                                <p>Book a counseling session</p>
                                <button class="btn btn-secondary" type="button">Schedule</button>
                            </div>
                            <div class="action-card">
                                <i data-lucide="users" class="action-icon" stroke-width="1"></i>
                                <h4>Join Support Group</h4>
                                <p>Connect with peer groups</p>
                                <button class="btn btn-secondary" type="button">Join Group</button>
                            </div>
                            <div class="action-card">
                                <i data-lucide="book-open" class="action-icon" stroke-width="1"></i>
                                <h4>Recovery Resources</h4>
                                <p>Access helpful materials</p>
                                <button class="btn btn-secondary" type="button">Browse</button>
                            </div>
                            <div class="action-card">
                                <i data-lucide="headphones" class="action-icon" stroke-width="1"></i>
                                <h4>Technical Support</h4>
                                <p>Get help with the platform</p>
                                <button class="btn btn-secondary" type="button">Get Help</button>
                            </div>
                        </div>
                    </div>

                    <div class="pagination" id="helpPagination" style="display: none;">
                        <button class="pagination-btn pagination-prev" id="prevBtn" disabled>
                            <i data-lucide="chevron-left" stroke-width="1.8"></i>
                        </button>
                        <div class="pagination-numbers" id="paginationNumbers"></div>
                        <button class="pagination-btn pagination-next" id="nextBtn">
                            <i data-lucide="chevron-right" stroke-width="1.8"></i>
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div id="centerDetailsModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalCenterName">Center Details</h3>
                <span class="close" onclick="window.closeCenterModal()">&times;</span>
            </div>
            <div class="modal-body" id="modalCenterContent"></div>
        </div>
    </div>

    <div hidden>
        <?php foreach ($helpServices as $service): ?>
            <?php require __DIR__ . '/../common/user.help-service-detail.php'; ?>
        <?php endforeach; ?>
    </div>
    <script src="/assets/js/user/help/helpCenter.js"></script>
    <script src="/assets/js/user/common/log-urge-popup.js"></script>
    <script>
        if (typeof lucide !== 'undefined') lucide.createIcons();
    </script>
</body>

</html>
