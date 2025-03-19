<?php
require_once 'db_config.php';

// User Authentication Functions
function authenticateUser($email, $password) {
    $sql = "SELECT user_id, email, password_hash, full_name, failed_login_attempts, last_login_attempt, account_status 
            FROM users WHERE email = ?";
    $user = getRow($sql, [$email]);
    
    if (!$user) {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }
    
    if ($user['account_status'] === 'locked') {
        if ($user['last_login_attempt'] && (time() - strtotime($user['last_login_attempt'])) < LOCKOUT_DURATION) {
            $remaining_time = ceil((LOCKOUT_DURATION - (time() - strtotime($user['last_login_attempt']))) / 60);
            return ['success' => false, 'message' => "Account is locked. Try again in {$remaining_time} minutes."];
        }
        // Reset lockout if duration has passed
        executeQuery("UPDATE users SET failed_login_attempts = 0, account_status = 'active' WHERE user_id = ?", 
                    [$user['user_id']]);
    }
    
    if (password_verify($password, $user['password_hash'])) {
        // Reset failed attempts
        executeQuery("UPDATE users SET failed_login_attempts = 0, last_login_attempt = NOW() WHERE user_id = ?", 
                    [$user['user_id']]);
        return ['success' => true, 'user' => $user];
    }
    
    // Increment failed attempts
    $new_attempts = $user['failed_login_attempts'] + 1;
    $status = $new_attempts >= MAX_LOGIN_ATTEMPTS ? 'locked' : 'active';
    
    executeQuery("UPDATE users SET failed_login_attempts = ?, last_login_attempt = NOW(), account_status = ? 
                 WHERE user_id = ?", 
                [$new_attempts, $status, $user['user_id']]);
    
    if ($new_attempts >= MAX_LOGIN_ATTEMPTS) {
        return ['success' => false, 'message' => 'Account locked due to too many failed attempts.'];
    }
    
    $remaining = MAX_LOGIN_ATTEMPTS - $new_attempts;
    return ['success' => false, 'message' => "Invalid password. {$remaining} attempts remaining."];
}

// User Profile Functions
function getUserProfile($userId) {
    $sql = "SELECT u.*, 
            GROUP_CONCAT(DISTINCT s.skill_name) as skills,
            COUNT(DISTINCT sj.saved_job_id) as saved_jobs_count
            FROM users u 
            LEFT JOIN user_skills s ON u.user_id = s.user_id
            LEFT JOIN saved_jobs sj ON u.user_id = sj.user_id
            WHERE u.user_id = ?
            GROUP BY u.user_id";
    return getRow($sql, [$userId]);
}

function updateUserProfile($userId, $data) {
    $allowedFields = ['full_name', 'bio', 'location', 'phone'];
    $updates = [];
    $params = [];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $params[] = $data[$field];
        }
    }
    
    if (empty($updates)) {
        return false;
    }
    
    $params[] = $userId;
    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE user_id = ?";
    return executeQuery($sql, $params);
}

// Skills Management
function addUserSkill($userId, $skillName, $proficiency) {
    $sql = "INSERT INTO user_skills (user_id, skill_name, proficiency_level) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE proficiency_level = VALUES(proficiency_level)";
    return insertData($sql, [$userId, $skillName, $proficiency]);
}

function removeUserSkill($userId, $skillName) {
    $sql = "DELETE FROM user_skills WHERE user_id = ? AND skill_name = ?";
    return executeQuery($sql, [$userId, $skillName]);
}

function getUserSkills($userId) {
    $sql = "SELECT skill_name, proficiency_level FROM user_skills WHERE user_id = ?";
    return getRows($sql, [$userId]);
}

// Job Management
function saveJob($userId, $jobData) {
    $sql = "INSERT INTO saved_jobs (user_id, job_title, company_name, job_location, 
            job_description, salary_range, job_url) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    return insertData($sql, [
        $userId,
        $jobData['title'],
        $jobData['company'],
        $jobData['location'],
        $jobData['description'],
        $jobData['salary'],
        $jobData['url']
    ]);
}

function updateJobStatus($userId, $jobId, $status) {
    $sql = "UPDATE saved_jobs SET application_status = ? 
            WHERE saved_job_id = ? AND user_id = ?";
    return executeQuery($sql, [$status, $jobId, $userId]);
}

function getSavedJobs($userId, $status = null) {
    $sql = "SELECT * FROM saved_jobs WHERE user_id = ?";
    $params = [$userId];
    
    if ($status) {
        $sql .= " AND application_status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY created_at DESC";
    return getRows($sql, $params);
}

// Education Management
function addEducation($userId, $eduData) {
    $sql = "INSERT INTO user_education (user_id, institution_name, degree, 
            field_of_study, start_date, end_date, grade) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    return insertData($sql, [
        $userId,
        $eduData['institution'],
        $eduData['degree'],
        $eduData['field'],
        $eduData['start_date'],
        $eduData['end_date'],
        $eduData['grade']
    ]);
}

function getUserEducation($userId) {
    $sql = "SELECT * FROM user_education WHERE user_id = ? ORDER BY end_date DESC";
    return getRows($sql, [$userId]);
}

// Experience Management
function addExperience($userId, $expData) {
    $sql = "INSERT INTO user_experience (user_id, company_name, position, 
            location, start_date, end_date, description) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    return insertData($sql, [
        $userId,
        $expData['company'],
        $expData['position'],
        $expData['location'],
        $expData['start_date'],
        $expData['end_date'],
        $expData['description']
    ]);
}

function getUserExperience($userId) {
    $sql = "SELECT * FROM user_experience WHERE user_id = ? ORDER BY end_date DESC";
    return getRows($sql, [$userId]);
} 