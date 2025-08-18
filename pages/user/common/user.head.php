<?php

/**
 * User head guard — include this at the top of every user page index.php.
 *
 * What it does:
 *   1. Verifies the JWT cookie exists and is valid
 *   2. Confirms the role is 'user'
 *   3. Sets $user = ['id', 'name', 'role', 'iat', 'exp']
 *   4. Redirects to /auth/login on any failure
 *
 * After this file is required, $user is available everywhere on the page.
 */
$user = Auth::requireRole('user');

// Enrich $user with live profile picture (not stored in JWT)
$_profileRs = Database::search(
    "SELECT profile_picture FROM users WHERE user_id = " . (int)$user['id'] . " LIMIT 1"
);
if ($_profileRs && ($_profileRow = $_profileRs->fetch_assoc())) {
    $user['profilePictureUrl'] = !empty($_profileRow['profile_picture'])
        ? $_profileRow['profile_picture']
        : '/assets/img/avatar.png';
} else {
    $user['profilePictureUrl'] = '/assets/img/avatar.png';
}
unset($_profileRs, $_profileRow);

// Normalise $pageStyle to an array
$_pageStyles = [];
if (!empty($pageStyle)) {
    $_pageStyles = is_array($pageStyle) ? $pageStyle : [$pageStyle];
}
