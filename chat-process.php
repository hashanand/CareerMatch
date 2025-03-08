<?php
session_start();
header('Content-Type: application/json');
require_once 'config.php'; // Will create this file for API key
require_once 'db_config.php';

// Initialize career guidance context
$system_prompt = "You are a career guidance counselor helping fresh graduates. Provide specific, actionable advice about career paths, skill development, resume writing, and interview preparation. Be encouraging but realistic.";

function callOpenAI($message, $system_prompt) {
    $api_key = OPENAI_API_KEY; // We'll store this in config.php
    
    $url = 'https://api.openai.com/v1/chat/completions';
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ];
    
    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'system', 'content' => $system_prompt],
            ['role' => 'user', 'content' => $message]
        ],
        'max_tokens' => 300,
        'temperature' => 0.7
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $result = json_decode($response, true);
        return $result['choices'][0]['message']['content'];
    } else {
        return "I apologize, but I'm having trouble connecting to the service right now. Please try again later.";
    }
}

// Store chat message in database
function storeChatMessage($userId, $message, $response) {
    $sql = "INSERT INTO chat_history (user_id, message, response) VALUES (?, ?, ?)";
    return insertData($sql, [$userId, $message, $response]);
}

// Store resume upload in database
function storeResumeUpload($userId, $fileName, $filePath) {
    // First, set all existing resumes to not current
    executeQuery(
        "UPDATE resume_uploads SET is_current = 0 WHERE user_id = ?",
        [$userId]
    );
    
    // Then insert the new resume
    $sql = "INSERT INTO resume_uploads (user_id, file_name, file_path, is_current) VALUES (?, ?, ?, 1)";
    return insertData($sql, [$userId, $fileName, $filePath]);
}

// Get the message from POST request
$message = $_POST['message'] ?? '';
$response = '';
$userId = $_SESSION['user_id'] ?? null;

if (!empty($message)) {
    // Process resume upload request
    if (strpos(strtolower($message), 'analyze my resume') !== false && isset($_FILES['resume'])) {
        $target_dir = "uploads/resumes/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file = $_FILES["resume"];
        $fileName = basename($file["name"]);
        $targetFile = $target_dir . time() . '_' . $fileName;
        
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            if ($userId) {
                storeResumeUpload($userId, $fileName, $targetFile);
            }
            $response = "I've received your resume. I can help you analyze it and provide suggestions for improvement. What specific aspects would you like me to focus on?";
        } else {
            $response = "I apologize, but there was an error uploading your resume. Please try again.";
        }
    } else {
        // Get AI-powered response
        $response = callOpenAI($message, $system_prompt);
    }
    
    // Store chat history if user is logged in
    if ($userId) {
        storeChatMessage($userId, $message, $response);
    }
} else {
    $response = "I'm here to help with your career questions. Feel free to ask about career paths, resume writing, interview tips, or upload your resume for analysis.";
}

// Return JSON response
echo json_encode(['response' => $response]);
?> 