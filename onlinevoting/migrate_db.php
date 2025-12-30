<?php
/**
 * Database Migration Script
 * Removes voter_id column from users table
 */

require_once 'backend/config/db.php';

try {
    echo "Starting database migration...\n";
    
    // Check if voter_id column exists
    $stmt = $pdo->prepare("
        SELECT count(*) 
        FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = 'voting_system' 
        AND TABLE_NAME = 'users' 
        AND COLUMN_NAME = 'voter_id'
    ");
    $stmt->execute();
    
    if ($stmt->fetchColumn() > 0) {
        echo "Found voter_id column. Removing...\n";
        
        // 1. Drop index
        try {
            $pdo->exec("ALTER TABLE users DROP INDEX idx_voter_id");
            echo "Index idx_voter_id dropped.\n";
        } catch (PDOException $e) {
            echo "Index might not exist or already dropped: " . $e->getMessage() . "\n";
        }
        
        // 2. Drop column
        $pdo->exec("ALTER TABLE users DROP COLUMN voter_id");
        echo "Column voter_id dropped successfully.\n";
        
    } else {
        echo "Column voter_id does not exist. No changes needed.\n";
    }
    
    echo "Migration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Migration Failed: " . $e->getMessage() . "\n";
}
?>
