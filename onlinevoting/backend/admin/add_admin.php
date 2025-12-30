<?php
/**
 * Add New Admin Endpoint (Dashboard)
 * Allows logged-in admins to create new admin accounts
 */

header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method');
}

// Check if user is logged in and is admin
require_admin();

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
    
    // Insert new admin user
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, role) 
        VALUES (?, ?, ?, 'admin')
    ");
    $stmt->execute([$name, $email, $hashed_password]);
    
    // Log the action
    $current_admin = get_user_id();
    error_log("Admin ID $current_admin created new admin: $email");
    
    json_response(true, 'New administrator added successfully!');
    
} catch (PDOException $e) {
    error_log("Add Admin Error: " . $e->getMessage());
    json_response(false, 'Failed to add administrator. Please try again.');
}
?>
