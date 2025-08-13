<?php

/**
 * Route: /common/notifications
 * Shared AJAX endpoint for the notification bell — works for user, counselor, and admin.
 *
 * Authentication: reads the JWT cookie directly (any valid, non-expired token is accepted).
 * Returns 401 JSON if the token is missing or invalid.
 *
 * GET  ?ajax=list              — fetch latest 20 notifications + unread count
 * POST ?ajax=mark_read         — mark all as read
 * POST ?ajax=mark_one_read     — body: {notification_id} — mark single as read
 */

require_once ROOT . '/core/NotificationService.php';

header('Content-Type: application/json');

$payload = Auth::getUser();
if ($payload === null) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$userId     = (int)($payload['id'] ?? 0);
$ajaxAction = Request::get('ajax');

switch ($ajaxAction) {

    case 'list':
        $result = NotificationService::list($userId);
        echo json_encode([
            'success'       => true,
            'notifications' => $result['items'],
            'unread'        => $result['unread'],
        ]);
        exit;

    case 'mark_read':
        NotificationService::markAllRead($userId);
        echo json_encode(['success' => true]);
        exit;

    case 'mark_one_read':
        $notifId = (int)(Request::post('notification_id') ?? 0);
        NotificationService::markOneRead($userId, $notifId);
        echo json_encode(['success' => true]);
        exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
exit;
