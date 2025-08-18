<?php
$postId = (int)($currentPost['postId'] ?? 0);
$isLiked = !empty($currentPost['active']);
$likesCount = (int)($currentPost['likesCount'] ?? 0);
$commentsCount = (int)($currentPost['commentsCount'] ?? 0);
$sharesCount = (int)($currentPost['sharesCount'] ?? 0);
?>
<div class="post-actions-component">
    <button class="action-btn like-btn <?= $isLiked ? 'liked' : '' ?>" data-post-id="<?= $postId ?>">
        <i data-lucide="heart" class="action-icon <?= $isLiked ? 'filled' : '' ?>"></i>
        <span class="action-count"><?= $likesCount ?></span>
        <span class="action-text">Like</span>
    </button>

    <button class="action-btn comment-btn" data-post-id="<?= $postId ?>">
        <i data-lucide="message-circle" class="action-icon"></i>
        <span class="action-count"><?= $commentsCount ?></span>
        <span class="action-text">Comment</span>
    </button>

    <button class="action-btn share-btn" data-post-id="<?= $postId ?>">
        <i data-lucide="share-2" class="action-icon"></i>
       
        <span class="action-text">Share</span>
    </button>
</div>
