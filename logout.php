<?php
require_once 'includes/session_manager.php';

// Log the logout
if (isset($_SESSION['user_id'])) {
    require_once 'includes/db_config.php';
    
    // Insert logout record
    $sql = "INSERT INTO login_history (user_id, status, ip_address, user_agent) VALUES (?, 'success', ?, ?)";
    insertData($sql, [
        $_SESSION['user_id'],
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ]);
}

// Destroy the session and cookies
destroySession();

// Redirect to login page with success message
header("Location: login.php?logout=success");
exit;