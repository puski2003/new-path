<?php

/**
 * Forgot Password Controller — GET: show form, POST: send reset link.
 *
 * Works for all roles (user / counselor / admin) — looks up by email only.
 * Always shows a generic success message to avoid leaking whether an email exists.
 */
$error   = null;
$success = false;

if (Request::isPost()) {
    $email = trim(Request::post('email') ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        require_once __DIR__ . '/forgot-password.model.php';
        $user = ForgotPasswordModel::findByEmail($email);

        if ($user !== null) {
            // Generate a secure random token (64 hex chars = 256-bit)
            $token = bin2hex(random_bytes(32));

            ForgotPasswordModel::setResetToken((int) $user['user_id'], $token);

            // Build reset URL
            $scheme    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $base      = defined('APP_BASE') ? APP_BASE : '';
            $resetLink = "$scheme://$host$base/auth/reset-password?token=" . urlencode($token);

            // Send email via PHPMailer
            require_once ROOT . '/core/Mailer.php';
            $html = "
                <div style='font-family:Montserrat,sans-serif;max-width:480px;margin:auto;padding:32px;'>
                    <h2 style='color:#2c3e50;margin-bottom:8px;'>Reset your password</h2>
                    <p style='color:#555;'>Click the button below to set a new NewPath password. This link expires in <strong>1 hour</strong>.</p>
                    <a href='" . htmlspecialchars($resetLink) . "'
                       style='display:inline-block;margin:24px 0;padding:12px 28px;background:#4CAF50;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;'>
                        Reset Password
                    </a>
                    <p style='color:#999;font-size:0.85rem;'>If you didn't request this, you can safely ignore this email.</p>
                </div>";

            Mailer::send($email, 'NewPath  Reset your password', $html);
        }

        // Always show success — do not reveal whether the email was found
        $success = true;
    }
}
