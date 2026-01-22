<?php

require_once __DIR__ . '/counselor-payouts.model.php';

/* ── AJAX handlers ── */
if ($ajaxAction = Request::get('ajax')) {
    header('Content-Type: application/json');

    switch ($ajaxAction) {

        case 'mark_paid':
            $counselorId = (int) Request::post('counselor_id');
            if ($counselorId <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid counselor ID']);
                exit;
            }
            $newPayoutId = CounselorPayoutsModel::markAsPaid($counselorId);
            echo json_encode([
                'success' => $newPayoutId > 0,
                'error'   => $newPayoutId > 0 ? null : 'No pending balance found for this counselor.',
            ]);
            exit;

        case 'update_status':
            $payoutId = (int) Request::post('payout_id');
            $status   = trim((string) (Request::post('status') ?? ''));
            if ($payoutId <= 0 || $status === '') {
                echo json_encode(['success' => false, 'error' => 'Invalid data']);
                exit;
            }
            $ok = CounselorPayoutsModel::updateStatus($payoutId, $status);
            echo json_encode([
                'success' => $ok,
                'error'   => $ok ? null : 'Could not update status.',
            ]);
            exit;
    }

    echo json_encode(['success' => false, 'error' => 'Unknown action']);
    exit;
}

/* ── Page data ── */
$filters = [
    'status' => Request::get('status') ?? 'all',
];
$search  = trim((string) (Request::get('search') ?? ''));
$page    = Pagination::sanitizePage((int) (Request::get('page') ?? 1));
$perPage = 15;

$summary      = CounselorPayoutsModel::getSummary();
$result       = CounselorPayoutsModel::getPayouts($filters, $search, $page, $perPage);
$payouts      = $result['items'];
$pagination   = $result['pagination'];
