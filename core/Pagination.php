<?php

class Pagination
{
    public static function sanitizePage(mixed $page): int
    {
        $value = (int) $page;
        return $value > 0 ? $value : 1;
    }

    public static function sanitizePerPage(mixed $perPage, int $default = 15, int $max = 100): int
    {
        $value = (int) $perPage;
        if ($value <= 0) {
            $value = $default;
        }
        return min($value, $max);
    }

    public static function offset(int $page, int $perPage): int
    {
        return max(0, ($page - 1) * $perPage);
    }

    public static function meta(int $totalRows, int $page, int $perPage): array
    {
        $safeTotalRows = max(0, $totalRows);
        $safePerPage = max(1, $perPage);
        $totalPages = max(1, (int) ceil($safeTotalRows / $safePerPage));
        $currentPage = min(max(1, $page), $totalPages);
        $offset = self::offset($currentPage, $safePerPage);

        $fromRow = $safeTotalRows === 0 ? 0 : ($offset + 1);
        $toRow = min($safeTotalRows, $offset + $safePerPage);

        return [
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalRows' => $safeTotalRows,
            'perPage' => $safePerPage,
            'offset' => $offset,
            'fromRow' => $fromRow,
            'toRow' => $toRow,
            'hasPrev' => $currentPage > 1,
            'hasNext' => $currentPage < $totalPages,
            'prevPage' => max(1, $currentPage - 1),
            'nextPage' => min($totalPages, $currentPage + 1),
        ];
    }
}
