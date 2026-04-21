<?php

class UserManagementModel
{
    private static function esc(string $value): string
    {
        Database::setUpConnection();
        return Database::$connection->real_escape_string($value);
    }

    private static function roleLabel(string $role): string
    {
        return match ($role) {
            'user' => 'Recovering User',
            'counselor' => 'Counselor',
            'admin' => 'Admin',
            default => ucfirst($role),
        };
    }

    public static function getUsers(array $filters): array
    {
        $where = ["role IN ('user', 'counselor', 'admin')"];

        $role = trim((string) ($filters['role'] ?? 'all'));
        if ($role !== '' && $role !== 'all') {
            $roleMap = [
                'Recovering User' => 'user',
                'Counselor' => 'counselor',
                'Admin' => 'admin',
                'Moderator' => 'admin',
            ];
            if (isset($roleMap[$role])) {
                $where[] = "role = '" . self::esc($roleMap[$role]) . "'";
            }
        }

        $status = trim((string) ($filters['status'] ?? 'all'));
        if ($status !== '' && $status !== 'all') {
            $where[] = match (strtolower($status)) {
                'active' => "is_active = 1",
                'inactive' => "is_active = 0",
                'pending' => "onboarding_completed = 0",
                default => "1=1",
            };
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $safeSearch = self::esc($search);
            $where[] = "(COALESCE(display_name, CONCAT(first_name, ' ', last_name), username, email) LIKE '%$safeSearch%' OR email LIKE '%$safeSearch%')";
        }

        $dateJoined = trim((string) ($filters['dateJoined'] ?? ''));
        if ($dateJoined !== '') {
            $safeDate = self::esc($dateJoined);
            $where[] = "DATE(created_at) = '$safeDate'";
        }

        $sql = "SELECT user_id, email, role, display_name, first_name, last_name, last_login, created_at, is_active, onboarding_completed, status
                FROM users
                WHERE " . implode(' AND ', $where) . "
                ORDER BY created_at DESC, user_id DESC";
        $rs = Database::search($sql);

        $users = [];
        while ($rs && ($row = $rs->fetch_assoc())) {
            $name = $row['display_name']
                ?: trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))
                ?: ($row['email'] ?? 'User');

            $engagementScore = 0;
            if (!empty($row['last_login'])) {
                $daysAgo = max(0, (int) floor((time() - strtotime($row['last_login'])) / 86400));
                $engagementScore = $daysAgo <= 3 ? 3 : ($daysAgo <= 10 ? 2 : 1);
            }
            $engagement = $engagementScore >= 3 ? 'High' : ($engagementScore === 2 ? 'Medium' : 'Low');

            $users[] = [
                'userId' => (int) $row['user_id'],
                'fullName' => $name,
                'email' => $row['email'] ?? '',
                'role' => self::roleLabel((string) ($row['role'] ?? 'user')),
                'status' => ($row['status'] ?? 'active') === 'banned' ? 'Banned' : (!$row['is_active'] ? 'Inactive' : ($row['role'] === 'user' && empty($row['onboarding_completed']) ? 'Pending' : 'Active')),
                'isBanned' => ($row['status'] ?? 'active') === 'banned',
                'engagement' => $engagement,
                'lastActive' => !empty($row['last_login']) ? date('M j, Y', strtotime($row['last_login'])) : 'Never',
                'registration' => !empty($row['created_at']) ? date('M j, Y', strtotime($row['created_at'])) : '-',
            ];
        }

        $engagementFilter = trim((string) ($filters['engagement'] ?? 'all'));
        if ($engagementFilter !== '' && $engagementFilter !== 'all') {
            $users = array_values(array_filter(
                $users,
                static fn(array $user): bool => strcasecmp($user['engagement'], $engagementFilter) === 0
            ));
        }

        return $users;
    }

    public static function getUsersPaginated(array $filters, int $page = 1, int $perPage = 15): array
    {
        $safePage = Pagination::sanitizePage($page);
        $safePerPage = Pagination::sanitizePerPage($perPage, 15, 100);

        $allUsers = self::getUsers($filters);
        $totalRows = count($allUsers);
        $meta = Pagination::meta($totalRows, $safePage, $safePerPage);

        $items = array_slice($allUsers, $meta['offset'], $meta['perPage']);

        return [
            'items' => $items,
            'pagination' => $meta,
        ];
    }

    public static function getUserById(int $userId): ?array
    {
        $safeUserId = max(0, $userId);
        $rs = Database::search(
            "SELECT user_id, email, username, role, first_name, last_name, display_name, profile_picture, phone_number, age, gender, is_active, onboarding_completed, current_onboarding_step, bio, created_at, last_login
             FROM users
             WHERE user_id = $safeUserId
             LIMIT 1"
        );

        if (!$rs || $rs->num_rows === 0) {
            return null;
        }

        $row = $rs->fetch_assoc();
        $name = $row['display_name']
            ?: trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))
            ?: ($row['email'] ?? 'User');

        return [
            'userId' => (int) $row['user_id'],
            'email' => $row['email'] ?? '',
            'username' => $row['username'] ?? '',
            'profilePicture' => $row['profile_picture'] ?? '',
            'phoneNumber' => $row['phone_number'] ?? '',
            'age' => $row['age'] !== null ? (int) $row['age'] : null,
            'gender' => $row['gender'] ?? '',
            'displayName' => $row['display_name'] ?? '',
            'firstName' => $row['first_name'] ?? '',
            'lastName' => $row['last_name'] ?? '',
            'fullName' => $name,
            'role' => (string) ($row['role'] ?? 'user'),
            'isActive' => !empty($row['is_active']),
            'onboardingCompleted' => !empty($row['onboarding_completed']),
            'currentOnboardingStep' => max(1, (int) ($row['current_onboarding_step'] ?? 1)),
            'bio' => $row['bio'] ?? '',
            'createdAt' => $row['created_at'] ?? null,
            'lastLogin' => $row['last_login'] ?? null,
        ];
    }

    public static function updateUser(int $userId, array $input): array
    {
        $safeUserId = max(0, $userId);
        $current = self::getUserById($safeUserId);
        if (!$current) {
            return ['ok' => false, 'type' => 'warning', 'message' => 'User not found.'];
        }

        $allowedRoles = ['user', 'counselor', 'admin'];
        $role = strtolower(trim((string) ($input['role'] ?? $current['role'])));
        if (!in_array($role, $allowedRoles, true)) {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Invalid role selected.'];
        }

        $email = strtolower(trim((string) ($input['email'] ?? $current['email'])));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Please provide a valid email address.'];
        }

        $username = trim((string) ($input['username'] ?? $current['username']));
        if ($username !== '') {
            if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
                return ['ok' => false, 'type' => 'warning', 'message' => 'Username must be 3-50 characters and contain only letters, numbers, and underscores.'];
            }
        }

        $dupEmail = Database::search("SELECT user_id FROM users WHERE email = '" . self::esc($email) . "' AND user_id <> $safeUserId LIMIT 1");
        if ($dupEmail && $dupEmail->num_rows > 0) {
            return ['ok' => false, 'type' => 'warning', 'message' => 'That email is already in use by another account.'];
        }

        if ($username !== '') {
            $dupUsername = Database::search("SELECT user_id FROM users WHERE username = '" . self::esc($username) . "' AND user_id <> $safeUserId LIMIT 1");
            if ($dupUsername && $dupUsername->num_rows > 0) {
                return ['ok' => false, 'type' => 'warning', 'message' => 'That username is already in use by another account.'];
            }
        }

        $displayName = trim((string) ($input['displayName'] ?? ''));
        $firstName = trim((string) ($input['firstName'] ?? ''));
        $lastName = trim((string) ($input['lastName'] ?? ''));
        $profilePicture = trim((string) ($input['profilePicture'] ?? ''));
        $phoneNumber = trim((string) ($input['phoneNumber'] ?? ''));
        $bio = trim((string) ($input['bio'] ?? ''));
        $onboardingCompleted = !empty($input['onboardingCompleted']) ? 1 : 0;
        $currentOnboardingStep = max(1, min(10, (int) ($input['currentOnboardingStep'] ?? $current['currentOnboardingStep'] ?? 1)));

        $gender = trim((string) ($input['gender'] ?? ''));
        $allowedGenders = ['male', 'female', 'other', 'prefer_not_to_say', ''];
        if (!in_array($gender, $allowedGenders, true)) {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Invalid gender selected.'];
        }
        $genderSql = $gender === '' ? 'NULL' : "'" . self::esc($gender) . "'";

        $ageRaw = trim((string) ($input['age'] ?? ''));
        $ageSql = 'NULL';
        if ($ageRaw !== '') {
            if (!ctype_digit($ageRaw)) {
                return ['ok' => false, 'type' => 'warning', 'message' => 'Age must be a whole number.'];
            }
            $age = (int) $ageRaw;
            if ($age < 13 || $age > 120) {
                return ['ok' => false, 'type' => 'warning', 'message' => 'Age must be between 13 and 120.'];
            }
            $ageSql = (string) $age;
        }

        $isActive = !empty($input['isActive']) ? 1 : 0;
        $actorId = (int) ($input['actorUserId'] ?? 0);

        $isAdminDemotionOrDeactivation = $current['role'] === 'admin' && ($role !== 'admin' || $isActive === 0);
        if ($isAdminDemotionOrDeactivation) {
            $adminCountRs = Database::search("SELECT COUNT(*) AS total FROM users WHERE role = 'admin' AND is_active = 1");
            $activeAdmins = (int) (($adminCountRs ? $adminCountRs->fetch_assoc()['total'] : 0) ?? 0);
            if ($activeAdmins <= 1) {
                return ['ok' => false, 'type' => 'warning', 'message' => 'At least one active admin account must remain.'];
            }
        }

        if ($actorId > 0 && $actorId === $safeUserId) {
            if ($isActive === 0) {
                return ['ok' => false, 'type' => 'warning', 'message' => 'You cannot deactivate your own account.'];
            }
            if ($current['role'] === 'admin' && $role !== 'admin') {
                return ['ok' => false, 'type' => 'warning', 'message' => 'You cannot remove your own admin role.'];
            }
        }

        Database::iud(
            "UPDATE users
             SET email = '" . self::esc($email) . "',
                 username = " . ($username === '' ? "NULL" : "'" . self::esc($username) . "'") . ",
                 role = '" . self::esc($role) . "',
                 display_name = '" . self::esc($displayName) . "',
                 first_name = '" . self::esc($firstName) . "',
                 last_name = '" . self::esc($lastName) . "',
                 profile_picture = " . ($profilePicture === '' ? "NULL" : "'" . self::esc($profilePicture) . "'") . ",
                 phone_number = '" . self::esc($phoneNumber) . "',
                 age = $ageSql,
                 gender = $genderSql,
                 is_active = $isActive,
                 onboarding_completed = $onboardingCompleted,
                 current_onboarding_step = $currentOnboardingStep,
                 bio = " . ($bio === '' ? "NULL" : "'" . self::esc($bio) . "'") . ",
                 updated_at = NOW()
             WHERE user_id = $safeUserId"
        );

        return ['ok' => true, 'type' => 'success', 'message' => 'User updated successfully.'];
    }

    public static function unbanUser(int $userId, int $actorUserId): array
    {
        $safeUserId = max(0, $userId);

        if ($safeUserId <= 0) {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Invalid user selected.'];
        }

        $target = self::getUserById($safeUserId);
        if (!$target) {
            return ['ok' => false, 'type' => 'warning', 'message' => 'User not found.'];
        }

        Database::iud("UPDATE users SET status = 'active', updated_at = NOW() WHERE user_id = $safeUserId");

        $identifier = '#' . $safeUserId . ' (' . ($target['fullName'] !== '' ? $target['fullName'] : $target['email']) . ')';
        return ['ok' => true, 'type' => 'success', 'message' => 'User ' . $identifier . ' has been unbanned.'];
    }

    public static function banUser(int $userId, int $actorUserId): array
    {
        $safeUserId = max(0, $userId);
        $safeActorUserId = max(0, $actorUserId);

        if ($safeUserId <= 0) {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Invalid user selected.'];
        }

        if ($safeUserId === $safeActorUserId) {
            return ['ok' => false, 'type' => 'warning', 'message' => 'You cannot ban your own account.'];
        }

        $target = self::getUserById($safeUserId);
        if (!$target) {
            return ['ok' => false, 'type' => 'warning', 'message' => 'User not found.'];
        }

        if ($target['role'] === 'admin') {
            $adminCountRs = Database::search("SELECT COUNT(*) AS total FROM users WHERE role = 'admin' AND status != 'banned'");
            $activeAdmins = (int) (($adminCountRs ? $adminCountRs->fetch_assoc()['total'] : 0) ?? 0);
            if ($activeAdmins <= 1) {
                return ['ok' => false, 'type' => 'warning', 'message' => 'At least one active admin account must remain.'];
            }
        }

        Database::iud("UPDATE users SET status = 'banned', updated_at = NOW() WHERE user_id = $safeUserId");

        $identifier = '#' . $safeUserId . ' (' . ($target['fullName'] !== '' ? $target['fullName'] : $target['email']) . ')';
        return ['ok' => true, 'type' => 'success', 'message' => 'User ' . $identifier . ' has been banned.'];
    }
}
