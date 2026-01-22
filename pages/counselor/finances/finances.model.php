<?php

class CounselorFinancesModel
{
    private static function esc(string $value): string
    {
        Database::setUpConnection();
        return Database::$connection->real_escape_string($value);
    }

    public static function getSummary(int $counselorId): array
    {
        $revenueRs = Database::search(
            "SELECT
                COALESCE(SUM(t.amount), 0) AS total_earned,
                COUNT(t.transaction_id)    AS total_sessions
             FROM transactions t
             WHERE t.counselor_id = $counselorId AND t.status = 'completed'"
        );
        $row = $revenueRs ? ($revenueRs->fetch_assoc() ?: []) : [];

        /* Total already paid out to this counselor */
        $paidRs = Database::search(
            "SELECT COALESCE(SUM(amount), 0) AS paid_amount
             FROM counselor_payouts
             WHERE counselor_id = $counselorId AND status = 'completed'"
        );
        $paidRow = $paidRs ? ($paidRs->fetch_assoc() ?: []) : [];

        $totalEarned = (float) ($row['total_earned'] ?? 0);
        $totalPaid   = (float) ($paidRow['paid_amount'] ?? 0);

        return [
            'totalEarned'    => $totalEarned,
            'totalSessions'  => (int) ($row['total_sessions'] ?? 0),
            /* Pending = everything earned that hasn't been paid out yet */
            'pendingPayout'  => max(0.0, $totalEarned - $totalPaid),
            'totalPaid'      => $totalPaid,
        ];
    }

    public static function getSessionPayments(int $counselorId, string $search = '', int $page = 1, int $perPage = 15): array
    {
        $safePage    = Pagination::sanitizePage($page);
        $safePerPage = Pagination::sanitizePerPage($perPage, 15, 100);

        $where = ["t.counselor_id = $counselorId"];
        if ($search !== '') {
            $safeSearch = self::esc($search);
            $where[] = "(t.transaction_uuid LIKE '%$safeSearch%' OR COALESCE(u.display_name, u.email) LIKE '%$safeSearch%')";
        }
        $whereStr = implode(' AND ', $where);

        $countRs = Database::search(
            "SELECT COUNT(*) AS total
             FROM transactions t
             JOIN users u ON u.user_id = t.user_id
             WHERE $whereStr"
        );
        $totalRows = (int) ($countRs ? ($countRs->fetch_assoc()['total'] ?? 0) : 0);
        $meta      = Pagination::meta($totalRows, $safePage, $safePerPage);

        $rs = Database::search(
            "SELECT t.transaction_id, t.transaction_uuid, t.amount, t.currency,
                    t.payment_type, t.status, t.created_at,
                    s.session_datetime, s.duration_minutes,
                    COALESCE(u.display_name, u.username, u.email) AS client_name,
                    u.profile_picture AS client_avatar
             FROM transactions t
             JOIN users u ON u.user_id = t.user_id
             LEFT JOIN sessions s ON s.session_id = t.session_id
             WHERE $whereStr
             ORDER BY t.created_at DESC
             LIMIT {$meta['perPage']} OFFSET {$meta['offset']}"
        );

        $items = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $items[] = [
                'rawId'           => (int) $row['transaction_id'],
                'transactionId'   => $row['transaction_uuid'] ?: ('TXN-' . $row['transaction_id']),
                'clientName'      => $row['client_name'] ?? 'Client',
                'clientAvatar'    => $row['client_avatar'] ?: '/assets/img/avatar.png',
                'sessionDate'     => !empty($row['session_datetime']) ? date('M j, Y', strtotime($row['session_datetime'])) : '-',
                'sessionTime'     => !empty($row['session_datetime']) ? date('g:i A', strtotime($row['session_datetime'])) : '-',
                'duration'        => (int) ($row['duration_minutes'] ?? 0),
                'amount'          => number_format((float) ($row['amount'] ?? 0), 2),
                'currency'        => $row['currency'] ?? 'LKR',
                'paymentType'     => ucfirst((string) ($row['payment_type'] ?? 'session')),
                'status'          => $row['status'] ?? 'pending',
                'date'            => !empty($row['created_at']) ? date('M j, Y', strtotime($row['created_at'])) : '-',
            ];
        }

        return [
            'items'      => $items,
            'pagination' => $meta,
        ];
    }

    public static function getPayouts(int $counselorId, string $statusFilter = 'all', int $page = 1, int $perPage = 10): array
    {
        $safePage    = Pagination::sanitizePage($page);
        $safePerPage = Pagination::sanitizePerPage($perPage, 10, 100);

        $where = ["counselor_id = $counselorId"];
        if ($statusFilter !== 'all' && $statusFilter !== '') {
            $safe = self::esc($statusFilter);
            $where[] = "status = '$safe'";
        }
        $whereStr = implode(' AND ', $where);

        $countRs   = Database::search("SELECT COUNT(*) AS total FROM counselor_payouts WHERE $whereStr");
        $totalRows = (int) ($countRs ? ($countRs->fetch_assoc()['total'] ?? 0) : 0);
        $meta      = Pagination::meta($totalRows, $safePage, $safePerPage);

        $rs = Database::search(
            "SELECT payout_id, amount, currency, period_start, period_end,
                    sessions_count, status, paid_at, created_at,
                    platform_commission, commission_rate
             FROM counselor_payouts
             WHERE $whereStr
             ORDER BY created_at DESC
             LIMIT {$meta['perPage']} OFFSET {$meta['offset']}"
        );

        $items = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $periodStart = !empty($row['period_start']) ? date('M j, Y', strtotime($row['period_start'])) : '-';
            $periodEnd   = !empty($row['period_end'])   ? date('M j, Y', strtotime($row['period_end']))   : '-';
            $items[] = [
                'payoutId'          => (int) $row['payout_id'],
                'amount'            => number_format((float) ($row['amount'] ?? 0), 2),
                'currency'          => $row['currency'] ?? 'LKR',
                'periodStart'       => $periodStart,
                'periodEnd'         => $periodEnd,
                'periodLabel'       => "$periodStart – $periodEnd",
                'sessionsCount'     => (int) ($row['sessions_count'] ?? 0),
                'status'            => $row['status'] ?? 'pending',
                'paidAt'            => !empty($row['paid_at']) ? date('M j, Y', strtotime($row['paid_at'])) : '-',
                'createdAt'         => !empty($row['created_at']) ? date('M j, Y', strtotime($row['created_at'])) : '-',
                'platformCommission'=> number_format((float) ($row['platform_commission'] ?? 0), 2),
                'commissionRate'    => number_format((float) ($row['commission_rate'] ?? 0), 1),
            ];
        }

        return [
            'items'      => $items,
            'pagination' => $meta,
        ];
    }
}
