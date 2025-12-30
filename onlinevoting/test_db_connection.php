<?php
// Simple DB Connection Test
require_once 'backend/config/db.php';

if (isset($pdo)) {
    echo "Database connection successful!\n";
    
    // Try a simple query
    try {
        $stmt = $pdo->query("SELECT count(*) as count FROM users");
        $row = $stmt->fetch();
        echo "Users in table: " . $row['count'] . "\n";
    } catch (PDOException $e) {
        echo "Query failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "Database connection failed (PDO not set).\n";
}
?>
