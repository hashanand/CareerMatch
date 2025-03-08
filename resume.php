<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// In a real application, you would fetch this data from a database
$resumeUploaded = false;
$resumeData = null;

// Create uploads directory if it doesn't exist
$uploadDir = 'uploads/resumes/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Add at the top after session_start()
require_once 'vendor/autoload.php'; // For Composer dependencies

use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\IOFactory;

// Define skill categories (matching analyzer)
$skill_categories = [
    'programming_languages' => [
        'python', 'java', 'javascript', 'php', 'c++', 'c#', 'ruby', 'swift', 'kotlin', 'go',
        'rust', 'typescript', 'scala', 'perl', 'r', 'matlab', 'dart', 'lua', 'haskell', 'objective-c',
        'assembly', 'vba', 'fortran', 'cobol', 'pascal', 'sql', 'plsql', 'bash'
    ],
    'web_technologies' => [
        'html', 'css', 'sass', 'less', 'bootstrap', 'tailwind', 'jquery', 'react', 'angular',
        'vue.js', 'node.js', 'express.js', 'django', 'flask', 'laravel', 'spring', 'asp.net',
        'wordpress', 'drupal', 'magento', 'shopify', 'webflow', 'gatsby', 'next.js', 'nuxt.js',
        'svelte', 'webpack', 'babel', 'graphql', 'rest api', 'soap', 'xml', 'json'
    ],
    'databases' => [
        'mysql', 'postgresql', 'mongodb', 'oracle', 'sql server', 'sqlite', 'redis', 'cassandra',
        'elasticsearch', 'dynamodb', 'mariadb', 'neo4j', 'couchdb', 'firebase', 'supabase'
    ],
    'cloud_platforms' => [
        'aws', 'azure', 'google cloud', 'heroku', 'digitalocean', 'cloudflare', 'vercel',
        'netlify', 'aws ec2', 'aws s3', 'aws lambda', 'azure functions', 'gcp compute engine',
        'kubernetes', 'docker', 'terraform', 'ansible'
    ],
    'ai_ml' => [
        'machine learning', 'deep learning', 'tensorflow', 'pytorch', 'keras', 'scikit-learn',
        'opencv', 'nltk', 'pandas', 'numpy', 'matplotlib', 'seaborn', 'jupyter', 'computer vision',
        'nlp', 'neural networks', 'data mining', 'predictive modeling'
    ],
    'soft_skills' => [
        'leadership', 'communication', 'teamwork', 'problem solving', 'analytical', 'project management',
        'time management', 'critical thinking', 'decision making', 'conflict resolution', 'negotiation',
        'presentation', 'mentoring', 'agile', 'scrum', 'customer service', 'collaboration',
        'adaptability', 'creativity', 'emotional intelligence'
    ],
    'development_tools' => [
        'git', 'github', 'gitlab', 'bitbucket', 'jira', 'confluence', 'trello', 'asana', 'slack',
        'visual studio', 'vscode', 'intellij', 'eclipse', 'xcode', 'android studio', 'postman',
        'swagger', 'jenkins', 'travis ci', 'circle ci', 'docker compose', 'kubernetes helm'
    ],
    'certifications' => [
        'aws certified', 'azure certified', 'google certified', 'cisco certified', 'comptia',
        'pmp', 'scrum master', 'itil', 'cissp', 'ceh', 'security+', 'network+', 'a+',
        'oracle certified', 'microsoft certified'
    ]
];

// Define experience indicators (matching analyzer)
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

function analyzeResume($filePath) {
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $content = '';
    
    try {
        if ($ext === 'pdf') {
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            $content = $pdf->getText();
        } elseif ($ext === 'doc' || $ext === 'docx') {
            // For now, we'll return an error for DOC/DOCX files
            throw new Exception("DOC/DOCX file support is currently unavailable. Please upload a PDF file.");
        }

        // Save the extracted text to a temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'resume_');
        file_put_contents($tempFile, $content);

        // Call the Python ML parser
        $command = escapeshellcmd('python resume_parser.py ' . escapeshellarg($tempFile));
        $output = shell_exec($command);
        unlink($tempFile); // Clean up temp file

        if ($output === null) {
            throw new Exception("Failed to execute ML parser");
        }

        $result = json_decode($output, true);
        if (isset($result['error'])) {
            throw new Exception($result['error']);
        }

        return $result['data'];
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}

// Handle form submission
$success = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['upload_type']) && $_POST['upload_type'] === 'drive') {
        // Handle Google Drive upload (would need Google Drive API integration)
        // This is a placeholder for actual Google Drive integration
        $success = false;
        $error = 'Google Drive integration coming soon!';
    } else {
        // Handle local file upload
        if (isset($_FILES['resumeFile']) && $_FILES['resumeFile']['error'] === 0) {
            $allowed = ['pdf' => 'application/pdf', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            $filename = $_FILES['resumeFile']['name'];
            $filetype = $_FILES['resumeFile']['type'];
            $filesize = $_FILES['resumeFile']['size'];

            // Verify file extension
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (!array_key_exists($ext, $allowed)) {
                $error = 'Error: Please select a valid file format (PDF, DOC, DOCX).';
            }

            // Verify file size - 5MB maximum
            $maxsize = 5 * 1024 * 1024;
            if ($filesize > $maxsize) {
                $error = 'Error: File size is larger than 5MB.';
            }

            // Verify MIME type
            if (empty($error) && !in_array($filetype, $allowed)) {
                $error = 'Error: Invalid file type. Please upload a PDF, DOC, or DOCX file.';
            }

            if (empty($error)) {
                // Generate unique filename
                $newFilename = uniqid() . '_' . $filename;
                $destination = $uploadDir . $newFilename;

                // Save the file
                if (move_uploaded_file($_FILES['resumeFile']['tmp_name'], $destination)) {
                    // Call Python script to parse resume
                    $command = escapeshellcmd('python resume_parser.py ' . escapeshellarg($destination));
                    $output = shell_exec($command);
                    
                    if ($output === null) {
                        $error = 'Error: Failed to execute resume parser.';
                    } else {
                        $analysis = json_decode($output, true);
                        
                        if (isset($analysis['error'])) {
                            $error = 'Error parsing resume: ' . $analysis['error'];
                        } else {
    $success = true;
                            $resumeUploaded = true;
                            $resumeData = array_merge([
                                'filename' => $filename,
                                'filepath' => $destination,
                                'uploaded_date' => date('F j, Y'),
                                'size' => number_format($filesize / 1024, 0) . ' KB',
                                'file_type' => strtoupper($ext)
                            ], $analysis['data'] ?? ['education' => [], 'skills' => []]);
                            
                            // Save resume data to session for persistence
                            $_SESSION['resume_data'] = $resumeData;
                        }
                    }
                } else {
                    $error = 'Error: Failed to save the file.';
                }
            }
        } else {
            $error = 'Error: Please select a file to upload.';
        }
    }
} else {
    // Check if resume data exists in session
    if (isset($_SESSION['resume_data'])) {
        $resumeUploaded = true;
        $resumeData = $_SESSION['resume_data'];
    }
}

// After $resumeData is set, categorize the skills
if ($resumeUploaded && $resumeData && !empty($resumeData['skills'])) {
    $categorized_skills = [];
    $uncategorized_skills = [];
    
    foreach ($resumeData['skills'] as $skill) {
        $skill = trim(strtolower($skill));
        $categorized = false;
        
        foreach ($skill_categories as $category => $category_skills) {
            if (in_array($skill, $category_skills)) {
                if (!isset($categorized_skills[$category])) {
                    $categorized_skills[$category] = [];
                }
                $categorized_skills[$category][] = $skill;
                $categorized = true;
                break;
            }
        }
        
        if (!$categorized) {
            $uncategorized_skills[] = $skill;
        }
    }
    
    // Determine experience level
    $experience_level = 'entry level';
    $resume_text = strtolower(implode(' ', $resumeData['skills']));
    
    foreach ($experience_indicators as $level => $indicators) {
        foreach ($indicators as $indicator) {
            if (strpos($resume_text, strtolower($indicator)) !== false) {
                $experience_level = $level;
                break 2;
            }
        }
    }
    
    $resumeData['categorized_skills'] = $categorized_skills;
    $resumeData['uncategorized_skills'] = $uncategorized_skills;
    $resumeData['experience_level'] = $experience_level;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume - CareerMatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .dashboard-container {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .sidebar {
            background-color: white;
            border-right: 1px solid #e9ecef;
            height: calc(100vh - 72px);
            position: sticky;
            top: 72px;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.25rem;
            color: #495057;
            text-decoration: none;
            border-radius: 0.25rem;
            margin-bottom: 0.25rem;
        }
        .sidebar-link:hover, .sidebar-link.active {
            background-color: #f8f9fa;
            color: #6a11cb;
        }
        .sidebar-link i {
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .upload-area:hover {
            border-color: #6a11cb;
            background-color: rgba(106, 17, 203, 0.05);
        }
        .resume-card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .resume-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 1.5rem;
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
                    <div class="sidebar py-4">
                        <div class="px-4 mb-4">
                            <h5 class="fw-bold mb-0">Dashboard</h5>
                        </div>
                        <div class="px-3">
                            <a href="dashboard.php" class="sidebar-link">
                                <i class="bi bi-house-door"></i> Home
                            </a>
                            <a href="profile.php" class="sidebar-link">
                                <i class="bi bi-person"></i> My Profile
                            </a>
                            <a href="resume.php" class="sidebar-link active">
                                <i class="bi bi-file-earmark-text"></i> Resume
                            </a>
                            <a href="job-matches.php?search=true" class="sidebar-link">
                                <i class="bi bi-briefcase"></i> Job Matches
                            </a>
                            <a href="applications.php" class="sidebar-link">
                                <i class="bi bi-send"></i> Applications
                            </a>
                            <a href="saved-jobs.php" class="sidebar-link">
                                <i class="bi bi-bookmark"></i> Saved Jobs
                            </a>
                            <a href="messages.php" class="sidebar-link">
                                <i class="bi bi-chat"></i> Messages
                            </a>
                            <a href="settings.php" class="sidebar-link">
                                <i class="bi bi-gear"></i> Settings
                            </a>
                            <hr>
                            <a href="logout.php" class="sidebar-link text-danger">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Main Content -->
                <div class="col-lg-10 col-md-12 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold mb-0">Resume Management</h4>
                    </div>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            Your resume has been uploaded successfully!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <?php if ($resumeUploaded && $resumeData): ?>
                                <!-- Resume Card -->
                                <div class="resume-card mb-4">
                                    <div class="resume-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="fw-bold mb-1">Current Resume</h5>
                                                <p class="mb-0">Uploaded on <?php echo htmlspecialchars($resumeData['uploaded_date']); ?></p>
                                            </div>
                                            <div>
                                                <a href="<?php echo htmlspecialchars($resumeData['filepath']); ?>" class="btn btn-light btn-sm me-2" download>
                                                    <i class="bi bi-download me-1"></i> Download
                                                </a>
                                                <button class="btn btn-light btn-sm me-2" data-bs-toggle="modal" data-bs-target="#uploadResumeModal">
                                                    <i class="bi bi-arrow-repeat me-1"></i> Replace
                                                </button>
                                                <?php if (!empty($resumeData['skills'])): ?>
                                                <a href="job-matches.php?search=true" class="btn btn-success btn-sm">
                                                    <i class="bi bi-search me-1"></i> Search Jobs
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="me-3">
                                                <?php if ($resumeData['file_type'] === 'PDF'): ?>
                                                <i class="bi bi-file-earmark-pdf text-danger fs-1"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-file-earmark-word text-primary fs-1"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($resumeData['filename']); ?></h5>
                                                <p class="text-muted mb-0">
                                                    <?php echo htmlspecialchars($resumeData['size']); ?> • 
                                                    <?php echo htmlspecialchars($resumeData['file_type']); ?>
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <!-- Skills Section -->
                                        <?php if (!empty($resumeData['skills'])): ?>
                                        <div class="mb-4">
                                            <h6 class="fw-bold mb-3">
                                                <i class="bi bi-tools text-primary me-2"></i>
                                                Skills Analysis
                                            </h6>
                                            
                                            <!-- Experience Level -->
                                            <div class="alert alert-info mb-4">
                                                <i class="bi bi-info-circle me-2"></i>
                                                Based on your skills and experience, you are at the 
                                                <strong><?php echo ucfirst($resumeData['experience_level']); ?></strong> level
                                            </div>
                                            
                                            <!-- Skills by Category -->
                                            <div class="card">
                                                <div class="card-body">
                                                    <?php foreach ($resumeData['categorized_skills'] as $category => $skills): ?>
                                                        <?php if (!empty($skills)): ?>
                                                            <div class="mb-3">
                                                                <h6 class="text-muted small mb-2"><?php echo ucwords(str_replace('_', ' ', $category)); ?></h6>
                                                                <div class="d-flex flex-wrap gap-2">
                                                                    <?php foreach ($skills as $skill): ?>
                                                                        <span class="badge bg-primary"><?php echo htmlspecialchars(ucfirst($skill)); ?></span>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                    
                                                    <?php if (!empty($resumeData['uncategorized_skills'])): ?>
                                                        <div class="mb-3">
                                                            <h6 class="text-muted small mb-2">Other Skills</h6>
                                                            <div class="d-flex flex-wrap gap-2">
                                                                <?php foreach ($resumeData['uncategorized_skills'] as $skill): ?>
                                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars(ucfirst($skill)); ?></span>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <!-- Skills Distribution -->
                                            <div class="mt-4">
                                                <h6 class="text-muted small mb-3">Skills Distribution</h6>
                                                <?php foreach ($resumeData['categorized_skills'] as $category => $skills): ?>
                                                    <?php if (!empty($skills)): ?>
                                                        <div class="mb-3">
                                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                                <small><?php echo ucwords(str_replace('_', ' ', $category)); ?></small>
                                                                <small class="text-muted"><?php echo count($skills); ?> skills</small>
                                                            </div>
                                                            <div class="progress" style="height: 8px;">
                                                                <div class="progress-bar" role="progressbar" 
                                                                     style="width: <?php echo (count($skills) / count($resumeData['skills']) * 100); ?>%" 
                                                                     aria-valuenow="<?php echo count($skills); ?>" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="<?php echo count($resumeData['skills']); ?>">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <!-- Parsing Information -->
                                        <?php if (isset($resumeData['metadata'])): ?>
                                        <div class="alert alert-info d-flex align-items-center" role="alert">
                                            <i class="bi bi-info-circle-fill me-2"></i>
                                            <div>
                                                Resume analyzed on <?php echo date('F j, Y g:i A', strtotime($resumeData['metadata']['parsed_date'])); ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Upload Resume Area -->
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="fw-bold mb-4">Upload Your Resume</h5>
                                        
                                        <ul class="nav nav-tabs mb-4" id="uploadTabs" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active" id="local-tab" data-bs-toggle="tab" data-bs-target="#local" type="button" role="tab" aria-controls="local" aria-selected="true">
                                                    <i class="bi bi-laptop me-2"></i>Local Computer
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="drive-tab" data-bs-toggle="tab" data-bs-target="#drive" type="button" role="tab" aria-controls="drive" aria-selected="false">
                                                    <i class="bi bi-google me-2"></i>Google Drive
                                                </button>
                                            </li>
                                        </ul>
                                        
                                        <div class="tab-content" id="uploadTabsContent">
                                            <!-- Local Upload Tab -->
                                            <div class="tab-pane fade show active" id="local" role="tabpanel" aria-labelledby="local-tab">
                                        <div class="upload-area mb-4" onclick="document.getElementById('resumeFile').click()">
                                            <i class="bi bi-cloud-arrow-up text-primary fs-1 mb-3"></i>
                                            <h5>Drag and drop your resume here</h5>
                                            <p class="text-muted mb-0">or click to browse files</p>
                                            <form method="post" action="" enctype="multipart/form-data" id="resumeForm">
                                                        <input type="file" id="resumeFile" name="resumeFile" class="d-none" accept=".pdf,.doc,.docx" onchange="document.getElementById('resumeForm').submit()">
                                                    </form>
                                                </div>
                                            </div>
                                            
                                            <!-- Google Drive Tab -->
                                            <div class="tab-pane fade" id="drive" role="tabpanel" aria-labelledby="drive-tab">
                                                <div class="text-center py-4">
                                                    <i class="bi bi-google-drive text-primary fs-1 mb-3"></i>
                                                    <h5>Import from Google Drive</h5>
                                                    <p class="text-muted mb-4">Select a resume file from your Google Drive</p>
                                                    <form method="post" action="">
                                                        <input type="hidden" name="upload_type" value="drive">
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="bi bi-google me-2"></i>Connect to Google Drive
                                                        </button>
                                            </form>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="text-muted small">
                                            <p class="mb-1">Supported file formats: PDF, DOCX, DOC</p>
                                            <p class="mb-0">Maximum file size: 5MB</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Resume Tips -->
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="fw-bold mb-3">Resume Tips for Fresh Graduates</h5>
                                    
                                    <div class="accordion" id="resumeTipsAccordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="headingOne">
                                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                                    Highlight Your Education
                                                </button>
                                            </h2>
                                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#resumeTipsAccordion">
                                                <div class="accordion-body">
                                                    <p>As a fresh graduate, your education is one of your strongest assets. Include details about your degree, relevant coursework, academic achievements, and any honors or awards you received.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="headingTwo">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                                    Showcase Relevant Projects
                                                </button>
                                            </h2>
                                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#resumeTipsAccordion">
                                                <div class="accordion-body">
                                                    <p>Include academic or personal projects that demonstrate your skills and knowledge. Describe the project, your role, the technologies or methodologies used, and the outcomes or results achieved.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="headingThree">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                                    Include Internships and Part-time Work
                                                </button>
                                            </h2>
                                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#resumeTipsAccordion">
                                                <div class="accordion-body">
                                                    <p>Even if your work experience isn't directly related to your target job, include internships, part-time jobs, or volunteer work to show your work ethic, responsibility, and transferable skills.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="headingFour">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                                    Emphasize Skills and Certifications
                                                </button>
                                            </h2>
                                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#resumeTipsAccordion">
                                                <div class="accordion-body">
                                                    <p>List technical skills, software proficiencies, languages, and any certifications you've earned. These can help compensate for limited work experience and show your commitment to professional development.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <!-- Resume Templates -->
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h5 class="fw-bold mb-3">Resume Templates</h5>
                                    <p class="text-muted mb-3">Need help creating a professional resume? Use one of our templates designed for fresh graduates.</p>
                                    
                                    <div class="d-grid gap-2">
                                        <a href="#" class="btn btn-outline-primary">
                                            <i class="bi bi-file-earmark-text me-2"></i> Modern Template
                                        </a>
                                        <a href="#" class="btn btn-outline-primary">
                                            <i class="bi bi-file-earmark-text me-2"></i> Professional Template
                                        </a>
                                        <a href="#" class="btn btn-outline-primary">
                                            <i class="bi bi-file-earmark-text me-2"></i> Creative Template
                                        </a>
                                        <a href="#" class="btn btn-outline-primary">
                                            <i class="bi bi-file-earmark-text me-2"></i> Technical Template
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Resume Review -->
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="fw-bold mb-3">Get Expert Resume Review</h5>
                                    <p class="text-muted mb-3">Have your resume reviewed by industry professionals to increase your chances of landing interviews.</p>
                                    
                                    <div class="d-grid">
                                        <a href="#" class="btn btn-primary">
                                            <i class="bi bi-star me-2"></i> Request Resume Review
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Upload Resume Modal -->
    <div class="modal fade" id="uploadResumeModal" tabindex="-1" aria-labelledby="uploadResumeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadResumeModalLabel">Upload New Resume</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="resumeFileModal" class="form-label">Select Resume File</label>
                            <input type="file" class="form-control" id="resumeFileModal" name="resumeFile">
                            <div class="form-text">Supported formats: PDF, DOCX, DOC (Max: 5MB)</div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Upload Resume</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

