<?php

/**
 * Counselor head guard.
 * Verifies the JWT role and hydrates the logged-in counselor profile.
 */
$authUser = Auth::requireRole('counselor');

Database::setUpConnection();
$userId = (int) ($authUser['id'] ?? 0);

$rs = Database::search(
    "SELECT c.counselor_id, c.user_id, c.title, c.specialty, c.specialty_short, c.bio,
            c.experience_years, c.education, c.certifications, c.languages_spoken,
            c.consultation_fee, c.availability_schedule, c.is_verified, c.rating,
            c.total_reviews, c.total_clients, c.total_sessions,
            u.email, u.username, u.display_name, u.first_name, u.last_name,
            u.profile_picture, u.phone_number, u.role
     FROM counselors c
     JOIN users u ON u.user_id = c.user_id
     WHERE c.user_id = $userId
     LIMIT 1"
);

$counselor = $rs ? $rs->fetch_assoc() : null;
if (!$counselor) {
    Response::redirect('/auth/login/counselor');
}

$displayName = $counselor['display_name']
    ?: trim(($counselor['first_name'] ?? '') . ' ' . ($counselor['last_name'] ?? ''))
    ?: ($authUser['name'] ?? 'Counselor');

$user = [
    'id' => $userId,
    'name' => $displayName,
    'role' => $authUser['role'] ?? 'counselor',
    'email' => $counselor['email'] ?? '',
    'username' => $counselor['username'] ?? '',
    'counselorId' => (int) $counselor['counselor_id'],
    'title' => $counselor['title'] ?? 'Counselor',
    'specialty' => $counselor['specialty'] ?? '',
    'bio' => $counselor['bio'] ?? '',
    'phoneNumber' => $counselor['phone_number'] ?? '',
    'consultationFee' => $counselor['consultation_fee'] ?? null,
    'profilePictureUrl' => $counselor['profile_picture'] ?: '/assets/img/avatar.png',
    'availabilitySchedule' => $counselor['availability_schedule'] ?? '{}',
    'displayName' => $displayName,
    'totalClients' => (int) ($counselor['total_clients'] ?? 0),
    'totalSessions' => (int) ($counselor['total_sessions'] ?? 0),
];

$currentCounselor = $user;

/* ── Follow-up popup data (available on every counselor page) ── */
$_counselorId = (int) $counselor['counselor_id'];
$_counselorUserId = $userId;
$_fuRs = Database::search(
    "SELECT s.session_id, s.session_datetime, s.updated_at,
            COALESCE(u.display_name, u.username, 'Client') AS client_name,
            u.profile_picture AS client_avatar,
            COUNT(sm.message_id) AS msg_count
     FROM sessions s
     JOIN users u ON u.user_id = s.user_id
     LEFT JOIN session_messages sm ON sm.session_id = s.session_id
     WHERE s.counselor_id = $_counselorId AND s.status = 'completed'
     GROUP BY s.session_id
     ORDER BY s.session_datetime DESC
     LIMIT 30"
);
$followupSessions = [];
while ($_fuRs && ($_fuRow = $_fuRs->fetch_assoc())) {
    $_completedTs = !empty($_fuRow['updated_at']) ? strtotime($_fuRow['updated_at']) : strtotime($_fuRow['session_datetime']);
    $_daysLeft    = max(0, (int) ceil(($_completedTs + 7 * 86400 - time()) / 86400));
    $_isLocked    = time() > $_completedTs + 7 * 86400;
    $followupSessions[] = [
        'sessionId'    => (int) $_fuRow['session_id'],
        'clientName'   => $_fuRow['client_name'],
        'clientAvatar' => $_fuRow['client_avatar'] ?: '/assets/img/avatar.png',
        'sessionDate'  => date('M j, Y', strtotime($_fuRow['session_datetime'])),
        'msgCount'     => (int) $_fuRow['msg_count'],
        'daysLeft'     => $_daysLeft,
        'isLocked'     => $_isLocked,
    ];
}
unset($_counselorId, $_counselorUserId, $_fuRs, $_fuRow, $_completedTs, $_daysLeft, $_isLocked);

$_pageStyles = [];
if (!empty($pageStyle)) {
    $_pageStyles = is_array($pageStyle) ? $pageStyle : [$pageStyle];
}
