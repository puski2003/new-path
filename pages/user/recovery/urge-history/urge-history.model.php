<?php

class UrgeHistoryModel
{
    public static function getUrgeLogs(int $userId, int $page = 1, int $limit = 15): array
    {
        $offset = ($page - 1) * $limit;

        $countRs = Database::search("SELECT COUNT(*) AS total FROM urge_logs WHERE user_id = $userId");
        $total = (int)($countRs->fetch_assoc()['total'] ?? 0);

        $rs = Database::search(
            "SELECT urge_id, intensity, trigger_category, coping_strategy_used, outcome, notes, logged_at
             FROM urge_logs
             WHERE user_id = $userId
             ORDER BY logged_at DESC
             LIMIT $limit OFFSET $offset"
        );

        $logs = [];
        while ($row = $rs->fetch_assoc()) {
            $logs[] = [
                'urgeId' => (int)$row['urge_id'],
                'intensity' => (int)$row['intensity'],
                'triggerCategory' => $row['trigger_category'] ?? '',
                'copingStrategy' => Encryption::decrypt($row['coping_strategy_used'] ?? ''),
                'outcome' => $row['outcome'] ?? '',
                'notes' => Encryption::decrypt($row['notes'] ?? ''),
                'loggedAt' => date('M j, Y g:i A', strtotime($row['logged_at'])),
            ];
        }

        return [
            'logs' => $logs,
            'total' => $total,
            'totalPages' => max(1, (int)ceil($total / $limit)),
            'page' => $page,
        ];
    }
}