<?php
/**
 * Manage Election Endpoint (Admin Only)
 * Create and update elections
 */

header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Require admin access
require_admin();

$method = $_SERVER['REQUEST_METHOD'];

// GET - Get all elections or specific election
if ($method === 'GET') {
    try {
        $election_id = intval($_GET['id'] ?? 0);
        
        if ($election_id > 0) {
            // Get specific election
            $stmt = $pdo->prepare("SELECT * FROM elections WHERE id = ?");
            $stmt->execute([$election_id]);
            $election = $stmt->fetch();
            
            if (!$election) {
                json_response(false, 'Election not found');
            }
            
            json_response(true, 'Election retrieved', ['election' => $election]);
        } else {
            // Get all elections
            $stmt = $pdo->query("SELECT * FROM elections ORDER BY created_at DESC");
            $elections = $stmt->fetchAll();
            
            json_response(true, 'Elections retrieved', ['elections' => $elections]);
        }
    } catch (PDOException $e) {
        error_log("Get Election Error: " . $e->getMessage());
        json_response(false, 'Failed to retrieve elections');
    }
}

// POST - Create or update election
elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $id = intval($input['id'] ?? 0);
    $title = sanitize_input($input['title'] ?? '');
    $description = sanitize_input($input['description'] ?? '');
    $status = sanitize_input($input['status'] ?? 'pending');
    $start_date = sanitize_input($input['start_date'] ?? '');
    $end_date = sanitize_input($input['end_date'] ?? '');
    
    // Validation
    if (empty($title)) {
        json_response(false, 'Election title is required');
    }
    
    if (!in_array($status, ['pending', 'active', 'closed'])) {
        json_response(false, 'Invalid status');
    }
    
    try {
        if ($id > 0) {
            // Update existing election
            $stmt = $pdo->prepare("
                UPDATE elections 
                SET title = ?, description = ?, status = ?, start_date = ?, end_date = ?
                WHERE id = ?
            ");
            $stmt->execute([$title, $description, $status, $start_date, $end_date, $id]);
            
            json_response(true, 'Election updated successfully');
        } else {
            // Create new election
            $stmt = $pdo->prepare("
                INSERT INTO elections (title, description, status, start_date, end_date) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $description, $status, $start_date, $end_date]);
            
            $election_id = $pdo->lastInsertId();
            
            json_response(true, 'Election created successfully', [
                'election_id' => $election_id
            ]);
        }
    } catch (PDOException $e) {
        error_log("Manage Election Error: " . $e->getMessage());
        json_response(false, 'Failed to manage election');
    }
}

// DELETE - Delete election
elseif ($method === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);
    
    if ($id <= 0) {
        json_response(false, 'Invalid election ID');
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM elections WHERE id = ?");
        $stmt->execute([$id]);
        
        json_response(true, 'Election deleted successfully');
    } catch (PDOException $e) {
        error_log("Delete Election Error: " . $e->getMessage());
        json_response(false, 'Failed to delete election');
    }
}

else {
    json_response(false, 'Invalid request method');
}
?>
