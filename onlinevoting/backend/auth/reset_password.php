<?php
/**
 * Reset Password Endpoint
 * Sets the new password
 */

header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method');
}

$input = json_decode(file_get_contents('php://input'), true);
$token = sanitize_input($input['token'] ?? '');
$password = $input['password'] ?? '';
$confirmPassword = $input['confirm_password'] ?? '';

// Validation
if (empty($token)) {
    json_response(false, 'Invalid request');
}

if (strlen($password) < 6) {
    json_response(false, 'Password must be at least 6 characters long');
}

if ($password !== $confirmPassword) {
    json_response(false, 'Passwords do not match');
}

try {
    // 1. Verify token again
    $stmt = $pdo->prepare("
        SELECT email 
        FROM password_resets 
        WHERE token = ? AND expires_at > NOW()
    ");
    $stmt->execute([$token]);
    $resetRequest = $stmt->fetch();
    
    if (!$resetRequest) {
        json_response(false, 'Invalid or expired password reset link.');
    }
    
    $email = $resetRequest['email'];
    
    // 2. Hash new password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // 3. Update password in databases
    // Update users table
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$hashedPassword, $email]);
    $usersUpdated = $stmt->rowCount();
    
    // Update admins table (if it exists)
    try {
        $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE email = ?");
        $stmt->execute([$hashedPassword, $email]);
        $adminsUpdated = $stmt->rowCount();
    } catch (Exception $e) {
        // Ignore if no admins table
    }
    
    // 4. Invalidate Used Token (Delete it)
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
    $stmt->execute([$token]);
    
    json_response(true, 'Password has been successfully updated. You can now login.');
    
} catch (PDOException $e) {
    error_log("Reset Password Error: " . $e->getMessage());
    json_response(false, 'Failed to update password. Please try again.');
}
?>
