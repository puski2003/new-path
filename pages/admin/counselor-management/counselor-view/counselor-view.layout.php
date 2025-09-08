<?php
$pageTitle = 'Counselor Details';
require_once __DIR__ . '/../../common/admin.html.head.php';

$formatNullable = static function ($value, string $fallback = '-'): string {
    $stringValue = trim((string) ($value ?? ''));
    return $stringValue !== '' ? $stringValue : $fallback;
};
?>
<main class="admin-main-container">
    <?php require_once __DIR__ . '/../../common/admin.sidebar.php'; ?>

    <section class="admin-main-content">
        <div class="admin-sub-container-1" style="justify-content: space-between; align-items: center;">
            <h1>Counselor Details</h1>
            <a href="/admin/counselor-management" class="admin-button admin-button--secondary">
                <span class="button-text">Back to Counselor Management</span>
            </a>
        </div>

        <div class="admin-sub-container-2">
            <?php if ($error !== ''): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php elseif ($counselor): ?>
                <div class="admin-sub-container-1" style="align-items: center; gap: var(--spacing-md);">
                    <span class="status-badge <?= $counselor['isActive'] ? 'status-badge--active' : 'status-badge--inactive' ?>">
                        <?= $counselor['isActive'] ? 'Active' : 'Inactive' ?>
                    </span>
                    <?php if ($counselor['isVerified']): ?>
                        <span class="status-badge status-badge--verified">
                            Verified
                        </span>
                    <?php endif; ?>
                </div>

                <table class="admin-table">
                    <tbody class="admin-table-body">
                        <tr class="admin-table-row admin-table-row--even">
                            <td class="admin-table-td"><strong>Counselor ID</strong></td>
                            <td class="admin-table-td">#<?= (int) $counselor['counselorId'] ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--odd">
                            <td class="admin-table-td"><strong>Full Name</strong></td>
                            <td class="admin-table-td"><?= htmlspecialchars($formatNullable($counselor['fullName'])) ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--even">
                            <td class="admin-table-td"><strong>Email</strong></td>
                            <td class="admin-table-td"><?= htmlspecialchars($formatNullable($counselor['email'])) ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--odd">
                            <td class="admin-table-td"><strong>Username</strong></td>
                            <td class="admin-table-td"><?= htmlspecialchars($formatNullable($counselor['username'])) ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--even">
                            <td class="admin-table-td"><strong>Phone Number</strong></td>
                            <td class="admin-table-td"><?= htmlspecialchars($formatNullable($counselor['phoneNumber'])) ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--odd">
                            <td class="admin-table-td"><strong>Title</strong></td>
                            <td class="admin-table-td"><?= htmlspecialchars($formatNullable($counselor['title'])) ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--even">
                            <td class="admin-table-td"><strong>Specialty</strong></td>
                            <td class="admin-table-td"><?= htmlspecialchars($formatNullable($counselor['specialty'])) ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--odd">
                            <td class="admin-table-td"><strong>Experience</strong></td>
                            <td class="admin-table-td"><?= (int) $counselor['experienceYears'] ?> years</td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--even">
                            <td class="admin-table-td"><strong>Languages Spoken</strong></td>
                            <td class="admin-table-td"><?= htmlspecialchars($formatNullable($counselor['languagesSpoken'])) ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--odd">
                            <td class="admin-table-td"><strong>Consultation Fee</strong></td>
                            <td class="admin-table-td"><?= $counselor['consultationFee'] !== null ? '$' . number_format($counselor['consultationFee'], 2) : '-' ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--even" style="vertical-align: top;">
                            <td class="admin-table-td"><strong>Bio</strong></td>
                            <td class="admin-table-td"><?= nl2br(htmlspecialchars($formatNullable($counselor['bio']))) ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--odd" style="vertical-align: top;">
                            <td class="admin-table-td"><strong>Education</strong></td>
                            <td class="admin-table-td"><?= nl2br(htmlspecialchars($formatNullable($counselor['education']))) ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--even" style="vertical-align: top;">
                            <td class="admin-table-td"><strong>Certifications</strong></td>
                            <td class="admin-table-td"><?= nl2br(htmlspecialchars($formatNullable($counselor['certifications']))) ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--odd" style="vertical-align: top;">
                            <td class="admin-table-td"><strong>Availability Schedule</strong></td>
                            <td class="admin-table-td">
                                <?php if (!empty($counselor['availabilitySchedule'])): ?>
                                    <pre style="margin: 0; white-space: pre-wrap;"><?= htmlspecialchars($counselor['availabilitySchedule']) ?></pre>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <h3 style="margin-top: var(--spacing-lg);">Statistics</h3>
                <div class="admin-sub-container-1" style="flex-wrap: wrap;">
                    <div class="admin-summary-card" style="flex: 1; min-width: 150px;">
                        <div class="admin-summary-card-content">
                            <p class="admin-summary-card-title">Rating</p>
                            <p class="admin-summary-card-info"><?= $counselor['rating'] > 0 ? number_format($counselor['rating'], 1) : 'N/A' ?>/5</p>
                        </div>
                    </div>
                    <div class="admin-summary-card" style="flex: 1; min-width: 150px;">
                        <div class="admin-summary-card-content">
                            <p class="admin-summary-card-title">Reviews</p>
                            <p class="admin-summary-card-info"><?= (int) $counselor['totalReviews'] ?></p>
                        </div>
                    </div>
                    <div class="admin-summary-card" style="flex: 1; min-width: 150px;">
                        <div class="admin-summary-card-content">
                            <p class="admin-summary-card-title">Clients</p>
                            <p class="admin-summary-card-info"><?= (int) $counselor['totalClients'] ?></p>
                        </div>
                    </div>
                    <div class="admin-summary-card" style="flex: 1; min-width: 150px;">
                        <div class="admin-summary-card-content">
                            <p class="admin-summary-card-title">Sessions</p>
                            <p class="admin-summary-card-info"><?= (int) $counselor['totalSessions'] ?></p>
                        </div>
                    </div>
                </div>

                <h3 style="margin-top: var(--spacing-lg);">Account Information</h3>
                <table class="admin-table">
                    <tbody class="admin-table-body">
                        <tr class="admin-table-row admin-table-row--even">
                            <td class="admin-table-td"><strong>Member Since</strong></td>
                            <td class="admin-table-td"><?= htmlspecialchars($formatNullable(date('F j, Y', strtotime($counselor['counselorCreatedAt'])))) ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--odd">
                            <td class="admin-table-td"><strong>Last Login</strong></td>
                            <td class="admin-table-td"><?= !empty($counselor['lastLogin']) ? htmlspecialchars(date('F j, Y g:i A', strtotime($counselor['lastLogin']))) : 'Never' ?></td>
                        </tr>
                    </tbody>
                </table>

                <?php if (!empty($recentSessions)): ?>
                <h3 style="margin-top: var(--spacing-lg);">Recent Sessions</h3>
                <table class="admin-table">
                    <thead class="admin-table-header">
                        <tr class="admin-table-row">
                            <th class="admin-table-th">Client</th>
                            <th class="admin-table-th">Date</th>
                            <th class="admin-table-th">Duration</th>
                            <th class="admin-table-th">Status</th>
                        </tr>
                    </thead>
                    <tbody class="admin-table-body">
                        <?php foreach ($recentSessions as $session): ?>
                            <tr class="admin-table-row">
                                <td class="admin-table-td"><?= htmlspecialchars($session['clientName']) ?></td>
                                <td class="admin-table-td"><?= htmlspecialchars(date('M j, Y g:i A', strtotime($session['scheduledAt']))) ?></td>
                                <td class="admin-table-td"><?= (int) $session['duration'] ?> min</td>
                                <td class="admin-table-td"><?= htmlspecialchars(ucfirst($session['status'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <?php if (!empty($recentReviews)): ?>
                <h3 style="margin-top: var(--spacing-lg);">Recent Reviews</h3>
                <div class="reviews-list">
                    <?php foreach ($recentReviews as $review): ?>
                        <div style="background: #f9f9f9; border-radius: 8px; padding: 16px; margin-bottom: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <strong><?= htmlspecialchars($review['clientName']) ?></strong>
                                <span style="color: #f39c12;">
                                    <?= str_repeat('★', (int) $review['rating']) ?><?= str_repeat('☆', 5 - (int) $review['rating']) ?>
                                </span>
                            </div>
                            <p style="margin: 0; color: #555;"><?= nl2br(htmlspecialchars($review['review'])) ?></p>
                            <small style="color: #999;"><?= htmlspecialchars(date('M j, Y', strtotime($review['createdAt']))) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<style>
.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: 600;
}
.status-badge--active {
    background: #d4edda;
    color: #155724;
}
.status-badge--inactive {
    background: #f8d7da;
    color: #721c24;
}
.status-badge--verified {
    background: #cce5ff;
    color: #004085;
}
</style>

<?php require_once __DIR__ . '/../../common/admin.footer.php'; ?>
