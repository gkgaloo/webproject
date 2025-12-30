<?php
/**
 * Validate Token Endpoint
 * Checks if a reset token is valid
 */

header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

$token = sanitize_input($_GET['token'] ?? '');

if (empty($token)) {
    json_response(false, 'Invalid token');
}

try {
    $stmt = $pdo->prepare("
        SELECT email, expires_at 
        FROM password_resets 
        WHERE token = ? AND expires_at > NOW()
    ");
    $stmt->execute([$token]);
    $resetRequest = $stmt->fetch();
    
    if ($resetRequest) {
        json_response(true, 'Token is valid', ['email' => $resetRequest['email']]);
    } else {
        json_response(false, 'Invalid or expired password reset link.');
    }
    
} catch (PDOException $e) {
    error_log("Token Validation Error: " . $e->getMessage());
    json_response(false, 'An error occurred.');
}
?>
