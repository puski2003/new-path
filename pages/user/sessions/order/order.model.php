<?php

require_once __DIR__ . '/../view/view.model.php';

class OrderModel
{
    public static function getSessionData(int $userId, int $sessionId): ?array
    {
        return ViewModel::getSessionById($userId, $sessionId);
    }
}
