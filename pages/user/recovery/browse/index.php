<?php
require_once __DIR__ . '/../../common/user.head.php';
require_once __DIR__ . '/browse.model.php';

$model = new BrowseModel();
$activePlans = $model->getUserActivePlans((int)$user['id']);

$data = [
    'plans'        => $model->getSystemPlans(),
    'hasActivePlan' => !empty($activePlans),
];

$pageTitle = 'Browse Recovery Plans';
$pageStyle = ['user/dashboard', 'user/manage-plans', 'user/browse-plans'];

require __DIR__ . '/browse.view.php';
