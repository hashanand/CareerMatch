<?php
require_once 'includes/session_manager.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get job ID and source from URL
$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : null;
$source = $_GET['source'] ?? '';

// Define skill categories
$skill_categories = [
    'programming_languages' => [
        'python', 'java', 'javascript', 'php', 'c++', 'c#', 'ruby', 'swift', 'kotlin', 'go',
        'rust', 'typescript', 'scala', 'perl', 'r', 'matlab', 'dart', 'lua', 'haskell', 'objective-c'
    ],
    'web_technologies' => [
        'html', 'css', 'sass', 'less', 'bootstrap', 'tailwind', 'jquery', 'react', 'angular',
        'vue.js', 'node.js', 'express.js', 'django', 'flask', 'laravel', 'spring', 'asp.net'
    ],
    'databases' => [
        'mysql', 'postgresql', 'mongodb', 'oracle', 'sql server', 'sqlite', 'redis', 'cassandra',
        'elasticsearch', 'dynamodb', 'mariadb', 'neo4j', 'couchdb', 'firebase', 'supabase'
    ],
    'cloud_platforms' => [
        'aws', 'azure', 'google cloud', 'heroku', 'digitalocean', 'cloudflare', 'vercel',
        'netlify', 'aws ec2', 'aws s3', 'aws lambda', 'azure functions'
    ],
    'ai_ml' => [
        'machine learning', 'deep learning', 'tensorflow', 'pytorch', 'keras', 'scikit-learn',
        'opencv', 'nltk', 'pandas', 'numpy', 'matplotlib', 'seaborn', 'jupyter'
    ],
    'soft_skills' => [
        'leadership', 'communication', 'teamwork', 'problem solving', 'analytical', 'project management',
        'time management', 'critical thinking', 'decision making', 'conflict resolution'
    ],
    'development_tools' => [
        'git', 'github', 'gitlab', 'bitbucket', 'jira', 'confluence', 'trello', 'asana', 'slack',
        'visual studio', 'vscode', 'intellij', 'eclipse', 'xcode', 'android studio', 'postman'
    ]
];

// Define experience levels
$experience_indicators = [
    'executive' => [
        'chief', 'cto', 'cio', 'vp', 'vice president', 'director', '10+ years', 
        'head of', 'principal', '15+ years', 'executive'
    ],
    'senior' => [
        'senior', 'lead', 'architect', 'manager', '5+ years', '6+ years', '7+ years', '8+ years',
        'staff engineer', 'technical lead', 'team lead'
    ],
    'mid' => [
        'mid-level', '3+ years', '4+ years', '3 years', '4 years', 'intermediate',
        'software engineer ii', 'developer ii', 'experienced'
    ],
    'junior' => [
        'junior', 'entry level', '1+ year', '2+ years', 'intern', 'fresher', 'associate',
        'software engineer i', 'developer i', 'graduate'
    ]
];

// Get the job data based on source
$job = null;
if ($source === 'matches' && isset($_SESSION['job_matches']['jobs'][$job_id])) {
    $job = $_SESSION['job_matches']['jobs'][$job_id];
} elseif ($source === 'saved' && isset($_SESSION['saved_jobs'][$job_id])) {
    $job = $_SESSION['saved_jobs'][$job_id];
}

// Redirect if job not found
if (!$job) {
    header("Location: job-matches.php");
    exit;
}

// Get user skills for comparison
$user_skills = isset($_SESSION['resume_data']['skills']) ? $_SESSION['resume_data']['skills'] : [];

// Categorize job skills
$job_skills_raw = explode(', ', $job['skills'] ?? '');
$job_skills_categorized = [];
$uncategorized_skills = [];

foreach ($job_skills_raw as $skill) {
    $skill = trim(strtolower($skill));
    $categorized = false;
    
    foreach ($skill_categories as $category => $category_skills) {
        if (in_array($skill, $category_skills)) {
            if (!isset($job_skills_categorized[$category])) {
                $job_skills_categorized[$category] = [];
            }
            $job_skills_categorized[$category][] = $skill;
            $categorized = true;
            break;
        }
    }
    
    if (!$categorized) {
        $uncategorized_skills[] = $skill;
    }
}

// Determine experience level
$job_experience_level = 'entry level';
$job_description = strtolower($job['description'] ?? '');

foreach ($experience_indicators as $level => $indicators) {
    foreach ($indicators as $indicator) {
        if (strpos($job_description, strtolower($indicator)) !== false) {
            $job_experience_level = $level;
            break 2;
        }
    }
}

// Calculate matching skills by category
$matching_skills_categorized = [];
$missing_skills_categorized = [];

foreach ($job_skills_categorized as $category => $skills) {
    $matching_skills_categorized[$category] = array_intersect($skills, $user_skills);
    $missing_skills_categorized[$category] = array_diff($skills, $user_skills);
}

// Handle uncategorized skills
$matching_skills_uncategorized = array_intersect($uncategorized_skills, $user_skills);
$missing_skills_uncategorized = array_diff($uncategorized_skills, $user_skills);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($job['title']); ?> - Job Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .dashboard-container {
            background-color: #f8f9fa;
            min-height: 100vh;
            padding: 2rem 0;
        }
        .job-header {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }
        .company-logo {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f6f8ff 0%, #f1f3f9 100%);
            border-radius: 15px;
            font-size: 2rem;
            color: #6a11cb;
            box-shadow: 0 3px 10px rgba(106, 17, 203, 0.1);
        }
        .job-content {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }
        .meta-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            color: #6c757d;
        }
        .meta-item:last-child {
            border-bottom: none;
        }
        .meta-item i {
            width: 24px;
            margin-right: 10px;
            color: #6a11cb;
            font-size: 1.1rem;
        }
        .skills-badge {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 500;
            box-shadow: 0 3px 10px rgba(40, 167, 69, 0.2);
        }
        .skill-tag {
            display: inline-block;
            padding: 0.4rem 1rem;
            margin: 0.25rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .skill-tag.matched {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .skill-tag.missing {
            background-color: #fff3e0;
            color: #ef6c00;
        }
        .action-buttons {
            position: sticky;
            bottom: 0;
            background: white;
            padding: 1rem 0;
            border-top: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 -5px 15px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="dashboard-container">
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-2 d-none d-lg-block p-0">
                    <?php include 'sidebar.php'; ?>
                </div>

                <!-- Main Content -->
                <div class="col-lg-10 col-md-12 p-4">
                    <!-- Back Button -->
                    <a href="<?php echo $source === 'saved' ? 'saved-jobs.php' : 'job-matches.php'; ?>" 
                       class="btn btn-outline-primary mb-4">
                        <i class="bi bi-arrow-left me-2"></i> Back to <?php echo $source === 'saved' ? 'Saved Jobs' : 'Job Matches'; ?>
                    </a>

                    <!-- Job Header -->
                    <div class="job-header">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="company-logo mb-3 mb-md-0">
                                    <?php if (!empty($job['company_logo'])): ?>
                                        <img src="<?php echo htmlspecialchars($job['company_logo']); ?>" 
                                             alt="<?php echo htmlspecialchars($job['company']); ?>"
                                             class="img-fluid">
                                    <?php else: ?>
                                        <?php echo substr($job['company'], 0, 1); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col">
                                <h1 class="h3 mb-2"><?php echo htmlspecialchars($job['title']); ?></h1>
                                <h2 class="h5 text-muted mb-3"><?php echo htmlspecialchars($job['company']); ?></h2>
                                <div class="d-flex flex-wrap gap-3">
                                    <?php if (!empty($job['location'])): ?>
                                        <div class="meta-item border-0 p-0">
                                            <i class="bi bi-geo-alt"></i>
                                            <span><?php echo htmlspecialchars($job['location']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($job['job_type'])): ?>
                                        <div class="meta-item border-0 p-0">
                                            <i class="bi bi-briefcase"></i>
                                            <span><?php echo htmlspecialchars($job['job_type']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($job['salary'])): ?>
                                        <div class="meta-item border-0 p-0">
                                            <i class="bi bi-cash"></i>
                                            <span><?php echo htmlspecialchars($job['salary']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="meta-item border-0 p-0">
                                        <i class="bi bi-clock"></i>
                                        <span>Posted <?php echo htmlspecialchars($job['posted_at']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-auto mt-3 mt-md-0">
                                <div class="skills-badge">
                                    <i class="bi bi-lightning-charge-fill me-2"></i>
                                    <?php echo number_format($job['skills_match']); ?>% Match
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Job Content -->
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="job-content mb-4">
                                <h3 class="h5 mb-4">Job Description</h3>
                                <div class="job-description mb-4">
                                    <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                                </div>

                                <?php if (!empty($job_skills_categorized)): ?>
                                <h3 class="h5 mb-3">Required Skills</h3>
                                <div class="mb-4">
                                    <?php foreach ($job_skills_categorized as $category => $skills): ?>
                                        <div class="mb-3">
                                            <h6 class="text-muted small mb-2"><?php echo ucwords(str_replace('_', ' ', $category)); ?></h6>
                                            <?php foreach ($skills as $skill): ?>
                                                <span class="skill-tag <?php echo in_array($skill, $user_skills) ? 'matched' : 'missing'; ?>">
                                                    <?php if (in_array($skill, $user_skills)): ?>
                                                        <i class="bi bi-check-circle-fill me-1"></i>
                                                    <?php else: ?>
                                                        <i class="bi bi-exclamation-circle-fill me-1"></i>
                                                    <?php endif; ?>
                                                    <?php echo htmlspecialchars(ucfirst($skill)); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($job['requirements'])): ?>
                                <h3 class="h5 mb-3">Requirements</h3>
                                <div class="mb-4">
                                    <?php echo nl2br(htmlspecialchars($job['requirements'])); ?>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($job['benefits'])): ?>
                                <h3 class="h5 mb-3">Benefits</h3>
                                <div class="mb-4">
                                    <?php echo nl2br(htmlspecialchars($job['benefits'])); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <!-- Skills Match Analysis -->
                            <div class="job-content mb-4">
                                <h3 class="h5 mb-4">Skills Match Analysis</h3>
                                
                                <!-- Experience Level -->
                                <div class="mb-4">
                                    <h6 class="mb-3">Experience Level Required</h6>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>
                                        This position requires <strong><?php echo ucfirst($job_experience_level); ?></strong> level experience
                                    </div>
                                </div>
                                
                                <!-- Overall Match -->
                                <div class="mb-4">
                                    <label class="form-label">Overall Match</label>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: <?php echo $job['skills_match']; ?>%" 
                                             aria-valuenow="<?php echo $job['skills_match']; ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <?php echo number_format($job['skills_match']); ?>%
                                        </div>
                                    </div>
                                </div>

                                <!-- Matching Skills by Category -->
                                <div class="mb-4">
                                    <h6 class="mb-3">Matching Skills</h6>
                                    <?php foreach ($matching_skills_categorized as $category => $skills): ?>
                                        <?php if (!empty($skills)): ?>
                                            <div class="mb-3">
                                                <h6 class="text-muted small mb-2"><?php echo ucwords(str_replace('_', ' ', $category)); ?></h6>
                                                <?php foreach ($skills as $skill): ?>
                                                    <span class="skill-tag matched">
                                                        <i class="bi bi-check-circle-fill me-1"></i>
                                                        <?php echo htmlspecialchars(ucfirst($skill)); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    
                                    <?php if (!empty($matching_skills_uncategorized)): ?>
                                        <div class="mb-3">
                                            <h6 class="text-muted small mb-2">Other Skills</h6>
                                            <?php foreach ($matching_skills_uncategorized as $skill): ?>
                                                <span class="skill-tag matched">
                                                    <i class="bi bi-check-circle-fill me-1"></i>
                                                    <?php echo htmlspecialchars(ucfirst($skill)); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Missing Skills by Category -->
                                <div class="mb-4">
                                    <h6 class="mb-3">Skills to Develop</h6>
                                    <?php foreach ($missing_skills_categorized as $category => $skills): ?>
                                        <?php if (!empty($skills)): ?>
                                            <div class="mb-3">
                                                <h6 class="text-muted small mb-2"><?php echo ucwords(str_replace('_', ' ', $category)); ?></h6>
                                                <?php foreach ($skills as $skill): ?>
                                                    <span class="skill-tag missing">
                                                        <i class="bi bi-exclamation-circle-fill me-1"></i>
                                                        <?php echo htmlspecialchars(ucfirst($skill)); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    
                                    <?php if (!empty($missing_skills_uncategorized)): ?>
                                        <div class="mb-3">
                                            <h6 class="text-muted small mb-2">Other Skills</h6>
                                            <?php foreach ($missing_skills_uncategorized as $skill): ?>
                                                <span class="skill-tag missing">
                                                    <i class="bi bi-exclamation-circle-fill me-1"></i>
                                                    <?php echo htmlspecialchars(ucfirst($skill)); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Recommendations -->
                                <?php if (!empty($missing_skills_categorized) || !empty($missing_skills_uncategorized)): ?>
                                <div>
                                    <h6 class="mb-3">Recommendations</h6>
                                    <ul class="list-unstyled">
                                        <?php foreach ($missing_skills_categorized as $category => $skills): ?>
                                            <?php if (!empty($skills)): ?>
                                                <li class="mb-2">
                                                    <i class="bi bi-lightbulb text-warning me-2"></i>
                                                    Consider focusing on <?php echo strtolower(str_replace('_', ' ', $category)); ?> skills: 
                                                    <?php echo htmlspecialchars(implode(', ', array_map('ucfirst', $skills))); ?>
                                                </li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        <li class="mb-2">
                                            <i class="bi bi-lightbulb text-warning me-2"></i>
                                            Look for projects that can help you gain practical experience
                                        </li>
                                        <li>
                                            <i class="bi bi-lightbulb text-warning me-2"></i>
                                            Join relevant professional communities and networks
                                        </li>
                                    </ul>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Company Information -->
                            <?php if (!empty($job['company_description'])): ?>
                            <div class="job-content mb-4">
                                <h3 class="h5 mb-4">About <?php echo htmlspecialchars($job['company']); ?></h3>
                                <div>
                                    <?php echo nl2br(htmlspecialchars($job['company_description'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <div class="container-fluid">
                            <div class="row justify-content-between align-items-center">
                                <div class="col-auto">
                                    <a href="<?php echo $source === 'saved' ? 'saved-jobs.php' : 'job-matches.php'; ?>" 
                                       class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left me-2"></i> Back
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <?php if ($source === 'matches'): ?>
                                    <button class="btn btn-outline-primary me-2" onclick='saveJob(<?php echo json_encode($job); ?>)'>
                                        <i class="bi bi-bookmark me-2"></i> Save Job
                                    </button>
                                    <?php endif; ?>
                                    <a href="<?php echo htmlspecialchars($job['link'] ?? $job['url'] ?? $job['job_url'] ?? '#'); ?>" 
                                       class="btn btn-primary" 
                                       target="_blank"
                                       <?php if (empty($job['link']) && empty($job['url']) && empty($job['job_url'])): ?>
                                       onclick="alert('Application link not available'); return false;"
                                       <?php endif; ?>>
                                        <i class="bi bi-box-arrow-up-right me-2"></i> Apply Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Save job functionality
    function saveJob(job) {
        const formData = new FormData();
        formData.append('job', JSON.stringify(job));

        // Show loading state
        const saveBtn = event.target;
        const originalText = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Saving...';

        fetch('includes/save_job.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success state
                saveBtn.classList.remove('btn-outline-primary');
                saveBtn.classList.add('btn-success');
                saveBtn.innerHTML = '<i class="bi bi-check-lg me-2"></i> Saved';
                setTimeout(() => {
                    saveBtn.classList.remove('btn-success');
                    saveBtn.classList.add('btn-outline-primary');
                    saveBtn.innerHTML = originalText;
                    saveBtn.disabled = false;
                }, 2000);
            } else {
                // Show error state
                saveBtn.classList.remove('btn-outline-primary');
                saveBtn.classList.add('btn-danger');
                saveBtn.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i> ' + (data.message || 'Error');
                setTimeout(() => {
                    saveBtn.classList.remove('btn-danger');
                    saveBtn.classList.add('btn-outline-primary');
                    saveBtn.innerHTML = originalText;
                    saveBtn.disabled = false;
                }, 3000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Show error state
            saveBtn.classList.remove('btn-outline-primary');
            saveBtn.classList.add('btn-danger');
            saveBtn.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i> Error';
            setTimeout(() => {
                saveBtn.classList.remove('btn-danger');
                saveBtn.classList.add('btn-outline-primary');
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }, 3000);
        });
    }
    </script>
</body>
</html>

