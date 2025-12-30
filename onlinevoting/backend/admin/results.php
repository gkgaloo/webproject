<?php
/**
 * Get Results Endpoint (Admin)
 * Returns detailed voting results
 */

header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/upload.php';

// Require login (accessible to both admin and voters)
require_login();

// Get election ID from query parameter
$election_id = intval($_GET['election_id'] ?? 0);

try {
    if ($election_id <= 0) {
        // Get active election
        $election = get_active_election($pdo);
        if (!$election) {
            json_response(false, 'No active election found');
        }
        $election_id = $election['id'];
    }
    
    // Get candidates with vote counts
    $stmt = $pdo->prepare("
        SELECT 
            c.id,
            c.name,
            c.party,
            c.description,
            c.image,
            c.photo,
            COUNT(v.id) as votes
        FROM candidates c
        LEFT JOIN votes v ON c.id = v.candidate_id AND v.election_id = ?
        WHERE c.election_id = ?
        GROUP BY c.id
        ORDER BY votes DESC, c.name ASC
    ");
    $stmt->execute([$election_id, $election_id]);
    $candidates = $stmt->fetchAll();
    
    // Calculate total votes
    $total_votes = array_sum(array_column($candidates, 'votes'));
    
    // Add percentage and photo URL to each candidate
    $results = array_map(function($candidate) use ($total_votes) {
        $candidate['percentage'] = $total_votes > 0 
            ? round(($candidate['votes'] / $total_votes) * 100, 1) 
            : 0;
        $candidate['votes'] = (int)$candidate['votes'];
        $candidate['photo_url'] = get_candidate_photo_url($candidate['photo']);
        return $candidate;
    }, $candidates);
    
    // Get voting statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT u.id) as total_voters,
            COUNT(DISTINCT v.user_id) as voted_count
        FROM users u
        LEFT JOIN votes v ON u.id = v.user_id AND v.election_id = ?
        WHERE u.role = 'voter'
    ");
    $stmt->execute([$election_id]);
    $stats = $stmt->fetch();
    
    $turnout = $stats['total_voters'] > 0 
        ? round(($stats['voted_count'] / $stats['total_voters']) * 100, 1) 
        : 0;
    
    json_response(true, 'Results retrieved successfully', [
        'results' => $results,
        'stats' => [
            'total_voters' => (int)$stats['total_voters'],
            'voted_count' => (int)$stats['voted_count'],
            'pending_count' => (int)($stats['total_voters'] - $stats['voted_count']),
            'total_candidates' => count($candidates),
            'turnout_percentage' => $turnout
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Results Error: " . $e->getMessage());
    json_response(false, 'Failed to retrieve results. Please try again.');
}
?>
