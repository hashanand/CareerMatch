<?php
session_start();

// In a real application, you would fetch this data from a database
$jobs = [
    [
        'id' => 1,
        'title' => 'Junior Software Developer',
        'company' => 'TechCorp',
        'location' => 'New York, NY',
        'salary' => '$70,000 - $85,000',
        'type' => 'Full-time',
        'posted_date' => '2 days ago',
        'description' => 'We are looking for a Junior Software Developer to join our growing team. The ideal candidate has knowledge of web development technologies and is eager to learn and grow in a collaborative environment.',
        'requirements' => [
            'Bachelor\'s degree in Computer Science or related field',
            'Knowledge of JavaScript, HTML, and CSS',
            'Familiarity with React or similar frameworks is a plus',
            'Strong problem-solving skills',
            'Good communication skills'
        ],
        'tags' => ['JavaScript', 'React', 'HTML', 'CSS', 'Entry-level']
    ],
    [
        'id' => 2,
        'title' => 'Marketing Associate',
        'company' => 'Brand Solutions',
        'location' => 'Chicago, IL',
        'salary' => '$55,000 - $65,000',
        'type' => 'Full-time',
        'posted_date' => '1 day ago',
        'description' => 'Brand Solutions is seeking a Marketing Associate to support our marketing team in creating and implementing marketing campaigns for our clients. This is an excellent opportunity for a recent graduate to gain hands-on experience in marketing.',
        'requirements' => [
            'Bachelor\'s degree in Marketing, Communications, or related field',
            'Strong written and verbal communication skills',
            'Knowledge of social media platforms',
            'Basic understanding of SEO and digital marketing',
            'Creativity and attention to detail'
        ],
        'tags' => ['Marketing', 'Social Media', 'Digital Marketing', 'Entry-level']
    ],
    [
        'id' => 3,
        'title' => 'Data Analyst',
        'company' => 'Analytics Pro',
        'location' => 'San Francisco, CA',
        'salary' => '$75,000 - $90,000',
        'type' => 'Full-time',
        'posted_date' => '3 days ago',
        'description' => 'Analytics Pro is looking for a Data Analyst to join our team. The ideal candidate will have strong analytical skills and be able to translate data into actionable insights for our clients.',
        'requirements' => [
            'Bachelor\'s degree in Statistics, Mathematics, Computer Science, or related field',
            'Proficiency in SQL and Excel',
            'Experience with data visualization tools (e.g., Tableau, Power BI)',
            'Knowledge of Python or R is a plus',
            'Strong analytical and problem-solving skills'
        ],
        'tags' => ['Data Analysis', 'SQL', 'Excel', 'Tableau', 'Entry-level']
    ],
    [
        'id' => 4,
        'title' => 'UX/UI Designer',
        'company' => 'Design Studio',
        'location' => 'Remote',
        'salary' => '$65,000 - $80,000',
        'type' => 'Full-time',
        'posted_date' => '5 days ago',
        'description' => 'Design Studio is seeking a talented UX/UI Designer to create amazing user experiences. The ideal candidate has a good eye for design and understands user-centered design principles.',
        'requirements' => [
            'Bachelor\'s degree in Design, HCI, or related field',
            'Portfolio demonstrating UX/UI design skills',
            'Proficiency in design tools (e.g., Figma, Sketch, Adobe XD)',
            'Understanding of user-centered design principles',
            'Good communication and collaboration skills'
        ],
        'tags' => ['UX Design', 'UI Design', 'Figma', 'User Research', 'Entry-level']
    ],
    [
        'id' => 5,
        'title' => 'Financial Analyst',
        'company' => 'Global Finance',
        'location' => 'Boston, MA',
        'salary' => '$65,000 - $75,000',
        'type' => 'Full-time',
        'posted_date' => '1 week ago',
        'description' => 'Global Finance is looking for a Financial Analyst to join our team. The ideal candidate will have strong analytical skills and be able to analyze financial data to support business decisions.',
        'requirements' => [
            'Bachelor\'s degree in Finance, Accounting, Economics, or related field',
            'Strong analytical and quantitative skills',
            'Proficiency in Excel and financial modeling',
            'Knowledge of financial analysis and accounting principles',
            'Attention to detail and accuracy'
        ],
        'tags' => ['Finance', 'Financial Analysis', 'Excel', 'Entry-level']
    ],
    [
        'id' => 6,
        'title' => 'Human Resources Assistant',
        'company' => 'People First',
        'location' => 'Austin, TX',
        'salary' => '$50,000 - $60,000',
        'type' => 'Full-time',
        'posted_date' => '4 days ago',
        'description' => 'People First is seeking an HR Assistant to support our HR team in various administrative and operational tasks. This is an excellent opportunity for a recent graduate to start a career in HR.',
        'requirements' => [
            'Bachelor\'s degree in Human Resources, Business Administration, or related field',
            'Strong organizational and administrative skills',
            'Excellent communication and interpersonal skills',
            'Ability to maintain confidentiality',
            'Attention to detail and accuracy'
        ],
        'tags' => ['HR', 'Human Resources', 'Administration', 'Entry-level']
    ]
];

// Filter options
$locations = ['New York, NY', 'Chicago, IL', 'San Francisco, CA', 'Remote', 'Boston, MA', 'Austin, TX'];
$jobTypes = ['Full-time', 'Part-time', 'Contract', 'Internship'];
$industries = ['Technology', 'Marketing', 'Finance', 'Design', 'Healthcare', 'Education'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Jobs - CareerMatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .job-card {
            border-radius: 10px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .filter-sidebar {
            position: sticky;
            top: 20px;
        }
        .company-logo {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            border-radius: 10px;
            font-size: 1.5rem;
            color: #6a11cb;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2 class="fw-bold mb-0">Browse Jobs</h2>
                <p class="text-muted">Find the perfect job opportunity for your career</p>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search jobs...">
                    <button class="btn btn-primary" type="button">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Filter Sidebar -->
            <div class="col-lg-3">
                <div class="card filter-sidebar">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Filters</h5>
                        
                        <div class="mb-3">
                            <label class="form-label fw-medium">Location</label>
                            <select class="form-select">
                                <option value="">All Locations</option>
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?php echo htmlspecialchars($location); ?>"><?php echo htmlspecialchars($location); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-medium">Job Type</label>
                            <?php foreach ($jobTypes as $type): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="<?php echo strtolower(str_replace(' ', '-', $type)); ?>">
                                    <label class="form-check-label" for="<?php echo strtolower(str_replace(' ', '-', $type)); ?>">
                                        <?php echo htmlspecialchars($type); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-medium">Industry</label>
                            <select class="form-select">
                                <option value="">All Industries</option>
                                <?php foreach ($industries as $industry): ?>
                                    <option value="<?php echo htmlspecialchars($industry); ?>"><?php echo htmlspecialchars($industry); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-medium">Salary Range</label>
                            <select class="form-select">
                                <option value="">Any Salary</option>
                                <option value="0-50000">$0 - $50,000</option>
                                <option value="50000-70000">$50,000 - $70,000</option>
                                <option value="70000-90000">$70,000 - $90,000</option>
                                <option value="90000-120000">$90,000 - $120,000</option>
                                <option value="120000+">$120,000+</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-medium">Experience Level</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="entry-level" checked>
                                <label class="form-check-label" for="entry-level">
                                    Entry Level
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="mid-level">
                                <label class="form-check-label" for="mid-level">
                                    Mid Level
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="senior-level">
                                <label class="form-check-label" for="senior-level">
                                    Senior Level
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary">Apply Filters</button>
                            <button class="btn btn-outline-secondary">Reset Filters</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Job Listings -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <p class="mb-0"><?php echo count($jobs); ?> jobs found</p>
                    <div class="d-flex align-items-center">
                        <label class="me-2">Sort by:</label>
                        <select class="form-select form-select-sm" style="width: auto;">
                            <option value="relevance">Relevance</option>
                            <option value="date">Date Posted</option>
                            <option value="salary-high">Salary (High to Low)</option>
                            <option value="salary-low">Salary (Low to High)</option>
                        </select>
                    </div>
                </div>
                
                <div class="row g-4">
                    <?php foreach ($jobs as $job): ?>
                        <div class="col-md-6">
                            <div class="card job-card h-100">
                                <div class="card-body">
                                    <div class="d-flex mb-3">
                                        <div class="company-logo me-3">
                                            <?php echo substr($job['company'], 0, 1); ?>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($job['title']); ?></h5>
                                            <p class="text-muted mb-0"><?php echo htmlspecialchars($job['company']); ?></p>
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
                                    
                                    <div class="mb-3">
                                        <?php foreach (array_slice($job['tags'], 0, 3) as $tag): ?>
                                            <span class="badge bg-light text-dark me-1"><?php echo htmlspecialchars($tag); ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($job['tags']) > 3): ?>
                                            <span class="badge bg-light text-dark">+<?php echo count($job['tags']) - 3; ?> more</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <a href="job-details.php?id=<?php echo $job['id']; ?>" class="btn btn-primary flex-grow-1">View Details</a>
                                        <button class="btn btn-outline-primary"><i class="bi bi-bookmark"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

