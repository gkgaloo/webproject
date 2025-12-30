<?php
/**
 * File Upload Utility Functions
 * Handles secure file uploads for candidate photos
 */

/**
 * Validate uploaded image file
 * @param array $file - $_FILES array element
 * @return array - ['valid' => bool, 'error' => string]
 */
function validate_image_upload($file) {
    // Check if file was uploaded
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['valid' => false, 'error' => 'No file uploaded'];
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'File upload failed'];
    }
    
    // Validate file size (max 2MB)
    $maxSize = 2 * 1024 * 1024; // 2MB in bytes
    if ($file['size'] > $maxSize) {
        return ['valid' => false, 'error' => 'File size must not exceed 2MB'];
    }
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['valid' => false, 'error' => 'Only JPG, JPEG, and PNG images are allowed'];
    }
    
    // Validate file extension
    $allowedExtensions = ['jpg', 'jpeg', 'png'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, $allowedExtensions)) {
        return ['valid' => false, 'error' => 'Invalid file extension'];
    }
    
    // Validate image dimensions (optional but recommended)
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return ['valid' => false, 'error' => 'Invalid image file'];
    }
    
    return ['valid' => true, 'error' => ''];
}

/**
 * Upload candidate photo
 * @param array $file - $_FILES array element
 * @param int $candidateId - ID of the candidate
 * @return array - ['success' => bool, 'path' => string, 'error' => string]
 */
function upload_candidate_photo($file, $candidateId = null) {
    // Validate file
    $validation = validate_image_upload($file);
    if (!$validation['valid']) {
        return ['success' => false, 'path' => '', 'error' => $validation['error']];
    }
    
    // Create upload directory if it doesn't exist
    $uploadDir = __DIR__ . '/../../uploads/candidates/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return ['success' => false, 'path' => '', 'error' => 'Failed to create upload directory'];
        }
    }
    
    // Generate unique filename
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $timestamp = time();
    $randomString = bin2hex(random_bytes(8));
    
    if ($candidateId) {
        $filename = "candidate_{$candidateId}_{$timestamp}.{$fileExtension}";
    } else {
        $filename = "candidate_temp_{$randomString}_{$timestamp}.{$fileExtension}";
    }
    
    $uploadPath = $uploadDir . $filename;
    $relativePath = "uploads/candidates/" . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => false, 'path' => '', 'error' => 'Failed to save file'];
    }
    
    // Set file permissions
    chmod($uploadPath, 0644);
    
    return ['success' => true, 'path' => $relativePath, 'error' => ''];
}

/**
 * Delete candidate photo
 * @param string $photoPath - Relative path to photo
 * @return bool
 */
function delete_candidate_photo($photoPath) {
    if (empty($photoPath)) {
        return true;
    }
    
    $fullPath = __DIR__ . '/../../' . $photoPath;
    
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    
    return true;
}

/**
 * Get candidate photo URL
 * @param string $photoPath - Relative path from database
 * @return string - Full URL or default placeholder
 */
function get_candidate_photo_url($photoPath) {
    if (empty($photoPath)) {
        return '/onlinevoting/uploads/candidates/default_candidate.png';
    }
    
    $fullPath = __DIR__ . '/../../' . $photoPath;
    if (!file_exists($fullPath)) {
        return '/onlinevoting/uploads/candidates/default_candidate.png';
    }
    
    return '/onlinevoting/' . $photoPath;
}

/**
 * Resize and optimize image
 * @param string $sourcePath - Source file path
 * @param string $destPath - Destination file path
 * @param int $maxWidth - Maximum width
 * @param int $maxHeight - Maximum height
 * @return bool
 */
function resize_image($sourcePath, $destPath, $maxWidth = 800, $maxHeight = 800) {
    $imageInfo = getimagesize($sourcePath);
    if ($imageInfo === false) {
        return false;
    }
    
    list($width, $height, $type) = $imageInfo;
    
    // Calculate new dimensions
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    if ($ratio >= 1) {
        // Image is smaller than max dimensions, no resize needed
        return copy($sourcePath, $destPath);
    }
    
    $newWidth = (int)($width * $ratio);
    $newHeight = (int)($height * $ratio);
    
    // Create image resource based on type
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($sourcePath);
            break;
        default:
            return false;
    }
    
    if ($source === false) {
        return false;
    }
    
    // Create new image
    $destination = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserve transparency for PNG
    if ($type === IMAGETYPE_PNG) {
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
    }
    
    // Resize
    imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Save
    $result = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($destination, $destPath, 85);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($destination, $destPath, 8);
            break;
    }
    
    // Clean up
    imagedestroy($source);
    imagedestroy($destination);
    
    return $result;
}
?>
