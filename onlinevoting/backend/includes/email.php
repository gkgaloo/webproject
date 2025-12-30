<?php
/**
 * Email Utility Functions
 */

require_once __DIR__ . '/../config/email.php';

/**
 * Send password reset email
 * @param string $email
 * @param string $token
 * @return bool
 */
function send_password_reset_email($email, $token) {
    $resetLink = get_reset_link($token);
    
    // In dev mode, we assume success and log the link (handled by endpoint)
    if (defined('EMAIL_DEV_MODE') && EMAIL_DEV_MODE) {
        error_log("DEV MODE - Password Reset Link for $email: $resetLink");
        return true;
    }

    $subject = "Reset Your Password - VoteSecure";
    $message = render_email_template([
        'title' => 'Reset Your Password',
        'content' => "You requested a password reset. Click the link below to set a new password. This link expires in " . TOKEN_EXPIRY_MINUTES . " minutes.",
        'action_text' => 'Reset Password',
        'action_url' => $resetLink,
        'warning' => "If you didn't request this, you can safely ignore this email."
    ]);

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: ' . EMAIL_FROM_NAME . ' <' . EMAIL_FROM . '>' . "\r\n";

    // Use PHP mail() - simple fallback
    // For production, suggest using PHPMailer or similar library
    return mail($email, $subject, $message, $headers);
}

/**
 * Generate full reset link
 * @param string $token
 * @return string
 */
function get_reset_link($token) {
    return RESET_LINK_BASE . '?token=' . $token;
}

/**
 * Render HTML email template
 * @param array $data
 * @return string
 */
function render_email_template($data) {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
            .header { background-color: #4f46e5; color: white; padding: 15px; text-align: center; border-radius: 5px 5px 0 0; }
            .content { padding: 20px; }
            .button { display: inline-block; padding: 10px 20px; background-color: #4f46e5; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { font-size: 12px; color: #777; text-align: center; margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>' . htmlspecialchars($data['title']) . '</h2>
            </div>
            <div class="content">
                <p>Hello,</p>
                <p>' . htmlspecialchars($data['content']) . '</p>
                <div style="text-align: center;">
                    <a href="' . htmlspecialchars($data['action_url']) . '" class="button">' . htmlspecialchars($data['action_text']) . '</a>
                </div>
                <p style="font-size: 0.9em; color: #666;">' . htmlspecialchars($data['warning']) . '</p>
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' VoteSecure System</p>
            </div>
        </div>
    </body>
    </html>';
}
?>
