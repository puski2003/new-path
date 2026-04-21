<?php

/**
 * Admin Dashboard Controller
 * GET only — no forms on this page.
 * Loads data from the model and prepares $data for the layout.
 */
require_once 'dashboard.model.php';

$data = DashboardModel::getSummary();
$chartData = DashboardModel::getChartData();
$revenueChart = DashboardModel::getRevenueChartData();
