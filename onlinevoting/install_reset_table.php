<?php
// Setup script for password resets
require_once 'backend/config/db.php';

try {
    $sql = file_get_contents('backend/password_reset_schema.sql');
    $pdo->exec($sql);
    echo "✅ Password reset table created successfully.";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
