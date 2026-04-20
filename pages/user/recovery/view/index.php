<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/view.model.php';

$userId = (int)$user['id'];
$planId = (int)(Request::get('planId') ?? 0);
if ($planId <= 0) {
    Response::redirect('/user/recovery');
    exit;
}

$model = new ViewModel();

$plan = $model->getPlanByIdForUser($planId, $userId);
if ($plan === null) {
    Response::status(404);
    require ROOT . '/pages/404.php';
    exit;
}

$goals = $model->getGoalsByPlanId($planId);
$tasks = $model->getTasksByPlanId($planId, $userId);

$tasksByPhase = [];
foreach ($tasks as $task) {
    $tasksByPhase[$task['phase']][] = $task;
}
ksort($tasksByPhase);

$currentPhase = null;
foreach ($tasksByPhase as $phase => $phaseTasks) {
    foreach ($phaseTasks as $t) {
        if ($t['status'] !== 'completed') {
            $currentPhase = $phase;
            break 2;
        }
    }
}

$data = [
    'plan'          => $plan,
    'goals'         => $goals,
    'tasks'         => $tasks,
    'tasksByPhase'  => $tasksByPhase,
    'currentPhase'  => $currentPhase,
    'isSelfManaged' => empty($plan['counselorId']),
];

$pageTitle = 'View Recovery Plan';
$pageStyle = ['user/dashboard', 'user/manage-plans', 'user/recovery'];

require __DIR__ . '/view.view.php';
