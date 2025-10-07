<?php

class WorkspaceModel
{
    /**
     * Fetch a single session that belongs to this counselor, with client info and notes.
     * Returns null if not found or counselor does not own the session.
     */
    public static function getSession(int $counselorId, int $sessionId): ?array
    {
        $rs = Database::search(
            "SELECT s.session_id, s.user_id, s.session_datetime, s.duration_minutes,
                    s.session_type, s.status, s.meeting_link,
                    s.session_notes, s.counselor_private_notes,
                    COALESCE(u.display_name, CONCAT(u.first_name,' ',u.last_name), u.username, 'Client') AS client_name,
                    u.profile_picture AS client_avatar,
                    u.email AS client_email
             FROM sessions s
             JOIN users u ON u.user_id = s.user_id
             WHERE s.session_id = $sessionId AND s.counselor_id = $counselorId
             LIMIT 1"
        );

        $row = $rs ? $rs->fetch_assoc() : null;
        if (!$row) {
            return null;
        }

        return [
            'sessionId'      => (int)$row['session_id'],
            'userId'         => (int)$row['user_id'],
            'clientName'     => $row['client_name'],
            'clientAvatar'   => $row['client_avatar'] ?: '/assets/img/avatar.png',
            'clientEmail'    => $row['client_email'] ?? '',
            'sessionDatetime'=> $row['session_datetime'],
            'durationMinutes'=> (int)($row['duration_minutes'] ?? 60),
            'sessionType'    => $row['session_type'] ?? 'video',
            'status'         => $row['status'] ?? 'scheduled',
            'meetingLink'    => $row['meeting_link'] ?? '',
            'sessionNotes'   => $row['session_notes'] ?? '',
            'privateNotes'   => $row['counselor_private_notes'] ?? '',
        ];
    }

    /**
     * Persist the counselor's private in-session notes.
     * Uses counselor_private_notes — not visible to the client.
     */
    public static function savePrivateNotes(int $counselorId, int $sessionId, string $notes): bool
    {
        Database::setUpConnection();
        $safe = Database::$connection->real_escape_string($notes);
        Database::iud(
            "UPDATE sessions
             SET counselor_private_notes = '$safe', updated_at = NOW()
             WHERE session_id = $sessionId AND counselor_id = $counselorId"
        );
        return true;
    }

    /**
     * Mark a counselor-owned session as completed.
     * Idempotent for already-completed sessions and blocked for cancelled/no-show sessions.
     */
    public static function markSessionCompleted(int $counselorId, int $sessionId): bool
    {
        $rs = Database::search(
            "SELECT status
             FROM sessions
             WHERE session_id = $sessionId AND counselor_id = $counselorId
             LIMIT 1"
        );

        $row = $rs ? $rs->fetch_assoc() : null;
        if (!$row) {
            return false;
        }

        $status = (string) ($row['status'] ?? '');
        if ($status === 'completed') {
            return true;
        }

        if (in_array($status, ['cancelled', 'no_show'], true)) {
            return false;
        }

        Database::iud(
            "UPDATE sessions
             SET status = 'completed', updated_at = NOW()
             WHERE session_id = $sessionId AND counselor_id = $counselorId"
        );

        return true;
    }
}
