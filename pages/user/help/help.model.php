<?php

class HelpModel
{
    private static function fetchCenters(string $whereClause): array
    {
        $rs = Database::search(
            "SELECT help_center_id, name, organization, type, category, phone_number, email, website,
                    address, city, state, zip_code, availability, description, specialties
             FROM help_centers
             WHERE is_active = 1 AND $whereClause
             ORDER BY created_at DESC"
        );

        $items = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $items[] = [
                'helpCenterId' => (int)$row['help_center_id'],
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
            ];
        }

        return $items;
    }

    public static function getActiveHelpCenters(): array
    {
        return self::fetchCenters("category != 'emergency'");
    }

    public static function getActiveEmergencyServices(): array
    {
        return self::fetchCenters("category = 'emergency'");
    }
}

