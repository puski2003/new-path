<!DOCTYPE html>
<html lang="en">
<?php
$pageScripts = [
    '/assets/js/user/community/community.js',
    '/assets/js/user/community/chat.js',
];
if ($tab === 'people') {
    $pageScripts[] = '/assets/js/user/community/find-people.js';
}
?>
<?php require_once __DIR__ . '/../common/user.html.head.php'; ?>
<body>
    <main class="main-container">
        <?php $activePage = 'community'; require_once __DIR__ . '/../common/user.sidebar.php'; ?>

        <section class="main-content">
            <img src="/assets/img/main-content-head.svg" alt="Main Content Head background" class="main-header-bg-image" />

            <div class="main-content-header">
                <div class="main-content-header-text">
                    <h2>Community</h2>
                    <p>Connect with others on the same journey.</p>
                </div>

                <?php $placeholder = $tab === 'people' ? 'Search people...' : 'Search posts...'; require __DIR__ . '/../common/user.searchbar.php'; ?>

                <div style="width: 25%;"></div>
                <img src="/assets/img/community.svg" alt="Community" class="community-image" />
            </div>

            <div class="main-content-body">
               
                <?php if ($tab === 'posts'): ?>
                <!-- Posts Tab -->
                <div class="community-content-header">
                     <a href="/user/community/find-people" class="btn btn-secondary" type="button" id="openPostModalBtn">
                        <i data-lucide="user-plus" width="15" height="15" stroke-width="1.8"></i>
                        Find People</a>
                    <button class="btn btn-primary" type="button" id="openPostModalBtn">
                       Create Post</button>
                </div>

                <div class="community-nav-sections">
                    <div class="filter-options">
                        <div class="filter-dropdown">
                            <a href="/user/community?scope=all<?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" class="filter-btn <?= $scope === 'all' ? 'active' : '' ?>">
                                <span>All Posts</span>
                                <i data-lucide="chevron-down" class="filter-icon" stroke-width="2"></i>
                            </a>
                        </div>
                        <div class="filter-dropdown">
                            <a href="/user/community?scope=mine<?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" class="filter-btn <?= $scope === 'mine' ? 'active' : '' ?>">
                                <span>My Posts</span>
                                <i data-lucide="chevron-down" class="filter-icon" stroke-width="2"></i>
                            </a>
                        </div>
                        <div class="filter-dropdown">
                            <a href="/user/community?scope=trending<?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" class="filter-btn <?= $scope === 'trending' ? 'active' : '' ?>">
                                <span>Trending</span>
                                <i data-lucide="chevron-down" class="filter-icon" stroke-width="2"></i>
                            </a>
                        </div>
                        <div class="filter-dropdown">
                            <a href="/user/community?scope=saved<?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" class="filter-btn <?= $scope === 'saved' ? 'active' : '' ?>">
                                <span>Saved</span>
                                <i data-lucide="bookmark" class="filter-icon" stroke-width="2"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="community-posts-container" id="postsContainer">
                    <?php if (empty($posts)): ?>
                        <div class="community-post"><p class="post-text">No posts found.</p></div>
                    <?php else: ?>
                        <?php foreach ($posts as $currentPost): ?>
                            <?php require __DIR__ . '/../common/user.community-post-item.php'; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div> 

                <?php else: ?>
                <!-- Find People Tab -->
                <div class="find-people-container">
                    <div class="users-grid" id="usersGrid">
                        <?php if (empty($users)): ?>
                            <div class="empty-state">
                                <i data-lucide="users" class="empty-state-icon" stroke-width="1.5"></i>
                                <h4 class="empty-state-title">No users found</h4>
                                <p class="empty-state-text">Try adjusting your search or check back later</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                                <div class="user-card" data-user-id="<?= $u['userId'] ?>">
                                    <div class="user-card-avatar">
                                        <?php if (!empty($u['profilePicture'])): ?>
                                            <img src="<?= htmlspecialchars($u['profilePicture']) ?>" alt="<?= htmlspecialchars($u['displayName']) ?>" />
                                        <?php else: ?>
                                            <img src="/assets/img/avatar.png" alt="Avatar" />
                                        <?php endif; ?>
                                    </div>
                                    <div class="user-card-info">
                                        <h4 class="user-card-name">
                                            <a href="/user/profile?id=<?= $u['userId'] ?>"><?= htmlspecialchars($u['displayName']) ?></a>
                                        </h4>
                                        <?php if (!empty($u['username'])): ?>
                                            <span class="user-card-username">@<?= htmlspecialchars($u['username']) ?></span>
                                        <?php endif; ?>
                                        <span class="user-card-followers"><?= $u['followersCount'] ?> followers</span>
                                    </div>
                                    <div class="user-card-actions">
                                        <?php if ($u['isFollowing']): ?>
                                            <button class="btn-follow following" data-user-id="<?= $u['userId'] ?>">
                                                <i data-lucide="user-check" stroke-width="2"></i>
                                                Following
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-follow" data-user-id="<?= $u['userId'] ?>">
                                                <i data-lucide="user-plus" stroke-width="2"></i>
                                                Follow
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn-more" data-user-id="<?= $u['userId'] ?>">
                                            <i data-lucide="more-horizontal" stroke-width="2"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../common/user.community-post-modal.php'; ?>
    <?php require __DIR__ . '/../common/user.community-delete-confirmation-modal.php'; ?>
    <?php require __DIR__ . '/../common/user.community-report-modal.php'; ?>
    <?php require __DIR__ . '/../common/user.community-chat.php'; ?>

    <?php require_once __DIR__ . '/../common/user.footer.php'; ?>
</body>
</html>
