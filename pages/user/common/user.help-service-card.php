<?php
$service = $service ?? [];
$serviceId = (int)($service['id'] ?? 0);
?>
<div
    class="service-card"
    data-service-id="<?= $serviceId ?>"
    data-category="<?= htmlspecialchars((string)($service['category'] ?? '')) ?>"
    data-type="<?= htmlspecialchars((string)($service['type'] ?? '')) ?>"
    data-search-text="<?= htmlspecialchars((string)($service['searchText'] ?? ''), ENT_QUOTES) ?>"
    data-service-title="<?= htmlspecialchars((string)($service['title'] ?? 'Center Details'), ENT_QUOTES) ?>"
>
    <div class="service-header">
        <div class="service-info">
            <h4><?= htmlspecialchars((string)($service['title'] ?? '')) ?></h4>
            <?php if (!empty($service['organization'])): ?>
                <p class="organization-name"><?= htmlspecialchars((string)$service['organization']) ?></p>
            <?php endif; ?>
        </div>
        <span class="service-category"><?= htmlspecialchars((string)($service['categoryLabel'] ?? '')) ?></span>
    </div>
    <p class="service-description"><?= htmlspecialchars((string)($service['description'] ?? '')) ?></p>
    <div class="service-meta">
        <span class="service-availability status-<?= htmlspecialchars((string)($service['status'] ?? 'available')) ?>">
            <?= htmlspecialchars((string)($service['availability'] ?? 'Available')) ?>
        </span>
        <span class="service-type"><?= htmlspecialchars((string)($service['typeLabel'] ?? '')) ?></span>
    </div>
    <div class="service-actions">
        <button
            class="btn btn-primary service-contact-btn"
            data-service-id="<?= $serviceId ?>"
            data-phone="<?= htmlspecialchars((string)($service['phoneNumber'] ?? ''), ENT_QUOTES) ?>"
            data-email="<?= htmlspecialchars((string)($service['email'] ?? ''), ENT_QUOTES) ?>"
            data-website="<?= htmlspecialchars((string)($service['website'] ?? ''), ENT_QUOTES) ?>"
            data-title="<?= htmlspecialchars((string)($service['title'] ?? ''), ENT_QUOTES) ?>"
        >
            <?= htmlspecialchars((string)($service['contactLabel'] ?? 'Contact')) ?>
        </button>
        <button class="btn btn-link service-details-btn" data-service-id="<?= $serviceId ?>">View Details</button>
    </div>
</div>
