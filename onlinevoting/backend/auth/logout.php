<?php
/**
 * User Logout Endpoint
 * Destroys user session
 */

header('Content-Type: application/json');
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Destroy session
destroy_session();

json_response(true, 'Logged out successfully');
?>
