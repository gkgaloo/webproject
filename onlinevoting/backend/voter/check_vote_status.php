<?php
/**
 * Check Vote Status Endpoint
 * Returns whether user has voted in an election
 */

header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Require login
require_login();

$user_id = get_user_id();
$election_id = intval($_GET['election_id'] ?? 0);

try {
    if ($election_id <= 0) {
        // Get active election
        $election = get_active_election($pdo);
        if (!$election) {
            json_response(true, 'No active election', [
                'has_voted' => false,
                'election_active' => false
            ]);
        }
        $election_id = $election['id'];
    }
    
    // Check if user has voted
    $has_voted = has_user_voted($pdo, $user_id, $election_id);
    
    // Get election status
    $election_active = is_election_active($pdo, $election_id);
    
    json_response(true, 'Vote status retrieved', [
        'has_voted' => $has_voted,
        'election_active' => $election_active,
        'election_id' => $election_id
    ]);
    
} catch (PDOException $e) {
    error_log("Check Vote Status Error: " . $e->getMessage());
    json_response(false, 'Failed to check vote status');
}
?>
