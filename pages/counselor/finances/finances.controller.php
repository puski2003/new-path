<?php

$counselorId = (int) ($user['counselorId'] ?? 0);

$search        = trim((string) (Request::get('search') ?? ''));
$statusFilter  = trim((string) (Request::get('payoutStatus') ?? 'all'));
$activeTab     = trim((string) (Request::get('tab') ?? 'payments'));

$txPage     = Pagination::sanitizePage((int) (Request::get('txPage') ?? 1));
$payoutPage = Pagination::sanitizePage((int) (Request::get('payoutPage') ?? 1));

$summary = CounselorFinancesModel::getSummary($counselorId);

$paymentsResult = CounselorFinancesModel::getSessionPayments($counselorId, $search, $txPage, 15);
$payments       = $paymentsResult['items'];
$paymentsPagination = $paymentsResult['pagination'];

$payoutsResult = CounselorFinancesModel::getPayouts($counselorId, $statusFilter, $payoutPage, 10);
$payouts       = $payoutsResult['items'];
$payoutsPagination = $payoutsResult['pagination'];

$pageHeaderTitle    = 'Finances';
$pageHeaderSubtitle = 'Your session earnings and weekly payouts';
