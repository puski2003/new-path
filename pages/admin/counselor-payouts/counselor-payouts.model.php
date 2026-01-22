<?php

class CounselorPayoutsModel
{
    private static function esc(string $value): string
    {
        Database::setUpConnection();
        return Database::$connection->real_escape_string($value);
    }

    public static function getSummary(): array
    {
        /* Total paid out across all counselors */
        $paidRs = Database::search(
            "SELECT COALESCE(SUM(amount), 0) AS paid_amount, COUNT(*) AS paid_count
             FROM counselor_payouts WHERE status = 'completed'"
        );
        $paidRow = $paidRs ? ($paidRs->fetch_assoc() ?: []) : [];

        /* Total earned by all counselors from completed transactions */
        $earnedRs = Database::search(
            "SELECT COALESCE(SUM(amount), 0) AS total_earned,
                    COUNT(DISTINCT counselor_id) AS counselor_count
             FROM transactions WHERE status = 'completed' AND counselor_id IS NOT NULL"
        );
        $earnedRow = $earnedRs ? ($earnedRs->fetch_assoc() ?: []) : [];

        $totalEarned = (float) ($earnedRow['total_earned'] ?? 0);
        $totalPaid   = (float) ($paidRow['paid_amount']    ?? 0);

        return [
            'pendingAmount'   => max(0.0, $totalEarned - $totalPaid),
            'paidAmount'      => $totalPaid,
            'pendingCount'    => (int) ($earnedRow['counselor_count'] ?? 0),
            'paidCount'       => (int) ($paidRow['paid_count']        ?? 0),
            'totalCount'      => (int) ($earnedRow['counselor_count'] ?? 0),
        ];
    }

    public static function getPayouts(array $filters, string $search, int $page, int $perPage): array
    {
        $safePage    = Pagination::sanitizePage($page);
        $safePerPage = Pagination::sanitizePerPage($perPage, 15, 100);
        $status      = trim((string) ($filters['status'] ?? 'all'));
        $items       = [];

        $searchWhere = '';
        if ($search !== '') {
            $safeSearch  = self::esc($search);
            $searchWhere = "AND (COALESCE(u.display_name, u.username, u.email) LIKE '%$safeSearch%' OR u.email LIKE '%$safeSearch%')";
        }

        /* ── Pending rows: counselors with earned > paid_out ── */
        if ($status === 'all' || $status === 'pending') {
            $rs = Database::search(
                "SELECT
                    c.counselor_id,
                    COALESCE(u.display_name, u.username, u.email) AS counselor_name,
                    u.email        AS counselor_email,
                    u.profile_picture AS counselor_avatar,
                    COALESCE(SUM(t.amount), 0) AS total_earned,
                    COUNT(t.transaction_id)    AS sessions_count,
                    COALESCE((
                        SELECT SUM(cp2.amount) FROM counselor_payouts cp2
                        WHERE cp2.counselor_id = c.counselor_id AND cp2.status = 'completed'
                    ), 0) AS total_paid
                 FROM counselors c
                 JOIN users u ON u.user_id = c.user_id
                 LEFT JOIN transactions t
                    ON t.counselor_id = c.counselor_id AND t.status = 'completed'
                 WHERE 1=1 $searchWhere
                 GROUP BY c.counselor_id
                 HAVING (total_earned - total_paid) > 0
                 ORDER BY total_earned DESC"
            );
            while ($rs && ($row = $rs->fetch_assoc())) {
                $pending = (float) $row['total_earned'] - (float) $row['total_paid'];
                $items[] = [
                    'payoutId'       => null,
                    'counselorName'  => $row['counselor_name']  ?? 'Counselor',
                    'counselorEmail' => $row['counselor_email'] ?? '',
                    'counselorAvatar'=> $row['counselor_avatar'] ?: '/assets/img/avatar.png',
                    'counselorId'    => (int) $row['counselor_id'],
                    'sessionsCount'  => (int) $row['sessions_count'],
                    'grossAmount'    => number_format($pending, 2),
                    'netAmount'      => number_format($pending, 2),
                    'commission'     => '0.00',
                    'commissionRate' => '0.0',
                    'currency'       => 'LKR',
                    'status'         => 'pending',
                    'paidAt'         => null,
                    'createdAt'      => '-',
                    'periodStart'    => '-',
                    'periodEnd'      => '-',
                ];
            }
        }

        /* ── Completed rows: from counselor_payouts history ── */
        if ($status === 'all' || $status === 'completed') {
            $rs = Database::search(
                "SELECT cp.payout_id, cp.amount, cp.currency, cp.period_start, cp.period_end,
                        cp.sessions_count, cp.status, cp.paid_at, cp.created_at,
                        cp.platform_commission, cp.commission_rate,
                        COALESCE(u.display_name, u.username, u.email) AS counselor_name,
                        u.profile_picture AS counselor_avatar,
                        u.email AS counselor_email,
                        cp.counselor_id
                 FROM counselor_payouts cp
                 JOIN counselors c ON c.counselor_id = cp.counselor_id
                 JOIN users u      ON u.user_id = c.user_id
                 WHERE cp.status = 'completed' $searchWhere
                 ORDER BY cp.paid_at DESC"
            );
            while ($rs && ($row = $rs->fetch_assoc())) {
                $gross = (float) ($row['amount'] ?? 0);
                $comm  = (float) ($row['platform_commission'] ?? 0);
                $items[] = [
                    'payoutId'       => (int) $row['payout_id'],
                    'counselorName'  => $row['counselor_name']  ?? 'Counselor',
                    'counselorEmail' => $row['counselor_email'] ?? '',
                    'counselorAvatar'=> $row['counselor_avatar'] ?: '/assets/img/avatar.png',
                    'counselorId'    => (int) $row['counselor_id'],
                    'sessionsCount'  => (int) ($row['sessions_count'] ?? 0),
                    'grossAmount'    => number_format($gross, 2),
                    'commission'     => number_format($comm, 2),
                    'commissionRate' => number_format((float) ($row['commission_rate'] ?? 0), 1),
                    'netAmount'      => number_format($gross - $comm, 2),
                    'currency'       => $row['currency'] ?? 'LKR',
                    'status'         => 'completed',
                    'paidAt'         => !empty($row['paid_at']) ? date('M j, Y', strtotime($row['paid_at'])) : null,
                    'createdAt'      => !empty($row['created_at']) ? date('M j, Y', strtotime($row['created_at'])) : '-',
                    'periodStart'    => !empty($row['period_start']) ? date('M j, Y', strtotime($row['period_start'])) : '-',
                    'periodEnd'      => !empty($row['period_end'])   ? date('M j, Y', strtotime($row['period_end']))   : '-',
                ];
            }
        }

        $totalRows = count($items);
        $meta      = Pagination::meta($totalRows, $safePage, $safePerPage);
        $paged     = array_slice($items, $meta['offset'], $meta['perPage']);

        return [
            'items'      => $paged,
            'pagination' => $meta,
        ];
    }

    /**
     * Mark a counselor's pending balance as paid.
     * Calculates the pending amount (total earned - already paid out),
     * inserts a new counselor_payouts record with status='completed',
     * and returns the new payout_id on success or 0 on failure.
     */
    public static function markAsPaid(int $counselorId): int
    {
        if ($counselorId <= 0) return 0;

        /* Calculate pending amount */
        $earnedRs = Database::search(
            "SELECT COALESCE(SUM(amount), 0) AS earned FROM transactions
             WHERE counselor_id = $counselorId AND status = 'completed'"
        );
        $earned = (float) ($earnedRs ? ($earnedRs->fetch_assoc()['earned'] ?? 0) : 0);

        $paidRs = Database::search(
            "SELECT COALESCE(SUM(amount), 0) AS paid FROM counselor_payouts
             WHERE counselor_id = $counselorId AND status = 'completed'"
        );
        $alreadyPaid = (float) ($paidRs ? ($paidRs->fetch_assoc()['paid'] ?? 0) : 0);

        $pending = round($earned - $alreadyPaid, 2);
        if ($pending <= 0) return 0;

        $now      = date('Y-m-d H:i:s');
        $today    = date('Y-m-d');

        /* Sessions count in this payout */
        $sessRs = Database::search(
            "SELECT COUNT(*) AS cnt FROM transactions
             WHERE counselor_id = $counselorId AND status = 'completed'"
        );
        $sessCount = (int) ($sessRs ? ($sessRs->fetch_assoc()['cnt'] ?? 0) : 0);

        Database::iud(
            "INSERT INTO counselor_payouts
                (counselor_id, amount, currency, period_start, period_end,
                 sessions_count, status, paid_at, created_at)
             VALUES
                ($counselorId, $pending, 'LKR', '2000-01-01', '$today',
                 $sessCount, 'completed', '$now', '$now')"
        );

        return (int) Database::$connection->insert_id;
    }

    public static function updateStatus(int $payoutId, string $status): bool
    {
        $allowed = ['pending', 'processing', 'completed', 'failed'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }
        $safeStatus = self::esc($status);
        $now        = date('Y-m-d H:i:s');
        $paidAt     = $status === 'completed' ? ", paid_at = '$now'" : '';

        Database::iud(
            "UPDATE counselor_payouts
             SET status = '$safeStatus', updated_at = '$now' $paidAt
             WHERE payout_id = $payoutId"
        );
        return Database::$connection->affected_rows > 0;
    }
}
