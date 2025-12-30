<?php
/**
 * User Registration Endpoint
 * Handles new voter registration with validation
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

// Validation
$errors = [];

// Validate Full Name
$name_validation = validate_name($name);
if (!$name_validation['valid']) {
    $errors = array_merge($errors, $name_validation['errors']);
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
    
    // Insert new user
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, role) 
        VALUES (?, ?, ?, 'voter')
    ");
    $stmt->execute([$name, $email, $hashed_password]);
    
    json_response(true, 'Registration successful! You can now login.');
    
} catch (PDOException $e) {
    error_log("Registration Error: " . $e->getMessage());
    json_response(false, 'Registration failed. Please try again.');
}
?>
