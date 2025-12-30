<?php
/**
 * Get Election Status Endpoint (Voter/Public)
 * Allows voters to check if election is active
 */

header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Require login (accessible to both admin and voters)
require_login();

// Get election ID from query parameter
$election_id = intval($_GET['id'] ?? 0);

try {
    if ($election_id <= 0) {
        // Get active election
        $election = get_active_election($pdo);
        
        if ($election) {
            json_response(true, 'Election found', ['election' => $election]);
        } else {
            // Check if there are any elections at all (even if not active)
            // Ideally we just say no active election
             json_response(true, 'No active election', ['election' => ['status' => 'closed']]);
        }
    } else {
        // Check specific election
        $stmt = $pdo->prepare("SELECT * FROM elections WHERE id = ?");
        $stmt->execute([$election_id]);
        $election = $stmt->fetch();
        
        if ($election) {
            json_response(true, 'Election found', ['election' => $election]);
        } else {
             json_response(false, 'Election not found');
        }
    }
} catch (PDOException $e) {
    error_log("Get Election Status Error: " . $e->getMessage());
    json_response(false, 'Failed to check election status');
}
?>
