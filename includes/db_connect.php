<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Default XAMPP username
define('DB_PASS', '');            // Default XAMPP password
define('DB_NAME', 'career_match');
define('DB_CHARSET', 'utf8mb4');

// Create logs directory if it doesn't exist
$logsDir = __DIR__ . '/../logs';
if (!file_exists($logsDir)) {
    mkdir($logsDir, 0777, true);
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', $logsDir . '/db_errors.log');

try {
    // Create connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    if (!$conn->query($sql)) {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select the database
    if (!$conn->select_db(DB_NAME)) {
        throw new Exception("Error selecting database: " . $conn->error);
    }
    
    // Set charset
    if (!$conn->set_charset(DB_CHARSET)) {
        throw new Exception("Error setting charset: " . $conn->error);
    }
    
    // Create saved_jobs table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS saved_jobs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        job_title VARCHAR(255) NOT NULL,
        company VARCHAR(255) NOT NULL,
        location VARCHAR(255),
        salary VARCHAR(255),
        job_type VARCHAR(100),
        description TEXT,
        company_logo VARCHAR(255),
        job_url VARCHAR(1024) NOT NULL,
        posted_at VARCHAR(100),
        skills_match DECIMAL(5,2),
        saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id)
    )";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating table: " . $conn->error);
    }
    
    // Set session wait_timeout and interactive_timeout
    $conn->query("SET session wait_timeout=600");
    $conn->query("SET session interactive_timeout=600");
    
} catch (Exception $e) {
    // Log the detailed error
    error_log("Database connection error: " . $e->getMessage());
    
    // If this is a critical page that requires database access
    if (strpos($_SERVER['PHP_SELF'], 'api/') !== false) {
        // For API requests, return JSON error
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Database connection failed']);
    } else {
        // For regular pages, redirect to an error page
        header("Location: /error.php?type=db");
    }
    exit;
}

/**
 * Helper function to escape strings for database queries
 * @param string $value The string to escape
 * @return string The escaped string
 */
function escapeString($value) {
    global $conn;
    return $conn->real_escape_string($value);
}

/**
 * Helper function to execute prepared statements
 * @param string $query The SQL query with placeholders
 * @param string $types The types of parameters (i: integer, s: string, d: double, b: blob)
 * @param array $params The parameters to bind
 * @return mysqli_stmt|false Returns the statement object or false on failure
 */
function prepareAndExecute($query, $types, $params) {
    global $conn;
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        return false;
    }
    
    return $stmt;
}

// Register shutdown function to close connection
register_shutdown_function(function() {
    global $conn;
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}); 