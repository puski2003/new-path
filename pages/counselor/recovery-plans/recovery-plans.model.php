<?php

require_once __DIR__ . '/../common/counselor.data.php';

class CounselorRecoveryPlansModel
{
    public static function getAll(int $counselorId): array
    {
        return CounselorData::getPlansByCounselor($counselorId);
    }

    public static function getPendingChangeRequests(int $counselorId): array
    {
        return CounselorData::getChangeRequestsForCounselor($counselorId);
    }

    public static function filterRecoveryPlans(int $counselorId,string $filter):array{
        $rs=Database::search(
            "SELECT rp.*,COALESCE(u.display_name,CONCAT(u.first_name,' ',u.last_name),u.username,'Client') AS client_name
            FROM recovery_plans rp 
            INNER JOIN users u ON u.user_id=rp.user_id
            WHERE rp.counselor_id=$counselorId AND rp.status= '$filter'
            ORDER BY rp.updated_at DESC"
        );

        $plans=[];
        while($rs && $row=$rs->fetch_assoc()){
            $plans[]=[
                'planId'=>(int) $row['plan_id'],
                'userId' => (int) $row['user_id'],
                'title' => $row['title'] ?? 'Recovery Plan',
                'description' => $row['description'] ?? '',
                'status' => $row['status'] ?? 'draft',
                'progressPercentage' => (int) ($row['progress_percentage'] ?? 0),
                'clientName' => $row['client_name'] ?? 'Client',
                'updatedAt' => $row['updated_at'] ?? null,
            ];
        }
        return $plans;
    } 
}
