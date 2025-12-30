<?php
/**
 * Add Candidate Endpoint (Admin Only)
 * Creates new candidate for an election with photo upload support
 */

header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/upload.php';

// Require admin access
require_admin();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method');
}

try {
    // Check if this is a file upload request (multipart/form-data)
    $isFileUpload = isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE;
    
    if ($isFileUpload) {
        // Handle multipart form data
        $name = sanitize_input($_POST['name'] ?? '');
        $party = sanitize_input($_POST['party'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $image = sanitize_input($_POST['image'] ?? '👤');
        $election_id = intval($_POST['election_id'] ?? 0);
    } else {
        // Handle JSON data (backward compatibility)
        $input = json_decode(file_get_contents('php://input'), true);
        $name = sanitize_input($input['name'] ?? '');
        $party = sanitize_input($input['party'] ?? '');
        $description = sanitize_input($input['description'] ?? '');
        $image = sanitize_input($input['image'] ?? '👤');
        $election_id = intval($input['election_id'] ?? 0);
    }
    
    // Validation
    if (empty($name)) {
        json_response(false, 'Candidate name is required');
    }
    
    if (empty($party)) {
        json_response(false, 'Party name is required');
    }
    
    if ($election_id <= 0) {
        // Get active election
        $election = get_active_election($pdo);
        if (!$election) {
            json_response(false, 'No active election found');
        }
        $election_id = $election['id'];
    }
    
    // Verify election exists
    $stmt = $pdo->prepare("SELECT id FROM elections WHERE id = ?");
    $stmt->execute([$election_id]);
    if (!$stmt->fetch()) {
        json_response(false, 'Invalid election');
    }
    
    // Insert new candidate (without photo first to get ID)
    $stmt = $pdo->prepare("
        INSERT INTO candidates (name, party, description, image, election_id) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$name, $party, $description, $image, $election_id]);
    
    $candidate_id = $pdo->lastInsertId();
    $photoPath = null;
    
    // Handle photo upload if present
    if ($isFileUpload) {
        $uploadResult = upload_candidate_photo($_FILES['photo'], $candidate_id);
        
        if ($uploadResult['success']) {
            $photoPath = $uploadResult['path'];
            
            // Update candidate with photo path
            $stmt = $pdo->prepare("UPDATE candidates SET photo = ? WHERE id = ?");
            $stmt->execute([$photoPath, $candidate_id]);
        } else {
            // Photo upload failed, but candidate was created
            // Log the error but don't fail the request
            error_log("Photo upload failed for candidate {$candidate_id}: " . $uploadResult['error']);
        }
    }
    
    json_response(true, 'Candidate added successfully', [
        'candidate' => [
            'id' => $candidate_id,
            'name' => $name,
            'party' => $party,
            'description' => $description,
            'image' => $image,
            'photo' => $photoPath,
            'photo_url' => get_candidate_photo_url($photoPath)
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Add Candidate Error: " . $e->getMessage());
    json_response(false, 'Failed to add candidate. Please try again.');
}
?>
