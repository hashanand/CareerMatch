<?php
// Only configure session if it hasn't started yet
if (session_status() === PHP_SESSION_NONE) {
    // Session configuration
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 1);
    
    // Start the session
    session_start();
}

// Session timeout configuration (30 minutes)
define('SESSION_TIMEOUT', 1800);

// Remember me token expiration (30 days)
define('REMEMBER_ME_EXPIRY', 60 * 60 * 24 * 30);

function checkSessionTimeout() {
    if (isset($_SESSION['last_activity'])) {
        $inactive_time = time() - $_SESSION['last_activity'];
        if ($inactive_time >= SESSION_TIMEOUT) {
            destroySession();
            header("Location: login.php?timeout=1");
            exit;
        }
    }
    $_SESSION['last_activity'] = time();
}

function destroySession() {
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me', '', time() - 3600, '/');
        // Remove remember me token from database
        require_once 'db_config.php';
        $sql = "DELETE FROM remember_me_tokens WHERE token = ?";
        executeQuery($sql, [$_COOKIE['remember_me']]);
    }
    session_destroy();
}

function generateRememberMeToken() {
    return bin2hex(random_bytes(32));
}

function setRememberMeToken($userId) {
    require_once 'db_config.php';
    
    $token = generateRememberMeToken();
    $expiry = date('Y-m-d H:i:s', time() + REMEMBER_ME_EXPIRY);
    
    // Remove any existing tokens for this user
    $sql = "DELETE FROM remember_me_tokens WHERE user_id = ?";
    executeQuery($sql, [$userId]);
    
    // Insert new token
    $sql = "INSERT INTO remember_me_tokens (user_id, token, expiry) VALUES (?, ?, ?)";
    if (insertData($sql, [$userId, $token, $expiry])) {
        setcookie('remember_me', $token, time() + REMEMBER_ME_EXPIRY, '/', '', true, true);
        return true;
    }
    return false;
}

function validateRememberMeToken() {
    if (isset($_COOKIE['remember_me'])) {
        require_once 'db_config.php';
        
        $sql = "SELECT t.user_id, u.full_name 
                FROM remember_me_tokens t 
                JOIN users u ON t.user_id = u.user_id 
                WHERE t.token = ? AND t.expiry > NOW()";
        
        $user = getRow($sql, [$_COOKIE['remember_me']]);
        
        if ($user) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['last_activity'] = time();
            
            // Generate new token for security
            setRememberMeToken($user['user_id']);
            return true;
        } else {
            // Remove invalid cookie
            setcookie('remember_me', '', time() - 3600, '/');
        }
    }
    return false;
}

// Check for session timeout on every page load
checkSessionTimeout();
?> 