<?php
/**
 * Check Authentication Status
 * Returns current user data if logged in
 */

header('Content-Type: application/json');
require_once '../includes/session.php';
require_once '../includes/functions.php';

if (is_logged_in()) {
    json_response(true, 'Authenticated', [
        'user' => get_user_data()
    ]);
} else {
    json_response(false, 'Not authenticated');
}
?>
