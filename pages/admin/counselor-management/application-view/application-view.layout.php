<?php
$pageTitle = 'Application Details';
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
            <h1>Application Details</h1>
            <a href="/admin/counselor-management" class="admin-button admin-button--secondary">
                <span class="button-text">Back to Counselor Management</span>
            </a>
        </div>

        <div class="admin-sub-container-2">
            <?php if ($error !== ''): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php elseif ($application): ?>
                <div class="admin-sub-container-1" style="align-items: center; gap: var(--spacing-sm);">
                    <span class="status-badge status-badge--<?= htmlspecialchars($application['status']) ?>">
                        <?= ucfirst(htmlspecialchars($application['status'])) ?>
                    </span>
                    <span style="color: var(--color-text-light);">
                        Submitted on <?= htmlspecialchars(date('F j, Y', strtotime($application['applicationDate']))) ?>
                    </span>
                </div>

                <table class="admin-table">
                    <tbody class="admin-table-body">
                        <tr class="admin-table-row admin-table-row--even">
                            <td class="admin-table-td"><strong>Application ID</strong></td>
                            <td class="admin-table-td">#<?= (int) $application['applicationId'] ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--odd">
                            <td class="admin-table-td"><strong>Full Name</strong></td>
                            <td class="admin-table-td"><?= htmlspecialchars($formatNullable($application['fullName'])) ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--even">
                            <td class="admin-table-td"><strong>Email</strong></td>
                            <td class="admin-table-td"><?= htmlspecialchars($formatNullable($application['email'])) ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--odd">
                            <td class="admin-table-td"><strong>Phone Number</strong></td>
                            <td class="admin-table-td"><?= htmlspecialchars($formatNullable($application['phoneNumber'])) ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--even">
                            <td class="admin-table-td"><strong>Title</strong></td>
                            <td class="admin-table-td"><?= htmlspecialchars($formatNullable($application['title'])) ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--odd">
                            <td class="admin-table-td"><strong>Specialty</strong></td>
                            <td class="admin-table-td"><?= htmlspecialchars($formatNullable($application['specialty'])) ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--even">
                            <td class="admin-table-td"><strong>Experience (Years)</strong></td>
                            <td class="admin-table-td"><?= $application['experienceYears'] !== null ? (int) $application['experienceYears'] . ' years' : '-' ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--odd">
                            <td class="admin-table-td"><strong>Languages Spoken</strong></td>
                            <td class="admin-table-td"><?= htmlspecialchars($formatNullable($application['languagesSpoken'])) ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--even">
                            <td class="admin-table-td"><strong>Consultation Fee</strong></td>
                            <td class="admin-table-td"><?= $application['consultationFee'] !== null ? '$' . number_format($application['consultationFee'], 2) : '-' ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--odd" style="vertical-align: top;">
                            <td class="admin-table-td"><strong>Bio</strong></td>
                            <td class="admin-table-td"><?= nl2br(htmlspecialchars($formatNullable($application['bio']))) ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--even" style="vertical-align: top;">
                            <td class="admin-table-td"><strong>Education</strong></td>
                            <td class="admin-table-td"><?= nl2br(htmlspecialchars($formatNullable($application['education']))) ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--odd" style="vertical-align: top;">
                            <td class="admin-table-td"><strong>Certifications</strong></td>
                            <td class="admin-table-td"><?= nl2br(htmlspecialchars($formatNullable($application['certifications']))) ?></td>
                        </tr>
                        <tr class="admin-table-row admin-table-row--even" style="vertical-align: top;">
                            <td class="admin-table-td"><strong>Availability Schedule</strong></td>
                            <td class="admin-table-td"><?= nl2br(htmlspecialchars($formatNullable($application['availabilitySchedule']))) ?></td>
                        </tr>
                        <?php if (!empty($application['documentsUrl'])): ?>
                        <tr class="admin-table-row admin-table-row--odd">
                            <td class="admin-table-td"><strong>Uploaded Document</strong></td>
                            <td class="admin-table-td">
                                <a href="<?= htmlspecialchars($application['documentsUrl']) ?>" target="_blank" class="admin-button admin-button--secondary">
                                    <span class="button-text">Download CV</span>
                                </a>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($application['reviewDate'])): ?>
                        <tr class="admin-table-row admin-table-row--even">
                            <td class="admin-table-td"><strong>Review Date</strong></td>
                            <td class="admin-table-td"><?= htmlspecialchars(date('F j, Y g:i A', strtotime($application['reviewDate']))) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($application['adminNotes'])): ?>
                        <tr class="admin-table-row admin-table-row--odd" style="vertical-align: top;">
                            <td class="admin-table-td"><strong>Admin Notes</strong></td>
                            <td class="admin-table-td"><?= nl2br(htmlspecialchars($application['adminNotes'])) ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if ($application['status'] === 'pending'): ?>
                <div class="admin-sub-container-1" style="margin-top: var(--spacing-lg); gap: var(--spacing-sm);">
                    <a href="/admin/counselor-management/approve?id=<?= (int) $application['applicationId'] ?>" class="admin-button admin-button--success">
                        <span class="button-text">Approve Application</span>
                    </a>
                    <a href="/admin/counselor-management/reject?id=<?= (int) $application['applicationId'] ?>" class="admin-button admin-button--danger">
                        <span class="button-text">Reject Application</span>
                    </a>
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
.status-badge--pending {
    background: #fff3cd;
    color: #856404;
}
.status-badge--approved {
    background: #d4edda;
    color: #155724;
}
.status-badge--rejected {
    background: #f8d7da;
    color: #721c24;
}
</style>

<?php require_once __DIR__ . '/../../common/admin.footer.php'; ?>
