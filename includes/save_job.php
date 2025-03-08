<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to save jobs']);
    exit;
}

// Check if it's a POST request with job data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job'])) {
    try {
        // Decode the JSON data
        $jobData = json_decode($_POST['job'], true);
        
        // Validate required fields
        if (!isset($jobData['title']) || !isset($jobData['company']) || !isset($jobData['url'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required job information']);
            exit;
        }

        $user_id = $_SESSION['user_id'];
        
        // Check if job is already saved
        $stmt = $conn->prepare("SELECT id FROM saved_jobs WHERE user_id = ? AND job_url = ?");
        $stmt->bind_param("is", $user_id, $jobData['url']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Job already saved']);
            exit;
        }
        
        // Prepare the insert statement
        $stmt = $conn->prepare("
            INSERT INTO saved_jobs (
                user_id, 
                job_title, 
                company, 
                location, 
                salary, 
                job_type, 
                description, 
                company_logo, 
                job_url, 
                posted_at, 
                skills_match
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        // Bind parameters with proper error checking
        if (!$stmt->bind_param("isssssssssd", 
            $user_id,
            $jobData['title'],
            $jobData['company'],
            $jobData['location'],
            $jobData['salary'],
            $jobData['job_type'],
            $jobData['description'],
            $jobData['company_logo'],
            $jobData['url'],
            $jobData['posted_at'],
            $jobData['skills_match']
        )) {
            throw new Exception("Error binding parameters: " . $stmt->error);
        }
        
        // Execute the statement
        if (!$stmt->execute()) {
            throw new Exception("Error executing statement: " . $stmt->error);
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Job saved successfully',
            'job_id' => $conn->insert_id
        ]);
        
    } catch (Exception $e) {
        error_log("Error saving job: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Error saving job: ' . $e->getMessage()
        ]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
} 