<?php

/**
 * Route: /user/notifications
 * Kept for backwards-compatibility — delegates to the shared /notifications endpoint.
 *
 * GET  ?ajax=list              — fetch latest 20 notifications + unread count
 * POST ?ajax=mark_read         — mark all as read
 * POST ?ajax=mark_one_read     — body: {notification_id} — mark single as read
 */

require_once __DIR__ . '/../common/user.head.php';
require_once ROOT . '/core/NotificationService.php';

header('Content-Type: application/json');

$userId     = (int)$user['id'];
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
