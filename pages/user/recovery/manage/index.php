<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/manage.model.php';

$userId = (int)$user['id'];

$activePlans = ManageModel::getActivePlans($userId);
$pendingPlans = ManageModel::getAssignedPlans($userId);
$pausedPlans = ManageModel::getPausedPlans($userId);

$data = [
    'activePlans' => $activePlans,
    'pendingPlans' => $pendingPlans,
    'pausedPlans' => $pausedPlans,
    'success' => $_GET['success'] ?? null,
];

$pageTitle = 'Manage Recovery Plans';
$pageStyle = ['user/dashboard', 'user/manage-plans'];

require __DIR__ . '/manage.view.php';