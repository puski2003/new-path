<?php

class TaskChangeRequestsModel
{
    public static function getRequests(int $userId): array
    {
        $rs = Database::search(
            "SELECT tcr.request_id, tcr.status, tcr.reason, tcr.requested_change,
                    tcr.counselor_note, tcr.created_at,
                    rt.title AS task_title
             FROM task_change_requests tcr
             INNER JOIN recovery_tasks rt ON rt.task_id = tcr.task_id
             WHERE tcr.user_id = $userId
             ORDER BY tcr.created_at DESC"
        );

        $requests = [];
        if (!$rs) return $requests;
        while ($row = $rs->fetch_assoc()) {
            $requests[] = [
                'requestId' => (int)$row['request_id'],
                'taskTitle' => $row['task_title'] ?? 'Task',
                'status' => $row['status'] ?? 'pending',
                'reason' => $row['reason'] ?? '',
                'requestedChange' => $row['requested_change'] ?? '',
                'counselorNote' => $row['counselor_note'] ?? '',
                'createdAt' => date('M j, Y', strtotime($row['created_at'])),
            ];
        }
        return $requests;
    }
}