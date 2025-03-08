<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// In a real application, you would fetch this data from a database
$user = [
    'name' => 'John Doe',
    'email' => 'john.doe@example.com',
    'phone' => '(123) 456-7890',
    'location' => 'New York, NY',
    'degree' => 'Bachelor of Science in Computer Science',
    'university' => 'State University',
    'graduation_year' => '2023',
    'bio' => 'Recent computer science graduate with a passion for web development and software engineering. Looking for opportunities to apply my skills in a dynamic environment.',
    'skills' => ['JavaScript', 'React', 'Node.js', 'HTML', 'CSS', 'UI/UX Design', 'Python', 'Java'],
    'experience' => [
        [
            'title' => 'Web Development Intern',
            'company' => 'Tech Solutions Inc.',
            'location' => 'New York, NY',
            'start_date' => 'May 2022',
            'end_date' => 'August 2022',
            'description' => 'Assisted in developing and maintaining company websites. Collaborated with the design team to implement responsive designs.'
        ],
        [
            'title' => 'Research Assistant',
            'company' => 'State University',
            'location' => 'New York, NY',
            'start_date' => 'January 2022',
            'end_date' => 'May 2022',
            'description' => 'Assisted professors with research projects related to machine learning algorithms. Collected and analyzed data for research papers.'
        ]
    ],
    'education' => [
        [
            'degree' => 'Bachelor of Science in Computer Science',
            'institution' => 'State University',
            'location' => 'New York, NY',
            'start_date' => 'September 2019',
            'end_date' => 'May 2023',
            'description' => 'GPA: 3.8/4.0. Relevant coursework: Data Structures, Algorithms, Database Systems, Web Development, Software Engineering.'
        ]
    ],
    'projects' => [
        [
            'title' => 'E-commerce Website',
            'description' => 'Developed a full-stack e-commerce website using React, Node.js, and MongoDB. Implemented features such as user authentication, product search, and shopping cart.',
            'link' => 'https://github.com/johndoe/ecommerce'
        ],
        [
            'title' => 'Weather App',
            'description' => 'Created a weather application using JavaScript and OpenWeatherMap API. Users can search for weather information by city name.',
            'link' => 'https://github.com/johndoe/weather-app'
        ]
    ]
];

// Handle form submission
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real application, you would validate and update the database
    $success = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - CareerMatch</title>
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
        .profile-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border-radius: 10px;
            padding: 2rem;
        }
        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid rgba(255, 255, 255, 0.3);
            object-fit: cover;
        }
        .nav-pills .nav-link {
            color: #495057;
        }
        .nav-pills .nav-link.active {
            background-color: #6a11cb;
            color: white;
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
                            <a href="profile.php" class="sidebar-link active">
                                <i class="bi bi-person"></i> My Profile
                            </a>
                            <a href="resume.php" class="sidebar-link">
                                <i class="bi bi-file-earmark-text"></i> Resume
                            </a>
                            <a href="job-matches.php" class="sidebar-link">
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
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            Your profile has been updated successfully!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Profile Header -->
                    <div class="profile-header mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                <img src="https://via.placeholder.com/150" alt="Profile" class="profile-img mb-3 mb-md-0">
                            </div>
                            <div class="col-md-8">
                                <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($user['name']); ?></h3>
                                <p class="mb-2"><?php echo htmlspecialchars($user['degree']); ?></p>
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-geo-alt me-2"></i>
                                        <span><?php echo htmlspecialchars($user['location']); ?></span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-envelope me-2"></i>
                                        <span><?php echo htmlspecialchars($user['email']); ?></span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-telephone me-2"></i>
                                        <span><?php echo htmlspecialchars($user['phone']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 text-md-end mt-3 mt-md-0">
                                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                    <i class="bi bi-pencil me-2"></i> Edit Profile
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Profile Content -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <ul class="nav nav-pills mb-4" id="profileTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="about  role="presentation">
                                    <button class="nav-link active" id="about-tab" data-bs-toggle="tab" data-bs-target="#about" type="button" role="tab" aria-controls="about" aria-selected="true">About</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="experience-tab" data-bs-toggle="tab" data-bs-target="#experience" type="button" role="tab" aria-controls="experience" aria-selected="false">Experience</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="education-tab" data-bs-toggle="tab" data-bs-target="#education" type="button" role="tab" aria-controls="education" aria-selected="false">Education</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="projects-tab" data-bs-toggle="tab" data-bs-target="#projects" type="button" role="tab" aria-controls="projects" aria-selected="false">Projects</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="skills-tab" data-bs-toggle="tab" data-bs-target="#skills" type="button" role="tab" aria-controls="skills" aria-selected="false">Skills</button>
                                </li>
                            </ul>
                            
                            <div class="tab-content" id="profileTabContent">
                                <!-- About Tab -->
                                <div class="tab-pane fade show active" id="about" role="tabpanel" aria-labelledby="about-tab">
                                    <h5 class="fw-bold mb-3">About Me</h5>
                                    <p><?php echo htmlspecialchars($user['bio']); ?></p>
                                </div>
                                
                                <!-- Experience Tab -->
                                <div class="tab-pane fade" id="experience" role="tabpanel" aria-labelledby="experience-tab">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="fw-bold mb-0">Work Experience</h5>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addExperienceModal">
                                            <i class="bi bi-plus"></i> Add Experience
                                        </button>
                                    </div>
                                    
                                    <?php foreach ($user['experience'] as $experience): ?>
                                        <div class="card mb-3 border-0 shadow-sm">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($experience['title']); ?></h5>
                                                    <div>
                                                        <button class="btn btn-sm btn-link text-primary"><i class="bi bi-pencil"></i></button>
                                                        <button class="btn btn-sm btn-link text-danger"><i class="bi bi-trash"></i></button>
                                                    </div>
                                                </div>
                                                <p class="text-muted mb-2">
                                                    <?php echo htmlspecialchars($experience['company']); ?> • 
                                                    <?php echo htmlspecialchars($experience['location']); ?>
                                                </p>
                                                <p class="text-muted small mb-3">
                                                    <?php echo htmlspecialchars($experience['start_date']); ?> - 
                                                    <?php echo htmlspecialchars($experience['end_date']); ?>
                                                </p>
                                                <p class="mb-0"><?php echo htmlspecialchars($experience['description']); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <!-- Education Tab -->
                                <div class="tab-pane fade" id="education" role="tabpanel" aria-labelledby="education-tab">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="fw-bold mb-0">Education</h5>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addEducationModal">
                                            <i class="bi bi-plus"></i> Add Education
                                        </button>
                                    </div>
                                    
                                    <?php foreach ($user['education'] as $education): ?>
                                        <div class="card mb-3 border-0 shadow-sm">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($education['degree']); ?></h5>
                                                    <div>
                                                        <button class="btn btn-sm btn-link text-primary"><i class="bi bi-pencil"></i></button>
                                                        <button class="btn btn-sm btn-link text-danger"><i class="bi bi-trash"></i></button>
                                                    </div>
                                                </div>
                                                <p class="text-muted mb-2">
                                                    <?php echo htmlspecialchars($education['institution']); ?> • 
                                                    <?php echo htmlspecialchars($education['location']); ?>
                                                </p>
                                                <p class="text-muted small mb-3">
                                                    <?php echo htmlspecialchars($education['start_date']); ?> - 
                                                    <?php echo htmlspecialchars($education['end_date']); ?>
                                                </p>
                                                <p class="mb-0"><?php echo htmlspecialchars($education['description']); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <!-- Projects Tab -->
                                <div class="tab-pane fade" id="projects" role="tabpanel" aria-labelledby="projects-tab">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="fw-bold mb-0">Projects</h5>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addProjectModal">
                                            <i class="bi bi-plus"></i> Add Project
                                        </button>
                                    </div>
                                    
                                    <div class="row g-4">
                                        <?php foreach ($user['projects'] as $project): ?>
                                            <div class="col-md-6">
                                                <div class="card h-100 border-0 shadow-sm">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between">
                                                            <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($project['title']); ?></h5>
                                                            <div>
                                                                <button class="btn btn-sm btn-link text-primary"><i class="bi bi-pencil"></i></button>
                                                                <button class="btn btn-sm btn-link text-danger"><i class="bi bi-trash"></i></button>
                                                            </div>
                                                        </div>
                                                        <p class="mb-3"><?php echo htmlspecialchars($project['description']); ?></p>
                                                        <a href="<?php echo htmlspecialchars($project['link']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-link-45deg me-1"></i> View Project
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <!-- Skills Tab -->
                                <div class="tab-pane fade" id="skills" role="tabpanel" aria-labelledby="skills-tab">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="fw-bold mb-0">Skills</h5>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addSkillModal">
                                            <i class="bi bi-plus"></i> Add Skill
                                        </button>
                                    </div>
                                    
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <?php foreach ($user['skills'] as $skill): ?>
                                            <div class="badge bg-light text-dark border p-2 d-flex align-items-center">
                                                <?php echo htmlspecialchars($skill); ?>
                                                <button class="btn btn-sm text-danger ms-2 p-0"><i class="bi bi-x"></i></button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <p class="text-muted small">
                                        <i class="bi bi-info-circle me-1"></i> 
                                        Adding relevant skills helps employers find you and improves your job matches.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($user['location']); ?>">
                            </div>
                            <div class="col-12">
                                <label for="bio" class="form-label">Bio</label>
                                <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="university" class="form-label">University</label>
                                <input type="text" class="form-control" id="university" name="university" value="<?php echo htmlspecialchars($user['university']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="degree" class="form-label">Degree</label>
                                <input type="text" class="form-control" id="degree" name="degree" value="<?php echo htmlspecialchars($user['degree']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="graduation_year" class="form-label">Graduation Year</label>
                                <input type="text" class="form-control" id="graduation_year" name="graduation_year" value="<?php echo htmlspecialchars($user['graduation_year']); ?>">
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

