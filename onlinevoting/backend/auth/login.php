<?php
/**
 * User Login Endpoint
 * Authenticates users and creates session
 */

header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method');
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Extract and sanitize inputs
$email = sanitize_input($input['email'] ?? '');
$password = $input['password'] ?? '';

// Validation
if (empty($email) || empty($password)) {
    json_response(false, 'Email and password are required');
}

try {
    // Find user by email
    $stmt = $pdo->prepare("
        SELECT id, name, email, password, role 
        FROM users 
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Verify user exists and password is correct
    if (!$user || !verify_password($password, $user['password'])) {
        json_response(false, 'Invalid email or password');
    }
    
    // Set session data
    set_user_session($user);
    
    // Return success with user data (without password)
    json_response(true, 'Login successful!', [
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Login Error: " . $e->getMessage());
    json_response(false, 'Login failed. Please try again.');
}
?>
