<?php
require_once __DIR__ . '/analytics.model.php';
$timePeriod    = Request::get('timePeriod') ?? 'lastMonth';
$summary       = AnalyticsModel::getSummary();
$engagementChart = AnalyticsModel::getEngagementChart($timePeriod);
$planAdoptionChart = AnalyticsModel::getPlanAdoptionChart();
$sessionStatusChart = AnalyticsModel::getSessionStatusChart();
$moodChart     = AnalyticsModel::getMoodChart();
$planStatusChart = AnalyticsModel::getPlanStatusChart();
$topCounselors = AnalyticsModel::getTopCounselors();
