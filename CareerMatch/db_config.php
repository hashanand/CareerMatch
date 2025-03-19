<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');     // Change this to your MySQL username
define('DB_PASS', '');         // Change this to your MySQL password
define('DB_NAME', 'career_match');

// Create database connection
function getDBConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'")
        );
        
        // Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        error_log("Connection failed: " . $e->getMessage());
        return null;
    }
}

// Helper function to execute queries
function executeQuery($sql, $params = []) {
    try {
        $conn = getDBConnection();
        if ($conn) {
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        }
        return false;
    } catch(PDOException $e) {
        error_log("Query failed: " . $e->getMessage());
        return false;
    }
}

// Helper function to get a single row
function getRow($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
}

// Helper function to get multiple rows
function getRows($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : false;
}

// Helper function to insert data and return last insert id
function insertData($sql, $params = []) {
    try {
        $conn = getDBConnection();
        if ($conn) {
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $conn->lastInsertId();
        }
        return false;
    } catch(PDOException $e) {
        error_log("Insert failed: " . $e->getMessage());
        return false;
    }
}
?> 