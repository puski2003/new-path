<?php
$avatarUrl = !empty($counselor['profile_picture']) ? $counselor['profile_picture'] : '/assets/img/avatar.png';
$name = $counselor['name'] ?? 'Counselor';
$title = $counselor['title'] ?? 'Counselor';
$specialty = $counselor['specialty'] ?? 'Specialist';
$rating = $counselor['rating'] ?? null;
$totalReviews = max(0, (int)($counselor['total_reviews'] ?? 0));
$ratingLabel = $rating !== null ? htmlspecialchars((string)$rating) . ' (' . $totalReviews . ' reviews)' : 'No reviews yet';
$ratingValue = $rating !== null ? (float)$rating : null;
$ratingInt = $ratingValue !== null && $ratingValue > 0 ? (int)round($ratingValue) : 0;
$priceFormatted = $counselor['price_formatted'] ?? 'Rs. 0.00';
?>

<div class="counselor-profile-card">
    <div class="counselor-profile-avatar">
        <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="<?= htmlspecialchars($name) ?>" />
    </div>
    <div class="counselor-profile-info">
        <h3 class="counselor-profile-name"><?= htmlspecialchars($name) ?></h3>
        <p class="counselor-profile-title"><?= htmlspecialchars($title) ?></p>
        <p class="counselor-profile-specialty"><?= htmlspecialchars($specialty) ?></p>
        <div class="counselor-rating">
            <div class="stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <img src="/assets/icons/star.svg" class="star<?= $i > $ratingInt ? ' star-empty' : '' ?>" alt="<?= $i <= $ratingInt ? 'filled' : 'empty' ?> star" />
                <?php endfor; ?>
            </div>
            <span class="rating-score"><?= $ratingLabel ?></span>
        </div>
    </div>
    <div class="counselor-profile-pricing">
        <span class="price-label">Per Hour</span>
        <span class="price-amount"><?= htmlspecialchars($priceFormatted) ?></span>
    </div>
</div>
