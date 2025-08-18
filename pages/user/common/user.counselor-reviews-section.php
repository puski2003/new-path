<?php
/**
 * Counselor reviews section — shown on the single counselor profile page.
 *
 * Variables expected from single-counselor.controller.php:
 *   $counselor        – counselor array  (rating, total_reviews, counselor_id)
 *   $reviews          – array of review rows for the current page
 *   $reviewsTotal     – int  total reviews in DB
 *   $reviewsPage      – int  current page
 *   $reviewsTotalPages– int  total pages
 *   $ratingBreakdown  – ['1'=>N, '2'=>N, '3'=>N, '4'=>N, '5'=>N]
 */

$ratingScore = $counselor['rating']       ?? '0.0';
$reviewCount = (int)($reviewsTotal > 0 ? $reviewsTotal : ($counselor['total_reviews'] ?? 0));

// Compute real percentages from breakdown
$totalRated = array_sum($ratingBreakdown);

function _reviewBarPct(array $bd, string $star, int $total): string
{
    if ($total === 0) return '0%';
    return round(($bd[$star] ?? 0) / $total * 100) . '%';
}

function _renderStarRow(int $rating): string
{
    $html = '<div class="review-stars">';
    for ($i = 1; $i <= 5; $i++) {
        $cls = $i > $rating ? ' star-empty' : '';
        $html .= '<img src="/assets/icons/star.svg" class="star' . $cls . '" alt="' . ($i <= $rating ? 'filled' : 'empty') . ' star" />';
    }
    $html .= '</div>';
    return $html;
}

function _timeAgo(string $dateStr): string
{
    if (empty($dateStr)) return '';
    $ts = strtotime($dateStr);
    if (!$ts)             return '';
    $diff = time() - $ts;
    if ($diff < 60)       return 'just now';
    if ($diff < 3600)     return (int)($diff / 60) . ' min ago';
    if ($diff < 86400)    return (int)($diff / 3600) . ' hr ago';
    if ($diff < 2592000)  return (int)($diff / 86400) . ' day' . ((int)($diff / 86400) !== 1 ? 's' : '') . ' ago';
    if ($diff < 31536000) return (int)($diff / 2592000) . ' month' . ((int)($diff / 2592000) !== 1 ? 's' : '') . ' ago';
    return (int)($diff / 31536000) . ' year' . ((int)($diff / 31536000) !== 1 ? 's' : '') . ' ago';
}
?>

<div class="reviews-section" id="reviews-section">
    <h4>Client Reviews</h4>

    <!-- Rating overview -->
    <div class="reviews-overview">
        <div class="rating-summary">
            <div class="rating-score-large"><?= htmlspecialchars((string)$ratingScore) ?></div>
            <div class="rating-stars-large">
                <?php $ratingInt = (int)round((float)$ratingScore);
                for ($i = 1; $i <= 5; $i++): ?>
                    <img src="/assets/icons/star.svg"
                         class="star<?= $i > $ratingInt ? ' star-empty' : '' ?>"
                         alt="<?= $i <= $ratingInt ? 'filled' : 'empty' ?> star" />
                <?php endfor; ?>
            </div>
            <div class="rating-count">
                <?= $reviewCount ?> review<?= $reviewCount !== 1 ? 's' : '' ?>
            </div>
        </div>

        <div class="rating-breakdown">
            <?php foreach ([5, 4, 3, 2, 1] as $star): ?>
            <div class="rating-bar">
                <span><?= $star ?></span>
                <div class="bar">
                    <div class="fill"
                         style="width: <?= _reviewBarPct($ratingBreakdown, (string)$star, $totalRated) ?>">
                    </div>
                </div>
                <span><?= _reviewBarPct($ratingBreakdown, (string)$star, $totalRated) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Individual reviews -->
    <div class="individual-reviews">
        <?php if (empty($reviews)): ?>
            <div class="reviews-empty">
                No reviews yet for this counselor.
            </div>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
            <div class="review-item">
                <div class="review-header">
                    <img src="<?= htmlspecialchars($review['reviewer_avatar']) ?>"
                         alt="<?= htmlspecialchars($review['reviewer_name']) ?>"
                         class="reviewer-avatar" />
                    <div class="reviewer-info">
                        <span class="reviewer-name"><?= htmlspecialchars($review['reviewer_name']) ?></span>
                        <span class="review-date"><?= _timeAgo($review['date']) ?></span>
                    </div>
                </div>
                <?= _renderStarRow((int)$review['rating']) ?>
                <?php if (!empty($review['review'])): ?>
                    <p class="review-text"><?= nl2br(htmlspecialchars($review['review'])) ?></p>
                <?php else: ?>
                    <p class="review-text review-no-text"><em>No written review.</em></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($reviewsTotalPages > 1): ?>
    <div class="reviews-pagination">
        <?php if ($reviewsPage > 1): ?>
            <a href="?id=<?= (int)$counselor['counselor_id'] ?>&reviewsPage=<?= $reviewsPage - 1 ?>#reviews-section"
               class="reviews-page-btn">&larr; Prev</a>
        <?php endif; ?>

        <span class="reviews-page-info">Page <?= $reviewsPage ?> of <?= $reviewsTotalPages ?></span>

        <?php if ($reviewsPage < $reviewsTotalPages): ?>
            <a href="?id=<?= (int)$counselor['counselor_id'] ?>&reviewsPage=<?= $reviewsPage + 1 ?>#reviews-section"
               class="reviews-page-btn">Next &rarr;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
