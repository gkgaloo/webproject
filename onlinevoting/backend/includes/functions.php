<?php
/**
 * Utility Functions
 * Common helper functions for validation and sanitization
 */

/**
 * Sanitize input string
 * @param string $data
 * @return string
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email address
 * @param string $email
 * @return bool
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate name - letters and spaces only
 * @param string $name
 * @return array
 */
function validate_name($name) {
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Name is required';
    } elseif (!preg_match('/^[A-Za-z\s]+$/', $name)) {
        $errors[] = 'Name must contain only letters and spaces';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Validate password strength
 * @param string $password
 * @return array
 */
function validate_password($password) {
    $errors = [];
    
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Hash password
 * @param string $password
 * @return string
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 * @param string $password
 * @param string $hash
 * @return bool
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate JSON response
 * @param bool $success
 * @param string $message
 * @param array $data
 */
function json_response($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit;
}

/**
 * Check if election is active
 * @param PDO $pdo
 * @param int $election_id
 * @return bool
 */
function is_election_active($pdo, $election_id) {
    $stmt = $pdo->prepare("
        SELECT status, start_date, end_date 
        FROM elections 
        WHERE id = ?
    ");
    $stmt->execute([$election_id]);
    $election = $stmt->fetch();
    
    if (!$election) {
        return false;
    }
    
    $now = date('Y-m-d H:i:s');
    return $election['status'] === 'active' && 
           $election['start_date'] <= $now && 
           $election['end_date'] >= $now;
}

/**
 * Check if user has voted in election
 * @param PDO $pdo
 * @param int $user_id
 * @param int $election_id
 * @return bool
 */
function has_user_voted($pdo, $user_id, $election_id) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM votes 
        WHERE user_id = ? AND election_id = ?
    ");
    $stmt->execute([$user_id, $election_id]);
    $result = $stmt->fetch();
    
    return $result['count'] > 0;
}

/**
 * Get active election
 * @param PDO $pdo
 * @return array|null
 */
function get_active_election($pdo) {
    $stmt = $pdo->query("
        SELECT * FROM elections 
        WHERE status = 'active' 
        AND start_date <= NOW() 
        AND end_date >= NOW() 
        ORDER BY id DESC 
        LIMIT 1
    ");
    return $stmt->fetch();
}

/**
 * Validate CSRF token (optional but recommended)
 * @param string $token
 * @return bool
 */
function validate_csrf_token($token) {
    return $_SESSION['csrf_token'];
}

/**
 * Check if email has exceeded password reset rate limit
 * @param PDO $pdo
 * @param string $email
 * @return bool - true if allowed, false if limit exceeded
 */
function check_reset_rate_limit($pdo, $email) {
    // Max 3 requests per 15 minutes
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM password_resets 
        WHERE email = ? 
        AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
    ");
    $stmt->execute([$email]);
    $result = $stmt->fetch();
    
    return $result['count'] < 3;
}

/**
 * Cleanup expired password reset tokens
 * @param PDO $pdo
 */
function cleanup_expired_tokens($pdo) {
    // Remove tokens older than 24 hours (safer margin than just expiry)
    $stmt = $pdo->query("DELETE FROM password_resets WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)");
}
?>
