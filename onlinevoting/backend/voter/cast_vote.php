<?php
/**
 * Cast Vote Endpoint (Voter Only)
 * Allows voter to cast their vote
 */

header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Require voter access
require_voter();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method');
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Extract inputs
$candidate_id = intval($input['candidate_id'] ?? 0);
$election_id = intval($input['election_id'] ?? 0);
$user_id = get_user_id();

// Validation
if ($candidate_id <= 0) {
    json_response(false, 'Invalid candidate');
}

try {
    if ($election_id <= 0) {
        // Get active election
        $election = get_active_election($pdo);
        if (!$election) {
            json_response(false, 'No active election found');
        }
        $election_id = $election['id'];
    }
    
    // Verify election is active
    if (!is_election_active($pdo, $election_id)) {
        json_response(false, 'Election is not currently active');
    }
    
    // Check if user has already voted in this election
    if (has_user_voted($pdo, $user_id, $election_id)) {
        json_response(false, 'You have already voted in this election');
    }
    
    // Verify candidate exists and belongs to this election
    $stmt = $pdo->prepare("
        SELECT id, name 
        FROM candidates 
        WHERE id = ? AND election_id = ?
    ");
    $stmt->execute([$candidate_id, $election_id]);
    $candidate = $stmt->fetch();
    
    if (!$candidate) {
        json_response(false, 'Invalid candidate or candidate not in this election');
    }
    
    // Record the vote
    $stmt = $pdo->prepare("
        INSERT INTO votes (user_id, candidate_id, election_id) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$user_id, $candidate_id, $election_id]);
    
    json_response(true, "Vote cast successfully for {$candidate['name']}!", [
        'voted' => true,
        'candidate_name' => $candidate['name']
    ]);
    
} catch (PDOException $e) {
    // Check if it's a duplicate vote error
    if ($e->getCode() == 23000) { // Integrity constraint violation
        json_response(false, 'You have already voted in this election');
    }
    
    error_log("Cast Vote Error: " . $e->getMessage());
    json_response(false, 'Failed to cast vote. Please try again.');
}
?>
