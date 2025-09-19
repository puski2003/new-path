<?php

class HelpCenterModel
{
    private static function esc(string $value): string
    {
        Database::setUpConnection();
        return Database::$connection->real_escape_string($value);
    }

    public static function getHelpCenters(array $filters = []): array
    {
        $where = ['1=1'];
        $type = trim((string) ($filters['type'] ?? 'all'));
        if ($type !== '' && $type !== 'all') {
            $where[] = "type = '" . self::esc($type) . "'";
        }

        $category = trim((string) ($filters['centerCategory'] ?? 'all'));
        if ($category !== '' && $category !== 'all') {
            $where[] = "category = '" . self::esc($category) . "'";
        }

        $status = trim((string) ($filters['centerStatus'] ?? 'all'));
        if ($status === 'active') {
            $where[] = 'is_active = 1';
        } elseif ($status === 'inactive') {
            $where[] = 'is_active = 0';
        }

        $rs = Database::search(
            "SELECT *
             FROM help_centers
             WHERE " . implode(' AND ', $where) . "
             ORDER BY created_at DESC, help_center_id DESC"
        );

        $items = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $items[] = [
                'helpCenterId' => (int) $row['help_center_id'],
                'name' => $row['name'] ?? '',
                'organization' => $row['organization'] ?? '',
                'type' => $row['type'] ?? '',
                'category' => $row['category'] ?? '',
                'phoneNumber' => $row['phone_number'] ?? '',
                'email' => $row['email'] ?? '',
                'website' => $row['website'] ?? '',
                'address' => $row['address'] ?? '',
                'city' => $row['city'] ?? '',
                'state' => $row['state'] ?? '',
                'zipCode' => $row['zip_code'] ?? '',
                'availability' => $row['availability'] ?? '',
                'description' => $row['description'] ?? '',
                'specialties' => $row['specialties'] ?? '',
                'active' => !empty($row['is_active']),
            ];
        }

        return $items;
    }

    public static function getHelpCentersPaginated(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $safePage = Pagination::sanitizePage($page);
        $safePerPage = Pagination::sanitizePerPage($perPage, 15, 100);

        $allCenters = self::getHelpCenters($filters);
        $totalRows = count($allCenters);
        $meta = Pagination::meta($totalRows, $safePage, $safePerPage);

        $items = array_slice($allCenters, $meta['offset'], $meta['perPage']);

        return [
            'items' => $items,
            'pagination' => $meta,
        ];
    }

    public static function getHelpCenterById(int $helpCenterId): ?array
    {
        foreach (self::getHelpCenters() as $center) {
            if ($center['helpCenterId'] === $helpCenterId) {
                return $center;
            }
        }
        return null;
    }

    public static function createHelpCenter(array $input, int $adminUserId): bool
    {
        $isActive = !empty($input['isActive']) ? 1 : 0;
        Database::iud(
            "INSERT INTO help_centers
                (name, organization, type, category, phone_number, email, website, address, city, state, zip_code, availability, description, specialties, is_active, created_by, created_at, updated_at)
             VALUES
                ('" . self::esc($input['name'] ?? '') . "',
                 '" . self::esc($input['organization'] ?? '') . "',
                 '" . self::esc($input['type'] ?? '') . "',
                 '" . self::esc($input['category'] ?? '') . "',
                 '" . self::esc($input['phoneNumber'] ?? '') . "',
                 '" . self::esc($input['email'] ?? '') . "',
                 '" . self::esc($input['website'] ?? '') . "',
                 '" . self::esc($input['address'] ?? '') . "',
                 '" . self::esc($input['city'] ?? '') . "',
                 '" . self::esc($input['state'] ?? '') . "',
                 '" . self::esc($input['zipCode'] ?? '') . "',
                 '" . self::esc($input['availability'] ?? '') . "',
                 '" . self::esc($input['description'] ?? '') . "',
                 '" . self::esc($input['specialties'] ?? '') . "',
                 $isActive,
                 " . max(1, $adminUserId) . ",
                 NOW(),
                 NOW())"
        );
        return true;
    }

    public static function updateHelpCenter(int $helpCenterId, array $input): bool
    {
        $isActive = !empty($input['isActive']) ? 1 : 0;
        Database::iud(
            "UPDATE help_centers
             SET name = '" . self::esc($input['name'] ?? '') . "',
                 organization = '" . self::esc($input['organization'] ?? '') . "',
                 type = '" . self::esc($input['type'] ?? '') . "',
                 category = '" . self::esc($input['category'] ?? '') . "',
                 phone_number = '" . self::esc($input['phoneNumber'] ?? '') . "',
                 email = '" . self::esc($input['email'] ?? '') . "',
                 website = '" . self::esc($input['website'] ?? '') . "',
                 address = '" . self::esc($input['address'] ?? '') . "',
                 city = '" . self::esc($input['city'] ?? '') . "',
                 state = '" . self::esc($input['state'] ?? '') . "',
                 zip_code = '" . self::esc($input['zipCode'] ?? '') . "',
                 availability = '" . self::esc($input['availability'] ?? '') . "',
                 description = '" . self::esc($input['description'] ?? '') . "',
                 specialties = '" . self::esc($input['specialties'] ?? '') . "',
                 is_active = $isActive,
                 updated_at = NOW()
             WHERE help_center_id = $helpCenterId"
        );
        return true;
    }

    public static function deleteHelpCenter(int $helpCenterId): bool
    {
        Database::iud("DELETE FROM help_centers WHERE help_center_id = $helpCenterId");
        return true;
    }
}
