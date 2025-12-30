<?php
/**
 * Forgot Password Endpoint
 * Handles password reset requests
 */

header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/email.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method');
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$email = sanitize_input($input['email'] ?? '');

// Validation
if (empty($email) || !validate_email($email)) {
    json_response(false, 'Please enter a valid email address');
}

try {
    // 1. Rate Limiting Check
    if (!check_reset_rate_limit($pdo, $email)) {
        json_response(false, 'Too many requests. Please try again in 15 minutes.');
    }

    // 2. Check if user exists (silently)
    // We check both users table (voters/admins) AND admins table (if separate)
    // For this system, admins are in 'users' or 'admins' depending on migration
    // We'll check 'users' table mainly as per original schema, but handle 'admins' if it exists
    
    $userExists = false;
    
    // Check users table
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $userExists = true;
    } 
    
    // Check admins table (if it exists)
    try {
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $userExists = true;
        }
    } catch (Exception $e) {
        // Admins table might not exist yet, ignore
    }

    // 3. Process Reset (only if user exists)
    $devLink = null;
    
    if ($userExists) {
        // Generate secure token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+' . TOKEN_EXPIRY_MINUTES . ' minutes'));
        
        // Store token
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expiry]);
        
        // Send email
        send_password_reset_email($email, $token);
        
        // In dev mode, return the link for testing
        if (defined('EMAIL_DEV_MODE') && EMAIL_DEV_MODE) {
            $devLink = get_reset_link($token);
        }
        
        // Cleanup old tokens occasionally
        if (rand(1, 100) === 1) {
            cleanup_expired_tokens($pdo);
        }
    } else {
        // Fake delay to prevent timing attacks
        usleep(rand(200000, 500000)); // 200-500ms
    }
    
    // 4. Return generic success message
    $response = [
        'success' => true,
        'message' => 'If that email is registered, you will receive a password reset link shortly.'
    ];
    
    if ($devLink) {
        $response['dev_link'] = $devLink; // Only for testing!
    }
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    error_log("Forgot Password Error: " . $e->getMessage());
    json_response(false, 'An error occurred. Please try again later.');
}
?>
