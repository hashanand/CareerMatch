<?php
// Initialize chatbot session variables if not set
if (!isset($_SESSION['chatbot_history'])) {
    $_SESSION['chatbot_history'] = [];
}
if (!isset($_SESSION['chatbot_context'])) {
    $_SESSION['chatbot_context'] = [
        'last_interaction' => null,
        'conversation_state' => 'greeting'
    ];
}
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// In a real application, you would fetch this data from a database
$user = [
    'name' => $_SESSION['user_name'] ?? 'Demo User',
    'profile_completion' => 75,
    'resume_uploaded' => true,
    'skills_extracted' => ['JavaScript', 'React', 'Node.js', 'HTML', 'CSS', 'UI/UX Design'],
    'job_matches' => 24,
    'saved_jobs' => 5,
    'applications' => 8
];

$recentJobs = [
    [
        'id' => 1,
        'title' => 'Frontend Developer',
        'company' => 'TechCorp',
        'location' => 'New York, NY',
        'match_score' => 95,
        'posted_date' => '2 days ago',
        'salary' => '$70,000 - $85,000',
        'type' => 'Full-time'
    ],
    [
        'id' => 2,
        'title' => 'UX Designer',
        'company' => 'Design Studio',
        'location' => 'Remote',
        'match_score' => 92,
        'posted_date' => '1 day ago',
        'salary' => '$65,000 - $80,000',
        'type' => 'Full-time'
    ],
    [
        'id' => 3,
        'title' => 'Junior Web Developer',
        'company' => 'WebSolutions',
        'location' => 'Chicago, IL',
        'match_score' => 88,
        'posted_date' => '3 days ago',
        'salary' => '$60,000 - $75,000',
        'type' => 'Full-time'
    ]
];

$upcomingEvents = [
    [
        'title' => 'Tech Career Fair',
        'date' => 'Oct 15, 2023',
        'time' => '10:00 AM - 4:00 PM',
        'location' => 'Virtual'
    ],
    [
        'title' => 'Resume Workshop',
        'date' => 'Oct 18, 2023',
        'time' => '2:00 PM - 3:30 PM',
        'location' => 'Virtual'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CareerMatch</title>
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
        .match-score {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: bold;
            font-size: 0.9rem;
            color: white;
        }
        .match-score.high {
            background-color: #28a745;
        }
        .match-score.medium {
            background-color: #ffc107;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
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
                            <a href="dashboard.php" class="sidebar-link active">
                                <i class="bi bi-house-door"></i> Home
                            </a>
                            <a href="profile.php" class="sidebar-link">
                                <i class="bi bi-person"></i> My Profile
                            </a>
                            <a href="resume.php" class="sidebar-link">
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
                        <h4 class="fw-bold mb-0">Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h4>
                        <div class="d-flex gap-2">
                            <a href="job-matches.php" class="btn btn-primary">
                                <i class="bi bi-briefcase me-2"></i> View All Job Matches
                            </a>
                        </div>
                    </div>
                    
                    <!-- Profile Completion -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="fw-bold">Complete Your Profile</h5>
                                    <p class="text-muted mb-2">Your profile is <?php echo $user['profile_completion']; ?>% complete</p>
                                    <div class="progress mb-3" style="height: 10px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $user['profile_completion']; ?>%"></div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php if (!$user['resume_uploaded']): ?>
                                            <a href="resume.php" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-upload me-1"></i> Upload Resume
                                            </a>
                                        <?php endif; ?>
                                        <a href="profile.php" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil me-1"></i> Complete Profile
                                        </a>
                                        <a href="skills.php" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-check-circle me-1"></i> Verify Skills
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center d-none d-md-block">
                                    <img src="https://via.placeholder.com/150" alt="Profile completion" class="img-fluid" style="max-height: 120px;">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Stats Row -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                            <i class="bi bi-briefcase text-primary fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0"><?php echo $user['job_matches']; ?></h6>
                                            <p class="text-muted mb-0">Job Matches</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                            <i class="bi bi-send text-success fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0"><?php echo $user['applications']; ?></h6>
                                            <p class="text-muted mb-0">Applications</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                                            <i class="bi bi-bookmark text-warning fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0"><?php echo $user['saved_jobs']; ?></h6>
                                            <p class="text-muted mb-0">Saved Jobs</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Top Job Matches -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0">Top Job Matches</h5>
                                <a href="job-matches.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <?php foreach ($recentJobs as $job): ?>
                                    <div class="col-md-4">
                                        <div class="card h-100 card-hover">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between mb-3">
                                                    <div>
                                                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($job['title']); ?></h5>
                                                        <p class="text-muted mb-0"><?php echo htmlspecialchars($job['company']); ?></p>
                                                    </div>
                                                    <div class="match-score <?php echo $job['match_score'] >= 90 ? 'high' : 'medium'; ?>">
                                                        <?php echo $job['match_score']; ?>%
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="bi bi-geo-alt me-2 text-secondary"></i>
                                                        <span><?php echo htmlspecialchars($job['location']); ?></span>
                                                    </div>
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="bi bi-cash me-2 text-secondary"></i>
                                                        <span><?php echo htmlspecialchars($job['salary']); ?></span>
                                                    </div>
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="bi bi-briefcase me-2 text-secondary"></i>
                                                        <span><?php echo htmlspecialchars($job['type']); ?></span>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-clock me-2 text-secondary"></i>
                                                        <span>Posted <?php echo htmlspecialchars($job['posted_date']); ?></span>
                                                    </div>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <a href="job-details.php?id=<?php echo $job['id']; ?>" class="btn btn-primary flex-grow-1">View Job</a>
                                                    <button class="btn btn-outline-primary"><i class="bi bi-bookmark"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-4">
                        <!-- Skills Section -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0">Your Skills</h5>
                                        <a href="skills.php" class="btn btn-sm btn-outline-primary">Manage Skills</a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach ($user['skills_extracted'] as $skill): ?>
                                            <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($skill); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="mt-3">
                                        <p class="text-muted small">These skills were extracted from your resume. You can add or remove skills to improve your job matches.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Upcoming Events -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0">Upcoming Events</h5>
                                        <a href="events.php" class="btn btn-sm btn-outline-primary">View All</a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($upcomingEvents as $event): ?>
                                        <div class="d-flex mb-3">
                                            <div class="me-3 text-center">
                                                <div class="bg-light rounded p-2" style="min-width: 60px;">
                                                    <div class="small text-muted"><?php echo explode(' ', $event['date'])[0]; ?></div>
                                                    <div class="fw-bold"><?php echo explode(' ', $event['date'])[1]; ?></div>
                                                </div>
                                            </div>
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($event['title']); ?></h6>
                                                <div class="small text-muted mb-1">
                                                    <i class="bi bi-clock me-1"></i> <?php echo htmlspecialchars($event['time']); ?>
                                                </div>
                                                <div class="small text-muted">
                                                    <i class="bi bi-geo-alt me-1"></i> <?php echo htmlspecialchars($event['location']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <a href="events.php" class="btn btn-sm btn-outline-primary w-100">Register for Events</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

