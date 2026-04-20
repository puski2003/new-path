<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/review.model.php';

$sessionId  = (int) Request::post('session_id');
$rating     = (int) Request::post('rating');
$reviewText = trim((string) (Request::post('review') ?? ''));
$userId     = (int) $user['id'];

if ($sessionId <= 0 || $rating < 1 || $rating > 5) {
    Response::redirect('/user/sessions?status=error&msg=Invalid+data');
    exit;
}

$ok = ReviewModel::submitReview($userId, $sessionId, $rating, $reviewText);

if ($ok) {
    Response::redirect('/user/sessions?status=success&msg=Review+submitted');
} else {
    Response::redirect('/user/sessions?id=' . $sessionId . '&status=error&msg=Could+not+submit+review');
}