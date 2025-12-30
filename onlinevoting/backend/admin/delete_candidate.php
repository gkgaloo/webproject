<?php
/**
 * Delete Candidate Endpoint (Admin Only)
 * Removes candidate from election
 */

header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Require admin access
require_admin();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method');
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Extract candidate ID
$candidate_id = intval($input['id'] ?? 0);

// Validation
if ($candidate_id <= 0) {
    json_response(false, 'Invalid candidate ID');
}

try {
    // Check if candidate exists
    $stmt = $pdo->prepare("SELECT id FROM candidates WHERE id = ?");
    $stmt->execute([$candidate_id]);
    if (!$stmt->fetch()) {
        json_response(false, 'Candidate not found');
    }
    
    // Delete candidate (votes will be cascade deleted due to foreign key)
    $stmt = $pdo->prepare("DELETE FROM candidates WHERE id = ?");
    $stmt->execute([$candidate_id]);
    
    json_response(true, 'Candidate removed successfully');
    
} catch (PDOException $e) {
    error_log("Delete Candidate Error: " . $e->getMessage());
    json_response(false, 'Failed to remove candidate. Please try again.');
}
?>
