<?php
$service = $service ?? [];
$serviceId = (int)($service['id'] ?? 0);
?>
<div id="serviceDetails-<?= $serviceId ?>" class="service-detail-template" hidden>
    <div class="center-details">
        <div class="detail-section">
            <h4>Contact Information</h4>
            <?php if (!empty($service['phoneNumber'])): ?>
                <p><strong>Phone:</strong> <a href="tel:<?= htmlspecialchars((string)$service['phoneNumber']) ?>"><?= htmlspecialchars((string)$service['phoneNumber']) ?></a></p>
            <?php endif; ?>
            <?php if (!empty($service['email'])): ?>
                <p><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars((string)$service['email']) ?>"><?= htmlspecialchars((string)$service['email']) ?></a></p>
            <?php endif; ?>
            <?php if (!empty($service['website'])): ?>
                <p><strong>Website:</strong> <a href="<?= htmlspecialchars((string)$service['website']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars((string)$service['website']) ?></a></p>
            <?php endif; ?>
        </div>

        <?php if (!empty($service['location'])): ?>
            <div class="detail-section">
                <h4>Location</h4>
                <p><?= htmlspecialchars((string)$service['location']) ?></p>
            </div>
        <?php endif; ?>

        <div class="detail-section">
            <h4>Service Details</h4>
            <p><strong>Type:</strong> <?= htmlspecialchars((string)($service['typeLabel'] ?? '')) ?></p>
            <p><strong>Category:</strong> <?= htmlspecialchars((string)($service['categoryLabel'] ?? 'Not specified')) ?></p>
            <?php if (!empty($service['availability'])): ?>
                <p><strong>Availability:</strong> <?= htmlspecialchars((string)$service['availability']) ?></p>
            <?php endif; ?>
            <?php if (!empty($service['organization'])): ?>
                <p><strong>Organization:</strong> <?= htmlspecialchars((string)$service['organization']) ?></p>
            <?php endif; ?>
        </div>

        <?php if (!empty($service['description'])): ?>
            <div class="detail-section">
                <h4>Description</h4>
                <p><?= htmlspecialchars((string)$service['description']) ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($service['specialties'])): ?>
            <div class="detail-section">
                <h4>Specialties</h4>
                <p><?= htmlspecialchars((string)$service['specialties']) ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
