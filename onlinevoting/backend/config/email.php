<?php
/**
 * Email Configuration
 */

// Basic settings
define('EMAIL_FROM', 'noreply@votesecure.com');
define('EMAIL_FROM_NAME', 'VoteSecure System');
define('RESET_LINK_BASE', 'http://localhost/onlinevoting/reset-password.html');
define('TOKEN_EXPIRY_MINUTES', 30);

// Development mode: 
// true = return reset link in API response (for testing without email server)
// false = try to send actual email via PHP mail() or SMTP
define('EMAIL_DEV_MODE', true);

// SMTP Settings (Optional - for production)
define('SMTP_HOST', '');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
?>
