<?php
/**
 * Admin Registration Endpoint
 * Handles new administrator registration with validation and access code verification
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
$name = sanitize_input($input['name'] ?? '');
$email = sanitize_input($input['email'] ?? '');
$password = $input['password'] ?? '';
$accessCode = sanitize_input($input['accessCode'] ?? '');

// Define admin access code (In production, store this in environment variables or database)
// For now, using a hardcoded value - CHANGE THIS!
define('ADMIN_ACCESS_CODE', 'ADMIN2024SECURE');

// Validation
$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
}

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!validate_email($email)) {
    $errors[] = 'Invalid email address';
}

if (empty($password)) {
    $errors[] = 'Password is required';
} else {
    $password_validation = validate_password($password);
    if (!$password_validation['valid']) {
        $errors = array_merge($errors, $password_validation['errors']);
    }
}

if (empty($accessCode)) {
    $errors[] = 'Admin access code is required';
} elseif ($accessCode !== ADMIN_ACCESS_CODE) {
    $errors[] = 'Invalid admin access code';
}

if (!empty($errors)) {
    json_response(false, implode('. ', $errors));
}

try {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        json_response(false, 'Email already registered');
    }
    
    // Hash password
    $hashed_password = hash_password($password);
    
    // Insert new admin user
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, role) 
        VALUES (?, ?, ?, 'admin')
    ");
    $stmt->execute([$name, $email, $hashed_password]);
    
    // Log the admin creation
    error_log("New admin registered: $email");
    
    json_response(true, 'Admin account created successfully! You can now login.');
    
} catch (PDOException $e) {
    error_log("Admin Registration Error: " . $e->getMessage());
    json_response(false, 'Registration failed. Please try again.');
}
?>
