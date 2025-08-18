<?php
$jobId = (int)($job['jobId'] ?? 0);
$jobType = (string)($job['jobType'] ?? 'Job');
$title = (string)($job['title'] ?? 'Untitled Job');
$company = (string)($job['company'] ?? 'Unknown Company');
$location = (string)($job['location'] ?? '');
$salaryRange = (string)($job['salaryRange'] ?? '');
$salary = $job['salary'] ?? null;
$description = (string)($job['description'] ?? '');
$applicationUrl = (string)($job['applicationUrl'] ?? '');
$contactEmail = (string)($job['contactEmail'] ?? '');
$isSaved = !empty($job['isSaved']);

$shortDescription = strlen($description) > 100 ? (substr($description, 0, 100) . '...') : $description;
?>
<div class="job-card" data-job-id="<?= $jobId ?>">
    <div class="job-card-header">
        <div class="job-position">
            <span class="job-type"><?= htmlspecialchars($jobType) ?></span>
            <h3 class="job-title"><?= htmlspecialchars($title) ?></h3>
            <p class="job-company"><?= htmlspecialchars($company) ?><?= $location !== '' ? ', ' . htmlspecialchars($location) : '' ?></p>

            <?php if ($salaryRange !== '' || $salary !== null): ?>
                <p class="job-salary">
                    <?php if ($salaryRange !== ''): ?>
                        <?= htmlspecialchars($salaryRange) ?>
                    <?php else: ?>
                        $<?= number_format((float)$salary, 0) ?>
                    <?php endif; ?>
                </p>
            <?php endif; ?>

            <?php if ($shortDescription !== ''): ?>
                <p class="job-description"><?= htmlspecialchars($shortDescription) ?></p>
            <?php endif; ?>
        </div>

        <div class="job-actions">
            <button class="btn <?= $isSaved ? 'btn-secondary saved' : 'btn-outline' ?> save-btn" data-job-id="<?= $jobId ?>">
                <?= $isSaved ? 'Saved' : 'Save' ?>
            </button>

            <?php if ($applicationUrl !== ''): ?>
                <a href="<?= htmlspecialchars($applicationUrl) ?>" target="_blank" rel="noopener" class="btn btn-primary apply-btn">Apply</a>
            <?php elseif ($contactEmail !== ''): ?>
                <a href="mailto:<?= htmlspecialchars($contactEmail) ?>?subject=<?= rawurlencode('Application for ' . $title) ?>" class="btn btn-primary apply-btn">Apply</a>
            <?php else: ?>
                <button class="btn btn-primary apply-btn" type="button">View Details</button>
            <?php endif; ?>
        </div>
    </div>
    <div class="job-company-logo">
        <div class="company-logo-placeholder"></div>
    </div>
</div>
