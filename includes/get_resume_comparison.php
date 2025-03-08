<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to compare jobs']);
    exit;
}

// Check if resume data exists
if (!isset($_SESSION['resume_data']) || !isset($_SESSION['resume_data']['skills'])) {
    echo json_encode(['success' => false, 'message' => 'Please upload your resume first']);
    exit;
}

// Get the job data from the request
$request_data = json_decode(file_get_contents('php://input'), true);
if (!isset($request_data['job'])) {
    echo json_encode(['success' => false, 'message' => 'No job data provided']);
    exit;
}

$job = $request_data['job'];
$resume_skills = $_SESSION['resume_data']['skills'];

// Extract skills from job description
function extractSkills($text) {
    $common_skills = [
        // Programming Languages
        'python', 'java', 'javascript', 'php', 'ruby', 'c\+\+', 'c#', 'swift', 'kotlin', 'go',
        // Web Technologies
        'html', 'css', 'react', 'angular', 'vue', 'node\.?js', 'express', 'django', 'flask', 'laravel',
        // Databases
        'sql', 'mysql', 'postgresql', 'mongodb', 'oracle', 'redis', 'elasticsearch',
        // Tools & Platforms
        'git', 'docker', 'kubernetes', 'aws', 'azure', 'gcp', 'jenkins', 'jira',
        // Concepts
        'api', 'rest', 'graphql', 'mvc', 'oop', 'ci/cd', 'agile', 'scrum',
        // Mobile
        'android', 'ios', 'react native', 'flutter',
        // Other
        'linux', 'unix', 'bash', 'powershell', 'testing', 'debugging'
    ];
    
    $pattern = '/\b(' . implode('|', $common_skills) . ')\b/i';
    preg_match_all($pattern, strtolower($text), $matches);
    
    return array_unique($matches[0]);
}

// Get skills from job description
$job_skills = extractSkills($job['description']);

// Find matched skills
$matched_skills = array_intersect($resume_skills, $job_skills);

// Generate recommendations
$recommendations = [];

// Missing important skills
$missing_skills = array_diff($job_skills, $resume_skills);
if (!empty($missing_skills)) {
    $recommendations[] = "Consider learning or highlighting experience with: " . implode(', ', $missing_skills);
}

// Skills to emphasize
if (!empty($matched_skills)) {
    $recommendations[] = "Emphasize your experience with: " . implode(', ', $matched_skills);
}

// Additional skills that might be relevant
$potential_skills = array_diff($resume_skills, $job_skills);
if (!empty($potential_skills)) {
    $recommendations[] = "You have additional relevant skills that could be valuable: " . implode(', ', $potential_skills);
}

// Skill level recommendations
if ($job['skills_match'] < 70) {
    $recommendations[] = "Consider upskilling in the required technologies to improve your match percentage";
} elseif ($job['skills_match'] >= 90) {
    $recommendations[] = "You're an excellent match! Make sure to highlight your extensive experience in the required skills";
}

// Return the comparison data
echo json_encode([
    'success' => true,
    'resume_skills' => $resume_skills,
    'job_skills' => $job_skills,
    'matched_skills' => array_values($matched_skills),
    'recommendations' => $recommendations
]); 