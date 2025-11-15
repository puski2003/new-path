<?php
require_once __DIR__ . '/recovery-plans.model.php';
$activeTab = Request::get('tab') ?? 'pre-built';
$filters = ['search' => Request::get('search') ?? '', 'category' => Request::get('category') ?? 'all', 'status' => Request::get('status') ?? 'all'];

$step2Filters = [
    'scaleType' => Request::get('scaleType') ?? 'all',
    'path' => Request::get('path') ?? 'all',
    'weight' => Request::get('weight') ?? '',
    'status' => Request::get('status') ?? 'all',
];

$step3Filters = [
    'module' => Request::get('module') ?? 'all',
    'scaleType' => Request::get('scaleType') ?? 'all',
    'weight' => Request::get('weight') ?? '',
    'status' => Request::get('status') ?? 'all',
];

$page = Pagination::sanitizePage(Request::get('page') ?? 1);
$perPage = 15;

$plansResult = RecoveryPlansAdminModel::getTemplatesPaginated($filters, $page, $perPage);
$plans = $plansResult['items'];
$plansPagination = $plansResult['pagination'];
$questionsResult = RecoveryPlansAdminModel::getQuestionsPaginated($filters, $page, $perPage);
$questions = $questionsResult['items'];
$questionsPagination = $questionsResult['pagination'];

$step2Result = RecoveryPlansAdminModel::getStep2QuestionsPaginated($step2Filters, $page, $perPage);
$step2Questions = $step2Result['items'];
$step2Pagination = $step2Result['pagination'];

$step3Result = RecoveryPlansAdminModel::getStep3QuestionsPaginated($step3Filters, $page, $perPage);
$step3Questions = $step3Result['items'];
$step3Pagination = $step3Result['pagination'];

$scales = RecoveryPlansAdminModel::getAllScales();
$modules = RecoveryPlansAdminModel::getAllModules();
