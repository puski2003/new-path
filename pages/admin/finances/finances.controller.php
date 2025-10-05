<?php
require_once __DIR__ . '/finances.model.php';

$filters  = ['disputeStatus' => Request::get('disputeStatus') ?? 'all', 'disputeIssue' => Request::get('disputeIssue') ?? 'allIssues'];
$search   = Request::get('search') ?? '';
$page     = Pagination::sanitizePage(Request::get('page') ?? 1);
$perPage  = 15;

$summary            = FinancesModel::getSummary();
$revenueChart       = FinancesModel::getMonthlyRevenueChart();
$paymentTypeChart   = FinancesModel::getPaymentTypeChart();
$statusChart        = FinancesModel::getStatusChart();
$counselorRevChart  = FinancesModel::getCounselorRevenueChart();

$disputesResult      = FinancesModel::getDisputesPaginated($filters, $page, $perPage);
$disputes            = $disputesResult['items'];
$disputesPagination  = $disputesResult['pagination'];

$transactionsResult  = FinancesModel::getTransactionsPaginated($search, $page, $perPage);
$transactions        = $transactionsResult['items'];
$transactionsPagination = $transactionsResult['pagination'];

$payouts = FinancesModel::getPayouts();
