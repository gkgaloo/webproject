<?php
/**
 * Session Management
 * Handles user authentication and session security
 */

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    session_start();
}

/**
 * Check if user is logged in
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

/**
 * Check if user is admin
 * @return bool
 */
function is_admin() {
    return is_logged_in() && $_SESSION['user_role'] === 'admin';
}

/**
 * Check if user is voter
 * @return bool
 */
function is_voter() {
    return is_logged_in() && $_SESSION['user_role'] === 'voter';
}

/**
 * Require login - redirect if not logged in
 */
function require_login() {
    if (!is_logged_in()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized. Please login.'
        ]);
        exit;
    }
}

/**
 * Require admin role
 */
function require_admin() {
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Forbidden. Admin access required.'
        ]);
        exit;
    }
}

/**
 * Require voter role
 */
function require_voter() {
    require_login();
    if (!is_voter()) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Forbidden. Voter access required.'
        ]);
        exit;
    }
}

/**
 * Get current user ID
 * @return int|null
 */
function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data
 * @return array|null
 */
function get_user_data() {
    if (!is_logged_in()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role']
    ];
}

/**
 * Set user session data
 */
function set_user_session($user_data) {
    $_SESSION['user_id'] = $user_data['id'];
    $_SESSION['user_name'] = $user_data['name'];
    $_SESSION['user_email'] = $user_data['email'];
    $_SESSION['user_role'] = $user_data['role'];
}

/**
 * Destroy user session
 */
function destroy_session() {
    $_SESSION = [];
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
}
?>
