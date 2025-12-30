<?php
/**
 * Get Users Endpoint (Admin Only)
 * Returns list of all registered users
 */

header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    json_response(false, 'Unauthorized access');
}

try {
    // Check if user is admin
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || $user['role'] !== 'admin') {
        json_response(false, 'Access denied. Admin rights required.');
    }

    // Get all users (excluding sensitive data)
    $stmt = $pdo->query("
        SELECT id, name, email, role, created_at 
        FROM users 
        ORDER BY created_at DESC
    ");
    $users = $stmt->fetchAll();

    // Check if users have voted in active election
    foreach ($users as &$u) {
        $active_election = get_active_election($pdo);
        if ($active_election) {
            $u['has_voted'] = has_user_voted($pdo, $u['id'], $active_election['id']);
        } else {
            $u['has_voted'] = false;
        }
    }

    json_response(true, 'Users retrieved successfully', [
        'users' => $users
    ]);

} catch (PDOException $e) {
    error_log("Get Users Error: " . $e->getMessage());
    json_response(false, 'Failed to retrieve users');
}
?>
