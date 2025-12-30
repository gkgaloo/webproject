<?php
/**
 * Test Database Connection and Photo Column
 * Run this to verify photo upload setup
 */

require_once 'backend/config/db.php';

try {
    // Check if photo column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM candidates LIKE 'photo'");
    $photoColumn = $stmt->fetch();
    
    if ($photoColumn) {
        echo "✅ Photo column exists in candidates table\n";
        echo "Column details:\n";
        print_r($photoColumn);
        echo "\n\n";
    } else {
        echo "❌ Photo column does NOT exist in candidates table\n";
        echo "Run the database migration: backend/photo_upload_migration.sql\n\n";
    }
    
    // Check current candidates
    $stmt = $pdo->query("SELECT id, name, image, photo FROM candidates LIMIT 5");
    $candidates = $stmt->fetchAll();
    
    echo "Current candidates:\n";
    foreach ($candidates as $candidate) {
        echo "ID: {$candidate['id']}, Name: {$candidate['name']}, Photo: " . ($candidate['photo'] ?? 'NULL') . "\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
