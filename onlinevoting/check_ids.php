<?php
// Check for ID Mismatch
require_once 'backend/config/db.php';
require_once 'backend/includes/functions.php';

if (isset($pdo)) {
    echo "--- Active Election ---\n";
    $active = get_active_election($pdo);
    if ($active) {
        echo "Active Election ID: " . $active['id'] . "\n";
        echo "Title: " . $active['title'] . "\n";
        
        echo "\n--- Candidates for ID " . $active['id'] . " ---\n";
        $stmt = $pdo->prepare("SELECT * FROM candidates WHERE election_id = ?");
        $stmt->execute([$active['id']]);
        $candidates = $stmt->fetchAll();
        echo "Count: " . count($candidates) . "\n";
        foreach ($candidates as $c) {
            echo " - " . $c['name'] . "\n";
        }
    } else {
        echo "No Active Election Found.\n";
    }

    echo "\n--- All Elections ---\n";
    $stmt = $pdo->query("SELECT id, title, status FROM elections");
    while ($row = $stmt->fetch()) {
        echo "ID: " . $row['id'] . ", Status: " . $row['status'] . "\n";
    }

    echo "\n--- All Candidates ---\n";
    $stmt = $pdo->query("SELECT id, name, election_id FROM candidates");
    while ($row = $stmt->fetch()) {
        echo "Name: " . $row['name'] . ", Linked to Election ID: " . $row['election_id'] . "\n";
    }

}
?>
