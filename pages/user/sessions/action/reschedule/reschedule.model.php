<?php

class RescheduleModel
{
    public static function requestReschedule(int $userId, int $sessionId, string $reason): bool
    {
        if ($sessionId <= 0) return false;

        $rs = Database::search(
            "SELECT session_id, counselor_id FROM sessions
             WHERE session_id = $sessionId
               AND user_id    = $userId
               AND status     IN ('scheduled','confirmed')
               AND session_datetime > NOW()
             LIMIT 1"
        );
        if (!$rs || $rs->num_rows === 0) return false;

        $session     = $rs->fetch_assoc();
        $counselorId = (int)$session['counselor_id'];

        // Block if a pending request already exists
        $existing = Database::search(
            "SELECT request_id FROM reschedule_requests
             WHERE session_id = $sessionId AND status = 'pending'
             LIMIT 1"
        );
        if ($existing && $existing->num_rows > 0) return false;

        Database::setUpConnection();
        $safeReason = Database::$connection->real_escape_string(trim($reason));

        Database::iud(
            "INSERT INTO reschedule_requests (session_id, user_id, counselor_id, reason)
             VALUES ($sessionId, $userId, $counselorId, '$safeReason')"
        );

        // Fetch counselor user info (for notification + email)
        $cuRs = Database::search(
            "SELECT u.user_id, u.email,
                    COALESCE(u.display_name, CONCAT(u.first_name,' ',u.last_name), u.username, 'Counselor') AS counselor_name
             FROM counselors c
             JOIN users u ON u.user_id = c.user_id
             WHERE c.counselor_id = $counselorId LIMIT 1"
        );
        if ($cuRs) {
            $cuRow = $cuRs->fetch_assoc();
            $counselorUserId = (int)($cuRow['user_id'] ?? 0);
            $counselorEmail  = (string)($cuRow['email'] ?? '');
            $counselorName   = (string)($cuRow['counselor_name'] ?? 'Counselor');

            if ($counselorUserId > 0) {
                $t = Database::$connection->real_escape_string('Reschedule Request');
                $m = Database::$connection->real_escape_string('A client has requested to reschedule their upcoming session.');
                $l = Database::$connection->real_escape_string('/counselor/sessions');
                Database::iud(
                    "INSERT INTO notifications (user_id, type, title, message, link)
                     VALUES ($counselorUserId, 'reschedule_request', '$t', '$m', '$l')"
                );
            }

            if ($counselorEmail !== '') {
                require_once ROOT . '/core/Mailer.php';
                $userRs = Database::search(
                    "SELECT COALESCE(display_name, CONCAT(first_name,' ',last_name), username, 'Client') AS client_name
                     FROM users WHERE user_id = $userId LIMIT 1"
                );
                $clientName = 'A client';
                if ($userRs && ($uRow = $userRs->fetch_assoc())) {
                    $clientName = $uRow['client_name'];
                }
                $reasonHtml = $safeReason !== ''
                    ? "<p style='margin:8px 0;'><strong>Reason:</strong> " . htmlspecialchars(trim($reason)) . "</p>"
                    : '';
                $html = "
                    <div style='font-family:Montserrat,sans-serif;max-width:520px;margin:auto;padding:32px;'>
                        <h2 style='color:#2c3e50;margin-bottom:8px;'>Reschedule Request</h2>
                        <p style='color:#555;'>Hi " . htmlspecialchars($counselorName) . ", a client has requested to reschedule their upcoming session.</p>
                        <div style='background:#f9f9f9;border-radius:8px;padding:20px;margin:20px 0;'>
                            <p style='margin:8px 0;'><strong>Client:</strong> " . htmlspecialchars($clientName) . "</p>
                            $reasonHtml
                        </div>
                        <a href='/counselor/sessions' style='display:inline-block;padding:12px 28px;background:#4CAF50;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;'>
                            Review Request
                        </a>
                        <p style='color:#999;font-size:0.85rem;margin-top:24px;'>Log in to approve or decline the request.</p>
                    </div>";
                Mailer::send($counselorEmail, 'NewPath  Reschedule Request from Client', $html, $counselorName);
            }
        }

        return true;
    }
}
