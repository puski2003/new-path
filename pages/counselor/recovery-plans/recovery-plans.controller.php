<?php

$counselorId = (int) ($user['counselorId'] ?? 0);
$plans = CounselorRecoveryPlansModel::getAll($counselorId);
$changeRequests = CounselorRecoveryPlansModel::getPendingChangeRequests($counselorId);
$pendingChangeRequestCount = count($changeRequests); 
$filter=Request::get('filter') ?? '';
if(!empty($filter)){
    $plans=CounselorRecoveryPlansModel::filterRecoveryPlans($counselorId,$filter);
}
