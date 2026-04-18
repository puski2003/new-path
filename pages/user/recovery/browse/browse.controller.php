<?php

$userId = (int)$user['id'];

$plans = BrowseModel::getSystemPlans();
$activePlans = BrowseModel::getUserActivePlans($userId);
$hasActivePlan = !empty($activePlans);

$pageTitle = 'Browse Recovery Plans';
$pageStyle = ['user/dashboard', 'user/manage-plans', 'user/browse-plans'];