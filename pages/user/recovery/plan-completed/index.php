<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/plan-completed.model.php';

$userId = (int)$user['id'];
$planId = (int)($_GET['planId'] ?? 0);

$plan = $planId > 0 ? PlanCompletedModel::getPlanDetails($planId, $userId) : null;
if (!$plan) {
    Response::redirect('/user/recovery');
}

$stats = PlanCompletedModel::getStats($userId);

$data = [
    'plan' => $plan,
    'stats' => $stats,
];

$pageTitle = 'Plan Completed';
$pageStyle = ['user/dashboard', 'user/manage-plans', 'user/recovery'];

require __DIR__ . '/plan-completed.view.php';