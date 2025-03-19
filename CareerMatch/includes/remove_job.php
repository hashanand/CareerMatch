<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to remove jobs']);
    exit;
}

// Check if job_id is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job_id'])) {
    $job_id = (int)$_POST['job_id'];
    $user_id = $_SESSION['user_id'];
    
    try {
        // Delete the job
        $stmt = $conn->prepare("DELETE FROM saved_jobs WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $job_id, $user_id);
        
        if ($stmt->execute()) {
            // Check if we should redirect (for saved jobs page) or just return success (for unsaving)
            $source = $_POST['source'] ?? '';
            if ($source === 'saved') {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Job removed successfully',
                    'redirect' => true
                ]);
            } else {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Job unsaved successfully'
                ]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error removing job']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
} 