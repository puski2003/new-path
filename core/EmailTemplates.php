<?php

class EmailTemplates
{
    public static function applicationReceived(array $data): array
    {
        $name = htmlspecialchars($data['name'] ?? 'Applicant');

        $subject = 'We Received Your NewPath Counselor Application';

        $body = "
            <div style='font-family:Montserrat,sans-serif;max-width:520px;margin:auto;padding:32px;'>
                <h2 style='color:#2c3e50;margin-bottom:8px;'>Thank you, $name!</h2>
                <p style='color:#555;'>We have received your application to join NewPath as a counselor. Our team will review your application and get back to you as soon as possible.</p>

                <div style='background:#f0f7ff;border-radius:8px;padding:20px;margin:20px 0;border-left:4px solid #3498db;'>
                    <p style='margin:0;color:#555;'>Typical review time is <strong>2–5 business days</strong>. You will receive an email with the outcome once your application has been reviewed.</p>
                </div>

                <p style='color:#555;'>If you have any questions in the meantime, feel free to reach out to our support team.</p>

                <div style='margin-top:24px;padding-top:16px;border-top:1px solid #eee;'>
                    <p style='color:#555;margin:0;'>Warm regards,<br><strong>NewPath Support Team</strong></p>
                </div>
            </div>
        ";

        return ['subject' => $subject, 'body' => $body];
    }

    public static function counselorApproval(array $data): array
    {
        $name = htmlspecialchars($data['name'] ?? 'Counselor');
        $email = htmlspecialchars($data['email'] ?? '');
        $password = htmlspecialchars($data['password'] ?? '');

        $subject = 'Congratulations! Your NewPath Counselor Application Has Been Approved';

        $body = "
            <div style='font-family:Montserrat,sans-serif;max-width:520px;margin:auto;padding:32px;'>
                <h2 style='color:#2c3e50;margin-bottom:8px;'>Welcome to NewPath, $name!</h2>
                <p style='color:#555;'>We are pleased to inform you that your counselor application has been approved. Your account has been created and you can now log in to your counselor dashboard.</p>
                
                <div style='background:#f9f9f9;border-radius:8px;padding:20px;margin:20px 0;border-left:4px solid #4CAF50;'>
                    <h3 style='margin:0 0 16px;color:#2c3e50;'>Your Account Credentials</h3>
                    <p style='margin:8px 0;'><strong>Email:</strong> $email</p>
                    <p style='margin:8px 0;'><strong>Password:</strong> $password</p>
                </div>
                
                <p style='color:#555;'><strong>Next Steps:</strong></p>
                <ol style='color:#555;'>
                    <li>Log in to your counselor dashboard using the credentials above.</li>
                    <li>Complete your profile with additional information.</li>
                    <li>Set your availability schedule.</li>
                    <li>Review our counselor guidelines and best practices.</li>
                </ol>
                
                <p style='color:#555;'>We recommend changing your password after your first login for security purposes.</p>
                
                <a href='/counselor/login' style='display:inline-block;margin-top:20px;padding:12px 28px;background:#4CAF50;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;'>
                    Log In to Your Dashboard
                </a>
                
                <p style='color:#999;font-size:0.85rem;margin-top:24px;'>If you have any questions, please don't hesitate to contact our support team.</p>
            </div>
        ";

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }

    public static function counselorRejection(array $data): array
    {
        $name = htmlspecialchars($data['name'] ?? 'Applicant');
        $reason = htmlspecialchars($data['reason'] ?? '');

        $subject = 'Update on Your NewPath Counselor Application';

        $reasonBlock = '';
        if (!empty($reason)) {
            $reasonBlock = "
                <div style='background:#fff3f3;border-radius:8px;padding:16px;margin:16px 0;border-left:4px solid #e74c3c;'>
                    <p style='margin:0;color:#555;'><strong>Reason:</strong> $reason</p>
                </div>
            ";
        }

        $body = "
            <div style='font-family:Montserrat,sans-serif;max-width:520px;margin:auto;padding:32px;'>
                <h2 style='color:#2c3e50;margin-bottom:8px;'>Dear $name,</h2>
                <p style='color:#555;'>Thank you for your interest in joining NewPath as a counselor. After careful review of your application, we regret to inform you that we are unable to move forward with your application at this time.</p>
                $reasonBlock
                <p style='color:#555;'>This decision was not easy, and we encourage you to review the feedback provided (if any) and consider reapplying in the future with an updated application.</p>
                <p style='color:#555;'>If you have any questions or would like to discuss this further, please contact our support team.</p>
                <p style='color:#555;'>We wish you the best in your future endeavors.</p>
                
                <div style='margin-top:24px;padding-top:16px;border-top:1px solid #eee;'>
                    <p style='color:#555;margin:0;'>Warm regards,<br><strong>NewPath Support Team</strong></p>
                </div>
            </div>
        ";

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }
}
