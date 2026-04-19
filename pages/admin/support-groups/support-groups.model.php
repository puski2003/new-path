<?php

class SupportGroupsModel
{
    private static function esc(string $value): string
    {
        Database::setUpConnection();
        return Database::$connection->real_escape_string($value);
    }

    public static function getGroups(array $filters): array
    {
        $where = ['1=1'];
        $status = trim((string) ($filters['status'] ?? 'all'));
        if ($status === 'Active') {
            $where[] = 'sg.is_active = 1';
        } elseif ($status === 'Archived') {
            $where[] = 'sg.is_active = 0';
        }
        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $safeSearch = self::esc($search);
            $where[] = "(sg.name LIKE '%$safeSearch%' OR sg.description LIKE '%$safeSearch%')";
        }

        $rs = Database::search(
            "SELECT sg.*, COUNT(sgm.membership_id) AS member_count, a.full_name AS admin_name
             FROM support_groups sg
             LEFT JOIN support_group_members sgm ON sgm.group_id = sg.group_id
             LEFT JOIN admin a ON a.admin_id = sg.created_by
             WHERE " . implode(' AND ', $where) . "
             GROUP BY sg.group_id
             ORDER BY sg.created_at DESC"
        );

        $groups = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $groups[] = [
                'groupId' => (int) $row['group_id'],
                'groupName' => $row['name'] ?? '',
                'description' => $row['description'] ?? '',
                // 'type' => !empty($row['meeting_link']) ? 'Public' : 'Private',
                'members' => (int) ($row['member_count'] ?? 0),
                'nextSession' => $row['meeting_schedule'] ?? 'To be scheduled',
                'createdBy' => $row['admin_name'] ?? 'Admin',
                'status' => !empty($row['is_active']) ? 'Active' : 'Archived',
            ];
        }

        return $groups;
    }

    public static function getGroupsPaginated(array $filters, int $page = 1, int $perPage = 15): array
    {
        $safePage = Pagination::sanitizePage($page);
        $safePerPage = Pagination::sanitizePerPage($perPage, 15, 100);

        $allGroups = self::getGroups($filters);
        $totalRows = count($allGroups);
        $meta = Pagination::meta($totalRows, $safePage, $safePerPage);

        $items = array_slice($allGroups, $meta['offset'], $meta['perPage']);

        return [
            'items' => $items,
            'pagination' => $meta,
        ];
    }

    public static function getUpcomingSessions(): array
    {
        return [
            ['day' => 'Mon', 'time' => '6:00 PM', 'title' => 'Peer Support Circle'],
            ['day' => 'Wed', 'time' => '7:30 PM', 'title' => 'Family Recovery Group'],
            ['day' => 'Sat', 'time' => '10:00 AM', 'title' => 'New Members Orientation'],
        ];
    }

    public static function createGroup(array $input, int $adminUserId): bool
    {
        $adminId = self::getAdminIdByUserId($adminUserId); 
        $maxMembers = is_numeric($input['max_members'] ?? null) ? (int) $input['max_members'] : 'NULL';

        Database::iud(
            "INSERT INTO support_groups
                (name, description, category, meeting_link, max_members, is_active, created_by, created_at, updated_at)
             VALUES
                ('" . self::esc($input['name'] ?? '') . "',
                 '" . self::esc($input['description'] ?? '') . "',
                 '" . self::esc($input['category'] ?? '') . "',
                 '" . self::esc($input['meeting_link'] ?? '') . "',
                 $maxMembers,
                 1,
                 " . max(1, $adminId) . ",
                 NOW(),
                 NOW())"
        );
        return true;
    }

    private static function getAdminIdByUserId(int $userId): int
    {
        $safeUserId = max(0, $userId);
        $rs = Database::search("SELECT admin_id FROM admin WHERE user_id = $safeUserId LIMIT 1");
        return (int) ($rs && $row = $rs->fetch_assoc() ? ($row['admin_id'] ?? 0) : 0);
    }

    public static function getGroupsForDropdown(): array
    {
        $rs = Database::search(
            "SELECT group_id, name, category 
             FROM support_groups 
             WHERE is_active = 1 
             ORDER BY name ASC"
        );

        $groups = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $groups[] = [
                'groupId' => (int) $row['group_id'],
                'name' => $row['name'],
                'category' => $row['category'],
            ];
        }
        return $groups;
    }

    public static function createSession(array $input, int $adminUserId): bool
    {
        $adminId = self::getAdminIdByUserId($adminUserId);
        $groupId = (int) ($input['group_id'] ?? 0);
        $title = self::esc(trim($input['title'] ?? ''));
        $description = self::esc(trim($input['description'] ?? ''));
        $sessionDatetime = self::esc($input['session_datetime'] ?? '');
        $durationMinutes = is_numeric($input['duration_minutes'] ?? null) ? (int) $input['duration_minutes'] : 60;
        $sessionType = self::esc($input['session_type'] ?? 'video');
        $meetingLink = self::esc($input['meeting_link'] ?? '');
        $meetingLocation = self::esc($input['meeting_location'] ?? '');
        $maxParticipants = is_numeric($input['max_participants'] ?? null) ? (int) $input['max_participants'] : 'NULL';
        $isRecurring = !empty($input['is_recurring']) ? 1 : 0;
        $recurrencePattern = $isRecurring ? self::esc($input['recurrence_pattern'] ?? '') : 'NULL';

        $sql = "INSERT INTO support_group_sessions
                    (group_id, title, description, session_datetime, duration_minutes, 
                     session_type, meeting_link, meeting_location, max_participants, 
                     is_recurring, recurrence_pattern, status, created_by, created_at, updated_at)
                 VALUES
                    ($groupId, '$title', '$description', '$sessionDatetime', $durationMinutes,
                     '$sessionType', '$meetingLink', '$meetingLocation', $maxParticipants,
                     $isRecurring, " . ($recurrencePattern === 'NULL' ? 'NULL' : "'$recurrencePattern'") . ",
                     'scheduled', " . max(1, $adminId) . ", NOW(), NOW())";

        Database::iud($sql);
        return true;
    }
}
