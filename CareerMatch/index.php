<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareerMatch - Find Your Perfect Job</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 80px 0;
        }
        .feature-card {
            border-radius: 10px;
            transition: transform 0.3s ease;
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #6a11cb;
        }
        .testimonial-card {
            border-radius: 10px;
            border-left: 4px solid #6a11cb;
        }
        .footer {
            background-color: #212529;
            color: white;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Launch Your Career with CareerMatch</h1>
                    <p class="lead mb-4">Connecting fresh graduates with their dream jobs through AI-powered matching.</p>
                    <div class="d-flex gap-3">
                        <a href="register.php" class="btn btn-light btn-lg px-4">Get Started</a>
                        <a href="#how-it-works" class="btn btn-outline-light btn-lg px-4">Learn More</a>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <img src="https://via.placeholder.com/600x400" alt="Job Search" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="p-4 bg-white rounded shadow-sm">
                        <h2 class="fw-bold text-primary">5,000+</h2>
                        <p class="mb-0 text-muted">Job Opportunities</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4 bg-white rounded shadow-sm">
                        <h2 class="fw-bold text-primary">1,200+</h2>
                        <p class="mb-0 text-muted">Partner Companies</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4 bg-white rounded shadow-sm">
                        <h2 class="fw-bold text-primary">85%</h2>
                        <p class="mb-0 text-muted">Placement Rate</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">How CareerMatch Works</h2>
                <p class="text-muted">Our simple process to help you land your dream job</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-person-plus feature-icon"></i>
                            <h4>Create Your Profile</h4>
                            <p class="text-muted">Sign up and build your professional profile with your education, skills, and preferences.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-file-earmark-text feature-icon"></i>
                            <h4>Upload Your Resume</h4>
                            <p class="text-muted">Our AI analyzes your resume to extract key skills and qualifications automatically.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-briefcase feature-icon"></i>
                            <h4>Get Matched</h4>
                            <p class="text-muted">Receive personalized job recommendations based on your profile and market demand.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Jobs Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">Featured Opportunities</h2>
                <a href="jobs.php" class="btn btn-primary">View All Jobs</a>
            </div>
            <div class="row g-4">
                <?php
                // In a real application, these would come from a database
                $featuredJobs = [
                    [
                        'title' => 'Junior Software Developer',
                        'company' => 'TechCorp',
                        'location' => 'New York, NY',
                        'salary' => '$70,000 - $85,000',
                        'tags' => ['Full-time', 'Remote', 'Entry-level']
                    ],
                    [
                        'title' => 'Marketing Associate',
                        'company' => 'Brand Solutions',
                        'location' => 'Chicago, IL',
                        'salary' => '$55,000 - $65,000',
                        'tags' => ['Full-time', 'Hybrid', 'Entry-level']
                    ],
                    [
                        'title' => 'Data Analyst',
                        'company' => 'Analytics Pro',
                        'location' => 'San Francisco, CA',
                        'salary' => '$75,000 - $90,000',
                        'tags' => ['Full-time', 'On-site', 'Entry-level']
                    ]
                ];

                foreach ($featuredJobs as $job) {
                    echo '<div class="col-md-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title fw-bold">' . $job['title'] . '</h5>
                                <h6 class="card-subtitle mb-2 text-muted">' . $job['company'] . '</h6>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-geo-alt me-2 text-secondary"></i>
                                    <span>' . $job['location'] . '</span>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-cash me-2 text-secondary"></i>
                                    <span>' . $job['salary'] . '</span>
                                </div>
                                <div class="mb-3">';
                                
                    foreach ($job['tags'] as $tag) {
                        echo '<span class="badge bg-light text-dark me-2">' . $tag . '</span>';
                    }
                                
                    echo '</div>
                                <a href="job-details.php" class="btn btn-outline-primary w-100">View Details</a>
                            </div>
                        </div>
                    </div>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Success Stories</h2>
                <p class="text-muted">Hear from graduates who found their dream jobs</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card testimonial-card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <img src="https://via.placeholder.com/50" class="rounded-circle me-3" alt="User">
                                <div>
                                    <h5 class="mb-0">Sarah Johnson</h5>
                                    <p class="text-muted mb-0">Software Engineer at Google</p>
                                </div>
                            </div>
                            <p class="mb-0">"CareerMatch helped me find the perfect job that matched my skills and interests. The personalized recommendations were spot on!"</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card testimonial-card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <img src="https://via.placeholder.com/50" class="rounded-circle me-3" alt="User">
                                <div>
                                    <h5 class="mb-0">Michael Chen</h5>
                                    <p class="text-muted mb-0">Data Analyst at Amazon</p>
                                </div>
                            </div>
                            <p class="mb-0">"The skill matching feature was incredible. It highlighted strengths I didn't even know I had and connected me with my dream company."</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card testimonial-card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <img src="https://via.placeholder.com/50" class="rounded-circle me-3" alt="User">
                                <div>
                                    <h5 class="mb-0">Emily Rodriguez</h5>
                                    <p class="text-muted mb-0">Marketing Specialist at Adobe</p>
                                </div>
                            </div>
                            <p class="mb-0">"As a fresh graduate, I was overwhelmed by the job market. CareerMatch simplified everything and helped me land a role I love."</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="fw-bold mb-3">Ready to Start Your Career Journey?</h2>
            <p class="lead mb-4">Join thousands of graduates who found their perfect job match</p>
            <a href="register.php" class="btn btn-light btn-lg px-4">Sign Up Now</a>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

