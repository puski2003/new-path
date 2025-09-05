<?php

/**
 * Route: /user/sessions/book/cancel
 *
 * PayHere redirects here when the user cancels or dismisses payment.
 * We release the booking hold so the slot becomes available again.
 */

require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/../book.model.php';

$holdId = (int)(Request::get('holdId') ?? 0);
$redirectUrl = '/user/counselors?cancelled=1';

if ($holdId > 0) {
    $hold = BookingModel::getHold($holdId);
    if ($hold && (int)($hold['userId'] ?? 0) === (int)($user['id'] ?? 0)) {
        $redirectUrl = '/user/counselors?id=' . (int)$hold['counselorId'] . '&cancelled=1';
    }
    BookingModel::releaseHold($holdId);
}

Response::redirect($redirectUrl);
exit;
