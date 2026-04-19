<?php

require_once __DIR__ . '/../journal.model.php';

class WriteModel
{
    public static function getCategories(int $userId): array
    {
        return JournalModel::getCategories($userId);
    }

    public static function getEntry(int $entryId, int $userId): ?array
    {
        return JournalModel::getEntry($entryId, $userId);
    }

    public static function save(int $userId, array $data): bool
    {
        return JournalModel::saveEntry($userId, $data);
    }
}