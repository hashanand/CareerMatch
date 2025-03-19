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
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        user_type ENUM('graduate', 'student', 'employer') NOT NULL,
        account_status ENUM('active', 'locked', 'inactive') DEFAULT 'active',
        profile_picture VARCHAR(255) DEFAULT NULL,
        bio TEXT,
        location VARCHAR(100),
        phone VARCHAR(20),
        failed_login_attempts INT DEFAULT 0,
        last_login_attempt DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (email),
        INDEX (user_type)
    )";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating users table: " . $conn->error);
    }
    
    // Create user_skills table
    $sql = "CREATE TABLE IF NOT EXISTS user_skills (
        skill_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        skill_name VARCHAR(100) NOT NULL,
        proficiency_level ENUM('beginner', 'intermediate', 'advanced', 'expert') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_skill (user_id, skill_name),
        INDEX (skill_name)
    )";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating user_skills table: " . $conn->error);
    }
    
    // Create user_education table
    $sql = "CREATE TABLE IF NOT EXISTS user_education (
        education_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        institution_name VARCHAR(255) NOT NULL,
        degree VARCHAR(255) NOT NULL,
        field_of_study VARCHAR(255) NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE,
        grade VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        INDEX (field_of_study)
    )";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating user_education table: " . $conn->error);
    }
    
    // Create user_experience table
    $sql = "CREATE TABLE IF NOT EXISTS user_experience (
        experience_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        company_name VARCHAR(255) NOT NULL,
        position VARCHAR(255) NOT NULL,
        location VARCHAR(255),
        start_date DATE NOT NULL,
        end_date DATE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        INDEX (company_name)
    )";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating user_experience table: " . $conn->error);
    }
    
    // Create saved_jobs table
    $sql = "CREATE TABLE IF NOT EXISTS saved_jobs (
        saved_job_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        job_title VARCHAR(255) NOT NULL,
        company_name VARCHAR(255) NOT NULL,
        job_location VARCHAR(255),
        job_description TEXT,
        salary_range VARCHAR(100),
        job_url VARCHAR(512),
        application_status ENUM('saved', 'applied', 'interviewing', 'offered', 'rejected') DEFAULT 'saved',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        INDEX (job_title),
        INDEX (company_name)
    )";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating saved_jobs table: " . $conn->error);
    }
    
    // Create login_history table
    $sql = "CREATE TABLE IF NOT EXISTS login_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        status ENUM('success', 'failed') NOT NULL,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        INDEX (user_id)
    )";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating login_history table: " . $conn->error);
    }
    
    // Create remember_me_tokens table
    $sql = "CREATE TABLE IF NOT EXISTS remember_me_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(255) NOT NULL,
        expiry DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        INDEX (token),
        INDEX (user_id)
    )";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating remember_me_tokens table: " . $conn->error);
    }

    // Create default demo user if it doesn't exist
    $demoEmail = 'demouser@careermatch.com';
    $sql = "SELECT user_id FROM users WHERE email = ?";
    $demoUser = getRow($sql, [$demoEmail]);
    
    if (!$demoUser) {
        $demoPassword = 'Demo@123';
        $passwordHash = password_hash($demoPassword, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (full_name, email, password_hash, user_type, bio) 
                VALUES ('Demo User', ?, ?, 'graduate', 'This is a demo account for testing purposes.')";
        insertData($sql, [$demoEmail, $passwordHash]);
    }
    
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

// Helper functions for database operations
function getRow($sql, $params = []) {
    global $conn;
    $stmt = prepareAndExecute($sql, str_repeat('s', count($params)), $params);
    if ($stmt) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row;
    }
    return null;
}

function getRows($sql, $params = []) {
    global $conn;
    $stmt = prepareAndExecute($sql, str_repeat('s', count($params)), $params);
    if ($stmt) {
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
    return [];
}

function insertData($sql, $params = []) {
    global $conn;
    $stmt = prepareAndExecute($sql, str_repeat('s', count($params)), $params);
    if ($stmt) {
        $success = $stmt->affected_rows > 0;
        $insert_id = $stmt->insert_id;
        $stmt->close();
        return $success ? $insert_id : false;
    }
    return false;
}

function executeQuery($sql, $params = []) {
    global $conn;
    $stmt = prepareAndExecute($sql, str_repeat('s', count($params)), $params);
    if ($stmt) {
        $success = $stmt->affected_rows >= 0;
        $stmt->close();
        return $success;
    }
    return false;
}

function prepareAndExecute($sql, $types, $params) {
    global $conn;
    $stmt = $conn->prepare($sql);
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