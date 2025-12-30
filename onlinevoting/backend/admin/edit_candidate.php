<?php
/**
 * Edit Candidate Endpoint (Admin Only)
 * Updates candidate information with photo upload support
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
    // Check if this is a file upload request
    $isFileUpload = isset($_FILES['photo']);
    
    if ($isFileUpload) {
        // Handle multipart form data
        $candidate_id = intval($_POST['id'] ?? 0);
        $name = sanitize_input($_POST['name'] ?? '');
        $party = sanitize_input($_POST['party'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $image = sanitize_input($_POST['image'] ?? '👤');
    } else {
        // Handle JSON data (backward compatibility)
        $input = json_decode(file_get_contents('php://input'), true);
        $candidate_id = intval($input['id'] ?? 0);
        $name = sanitize_input($input['name'] ?? '');
        $party = sanitize_input($input['party'] ?? '');
        $description = sanitize_input($input['description'] ?? '');
        $image = sanitize_input($input['image'] ?? '👤');
    }
    
    // Validation
    if ($candidate_id <= 0) {
        json_response(false, 'Invalid candidate ID');
    }
    
    if (empty($name)) {
        json_response(false, 'Candidate name is required');
    }
    
    if (empty($party)) {
        json_response(false, 'Party name is required');
    }
    
    // Check if candidate exists and get current photo
    $stmt = $pdo->prepare("SELECT id, photo FROM candidates WHERE id = ?");
    $stmt->execute([$candidate_id]);
    $candidate = $stmt->fetch();
    
    if (!$candidate) {
        json_response(false, 'Candidate not found');
    }
    
    $oldPhoto = $candidate['photo'];
    $newPhoto = $oldPhoto;
    
    // Handle photo upload if present
    if ($isFileUpload && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadResult = upload_candidate_photo($_FILES['photo'], $candidate_id);
        
        if ($uploadResult['success']) {
            $newPhoto = $uploadResult['path'];
            
            // Delete old photo if it exists and is different
            if ($oldPhoto && $oldPhoto !== $newPhoto) {
                delete_candidate_photo($oldPhoto);
            }
        } else {
            json_response(false, 'Photo upload failed: ' . $uploadResult['error']);
        }
    }
    
    // Update candidate
    $stmt = $pdo->prepare("
        UPDATE candidates 
        SET name = ?, party = ?, description = ?, image = ?, photo = ? 
        WHERE id = ?
    ");
    $stmt->execute([$name, $party, $description, $image, $newPhoto, $candidate_id]);
    
    json_response(true, 'Candidate updated successfully', [
        'candidate' => [
            'id' => $candidate_id,
            'name' => $name,
            'party' => $party,
            'description' => $description,
            'image' => $image,
            'photo' => $newPhoto,
            'photo_url' => get_candidate_photo_url($newPhoto)
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Edit Candidate Error: " . $e->getMessage());
    json_response(false, 'Failed to update candidate. Please try again.');
}
?>
