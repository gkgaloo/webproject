<?php
// Reset Admin Password
require_once 'backend/config/db.php';

if (isset($pdo)) {
    $email = 'admin@voting.com';
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if admin exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Update existing admin
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hash, $user['id']]);
        echo "Admin password has been reset successfully.\n";
    } else {
        // Create admin if missing
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Administrator', $email, $hash, 'admin']);
        echo "Admin user was missing. Created new admin account.\n";
    }
    
    echo "Login: $email\n";
    echo "Password: $password\n";
    
} else {
    echo "Database connection failed.\n";
}
?>
