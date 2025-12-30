<?php
/**
 * Get Candidates Endpoint
 * Returns all candidates for an election
 */

header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/upload.php';

$election_id = intval($_GET['election_id'] ?? 0);

try {
    if ($election_id <= 0) {
        // Get active election
        $election = get_active_election($pdo);
        if (!$election) {
            json_response(true, 'No active election', [
                'candidates' => []
            ]);
        }
        $election_id = $election['id'];
    }
    
    // Get all candidates for this election with photo
    $stmt = $pdo->prepare("
        SELECT id, name, party, description, image, photo 
        FROM candidates 
        WHERE election_id = ? 
        ORDER BY name ASC
    ");
    $stmt->execute([$election_id]);
    $candidates = $stmt->fetchAll();
    
    // Add photo URLs to each candidate
    foreach ($candidates as &$candidate) {
        $candidate['photo_url'] = get_candidate_photo_url($candidate['photo']);
    }
    

    // Log success for debugging
    error_log("Candidates found: " . count($candidates) . " for election " . $election_id);

    json_response(true, 'Candidates retrieved successfully', [
        'candidates' => $candidates,
        'election_id' => $election_id
    ]);
    
} catch (PDOException $e) {
    error_log("Get Candidates Error: " . $e->getMessage());
    json_response(false, 'Failed to retrieve candidates');
}

?>
