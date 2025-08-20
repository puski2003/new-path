<?php

/**
 * Mailer — raw SMTP over PHP sockets, no external libraries.
 * Supports STARTTLS + AUTH LOGIN (Gmail, Outlook, etc.)
 *
 * Usage:
 *   Mailer::send('to@example.com', 'Subject', '<p>HTML body</p>');
 */

class Mailer
{
    /**
     * Send an email.
     *
     * @param  string $toEmail
     * @param  string $subject
     * @param  string $htmlBody  HTML content
     * @param  string $toName   Optional display name for recipient
     * @return bool              true on success, false on failure
     */
    public static function send(string $toEmail, string $subject, string $htmlBody, string $toName = ''): bool
    {
        $host      = env('SMTP_HOST', '');
        $port      = (int) env('SMTP_PORT', 587);
        $user      = env('SMTP_USER', '');
        $pass      = env('SMTP_PASS', '');
        $fromEmail = env('SMTP_FROM_EMAIL', $user);
        $fromName  = env('SMTP_FROM_NAME', 'NewPath');

        $sock = fsockopen($host, $port, $errno, $errstr, 10);
        if (!$sock) {
            error_log("Mailer: connect failed — $errstr ($errno)");
            return false;
        }

        try {
            self::expect($sock, 220);

            self::cmd($sock, "EHLO localhost");
            self::expect($sock, 250);

            // Upgrade to TLS
            self::cmd($sock, "STARTTLS");
            self::expect($sock, 220);
            stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

            self::cmd($sock, "EHLO localhost");
            self::expect($sock, 250);

            // Authenticate
            self::cmd($sock, "AUTH LOGIN");
            self::expect($sock, 334);
            self::cmd($sock, base64_encode($user));
            self::expect($sock, 334);
            self::cmd($sock, base64_encode($pass));
            self::expect($sock, 235);

            // Envelope
            self::cmd($sock, "MAIL FROM:<$fromEmail>");
            self::expect($sock, 250);
            self::cmd($sock, "RCPT TO:<$toEmail>");
            self::expect($sock, 250);

            // Message
            self::cmd($sock, "DATA");
            self::expect($sock, 354);
            fwrite($sock, self::buildMessage($fromEmail, $fromName, $toEmail, $toName, $subject, $htmlBody));
            self::expect($sock, 250);

            self::cmd($sock, "QUIT");
            return true;

        } catch (\RuntimeException $e) {
            error_log('Mailer error: ' . $e->getMessage());
            return false;
        } finally {
            fclose($sock);
        }
    }

    private static function buildMessage(
        string $fromEmail, string $fromName,
        string $toEmail,   string $toName,
        string $subject,   string $htmlBody
    ): string {
        $boundary = bin2hex(random_bytes(8));

        $headers = implode("\r\n", [
            'From: =?UTF-8?B?' . base64_encode($fromName) . '?= <' . $fromEmail . '>',
            'To: =?UTF-8?B?' . base64_encode($toName ?: $toEmail) . '?= <' . $toEmail . '>',
            'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=',
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        ]);

        $plainText = strip_tags($htmlBody);

        $body = "--$boundary\r\n"
              . "Content-Type: text/plain; charset=UTF-8\r\n\r\n"
              . $plainText . "\r\n"
              . "--$boundary\r\n"
              . "Content-Type: text/html; charset=UTF-8\r\n\r\n"
              . $htmlBody . "\r\n"
              . "--$boundary--";

        return $headers . "\r\n\r\n" . $body . "\r\n.\r\n";
    }

    private static function cmd($sock, string $command): void
    {
        fwrite($sock, $command . "\r\n");
    }

    private static function expect($sock, int $expectedCode): string
    {
        $response = '';
        while ($line = fgets($sock, 512)) {
            $response .= $line;
            // Multi-line SMTP responses use "250-..." for continuation, "250 " for final line
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        $actualCode = (int) substr($response, 0, 3);
        if ($actualCode !== $expectedCode) {
            throw new \RuntimeException("SMTP expected $expectedCode, got $actualCode: $response");
        }
        return $response;
    }
}
