<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/goals.model.php';

$userId = (int)$user['id'];
$model  = new GoalsModel();

$goals       = $model->getUserGoalsForActivePlan($userId);
$activePlans = $model->getUserActivePlans($userId);
$activePlan  = !empty($activePlans) ? $activePlans[0] : null;

$editId   = (int)(Request::get('edit') ?? 0);
$editGoal = null;
if ($editId > 0) {
    foreach ($goals as $g) {
        if ($g['goalId'] === $editId) { $editGoal = $g; break; }
    }
}

$data = [
    'goals'      => $goals,
    'activePlan' => $activePlan,
    'editGoal'   => $editGoal,
];

$pageTitle = 'My Goals';
$pageStyle = ['user/dashboard', 'user/manage-plans', 'user/recovery'];

require __DIR__ . '/goals.view.php';
