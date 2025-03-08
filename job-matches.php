<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'includes/job_search.php';

// Check if we need to fetch new jobs
if (isset($_GET['search']) && isset($_SESSION['resume_data']['skills'])) {
    $jobs = fetchJobsFromSerpApi($_SESSION['resume_data']['skills']);
    saveJobsToSession($jobs);
}

// Get jobs from session
$jobMatches = $_SESSION['job_matches'] ?? null;

// Get filter and sort parameters
$location_filter = $_GET['location'] ?? '';
$salary_filter = $_GET['salary'] ?? '';
$job_type_filter = $_GET['job_type'] ?? '';
$skills_match_filter = $_GET['skills_match'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'skills_match';
$sort_order = $_GET['sort_order'] ?? 'desc';

// Filter and sort jobs if they exist
if ($jobMatches && !empty($jobMatches['jobs'])) {
    $filtered_jobs = $jobMatches['jobs'];
    
    // Apply filters
    if (!empty($location_filter)) {
        $filtered_jobs = array_filter($filtered_jobs, function($job) use ($location_filter) {
            return stripos($job['location'], $location_filter) !== false;
        });
    }
    
    if (!empty($salary_filter)) {
        $filtered_jobs = array_filter($filtered_jobs, function($job) use ($salary_filter) {
            if ($salary_filter === 'specified') {
                return !empty($job['salary']) && $job['salary'] !== 'Salary not specified';
            }
            return true;
        });
    }
    
    if (!empty($job_type_filter)) {
        $filtered_jobs = array_filter($filtered_jobs, function($job) use ($job_type_filter) {
            return stripos($job['job_type'], $job_type_filter) !== false;
        });
    }
    
    if (!empty($skills_match_filter)) {
        $min_match = (int)$skills_match_filter;
        $filtered_jobs = array_filter($filtered_jobs, function($job) use ($min_match) {
            return $job['skills_match'] >= $min_match;
        });
    }
    
    // Sort jobs
    usort($filtered_jobs, function($a, $b) use ($sort_by, $sort_order) {
        $result = 0;
        switch ($sort_by) {
            case 'skills_match':
                $result = $b['skills_match'] <=> $a['skills_match'];
                break;
            case 'posted_date':
                $a_time = strtotime(str_replace(' ago', '', $a['posted_at']));
                $b_time = strtotime(str_replace(' ago', '', $b['posted_at']));
                $result = $b_time <=> $a_time;
                break;
            case 'company':
                $result = strcasecmp($a['company'], $b['company']);
                break;
            case 'title':
                $result = strcasecmp($a['title'], $b['title']);
                break;
        }
        return $sort_order === 'desc' ? $result : -$result;
    });
    
    // Update session with filtered and sorted jobs
    $jobMatches['jobs'] = $filtered_jobs;
    $jobMatches['total'] = count($filtered_jobs);
}

$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : null;

// Get unique locations and job types for filters
$locations = [];
$job_types = [];
if ($jobMatches && !empty($jobMatches['jobs'])) {
    foreach ($jobMatches['jobs'] as $job) {
        if (!empty($job['location'])) {
            $locations[trim($job['location'])] = true;
        }
        if (!empty($job['job_type'])) {
            $job_types[trim($job['job_type'])] = true;
        }
    }
    $locations = array_keys($locations);
    $job_types = array_keys($job_types);
    sort($locations);
    sort($job_types);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Matches - CareerMatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        .dashboard-container {
            background-color: #f8f9fa;
            min-height: 100vh;
            padding: 2rem 0;
        }
        .job-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: white;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }
        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        .company-logo {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f6f8ff 0%, #f1f3f9 100%);
            border-radius: 12px;
            font-size: 1.5rem;
            color: #6a11cb;
            box-shadow: 0 3px 10px rgba(106, 17, 203, 0.1);
        }
        .company-logo img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            padding: 8px;
        }
        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            line-height: 1.4;
        }
        .company-name {
            font-size: 0.95rem;
            color: #6c757d;
            font-weight: 500;
        }
        .job-meta {
            margin: 1.5rem 0;
        }
        .meta-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            color: #6c757d;
            font-size: 0.9rem;
        }
        .meta-item:last-child {
            border-bottom: none;
        }
        .meta-item i {
            width: 20px;
            margin-right: 8px;
            color: #6a11cb;
            font-size: 1rem;
        }
        .salary-badge {
            background-color: #e3f2fd;
            color: #0d6efd;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
        }
        .job-type-badge {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
        }
        .skills-match {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 1;
        }
        .skills-match .badge {
            font-size: 0.85rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            box-shadow: 0 3px 10px rgba(40, 167, 69, 0.2);
        }
        .job-description {
            color: #6c757d;
            font-size: 0.95rem;
            line-height: 1.6;
            margin: 1rem 0;
            position: relative;
            max-height: 4.8rem;
            overflow: hidden;
        }
        .job-description::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 100%;
            height: 20px;
            background: linear-gradient(180deg, transparent, white);
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        .action-buttons .btn {
            padding: 0.5rem 1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .action-buttons .btn-primary {
            flex-grow: 1;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            border: none;
            box-shadow: 0 3px 10px rgba(106, 17, 203, 0.2);
        }
        .action-buttons .btn-outline-primary {
            color: #6a11cb;
            border-color: rgba(106, 17, 203, 0.2);
        }
        .action-buttons .btn-outline-primary:hover {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            border-color: transparent;
            color: white;
        }
        .page-header h4 {
            font-size: 1.75rem;
            color: #2c3e50;
            font-weight: 700;
        }
        .page-header p {
            color: #6c757d;
            font-size: 1.1rem;
        }
        .header-buttons .btn {
            padding: 0.5rem 1.25rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="fw-bold mb-1">Job Matches</h4>
                            <?php if ($jobMatches): ?>
                            <p class="text-muted mb-0">Found <?php echo $jobMatches['total']; ?> jobs matching your skills</p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <a href="resume.php" class="btn btn-outline-primary me-2">
                                <i class="bi bi-file-earmark-text me-1"></i> View Resume
                            </a>
                            <a href="?search=true" class="btn btn-primary">
                                <i class="bi bi-arrow-clockwise me-1"></i> Refresh Results
                            </a>
                        </div>
                    </div>

                    <?php if (!isset($_SESSION['resume_data'])): ?>
                    <!-- No Resume Uploaded -->
                    <div class="alert alert-warning" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Please upload your resume first to see job matches.
                        <a href="resume.php" class="alert-link ms-2">Upload Resume</a>
                    </div>
                    <?php elseif (!$jobMatches): ?>
                    <!-- No Job Matches Yet -->
                    <div class="text-center py-5">
                        <i class="bi bi-search display-1 text-muted mb-3"></i>
                        <h5>No Job Matches Yet</h5>
                        <p class="text-muted">Click the button below to find jobs matching your skills</p>
                        <a href="?search=true" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i> Find Jobs
                        </a>
                    </div>
                    <?php else: ?>
                    <!-- Filters and Sorting -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form id="filterForm" class="row g-3">
                                <!-- Location Filter -->
                                <div class="col-md-3">
                                    <label for="location" class="form-label">Location</label>
                                    <select class="form-select" id="location" name="location">
                                        <option value="">All Locations</option>
                                        <?php foreach ($locations as $loc): ?>
                                        <option value="<?php echo htmlspecialchars($loc); ?>" 
                                                <?php echo $location_filter === $loc ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($loc); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Job Type Filter -->
                                <div class="col-md-3">
                                    <label for="job_type" class="form-label">Job Type</label>
                                    <select class="form-select" id="job_type" name="job_type">
                                        <option value="">All Types</option>
                                        <?php foreach ($job_types as $type): ?>
                                        <option value="<?php echo htmlspecialchars($type); ?>"
                                                <?php echo $job_type_filter === $type ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($type); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Salary Filter -->
                                <div class="col-md-3">
                                    <label for="salary" class="form-label">Salary</label>
                                    <select class="form-select" id="salary" name="salary">
                                        <option value="">All Salaries</option>
                                        <option value="specified" <?php echo $salary_filter === 'specified' ? 'selected' : ''; ?>>
                                            Salary Specified
                                        </option>
                                    </select>
                                </div>

                                <!-- Skills Match Filter -->
                                <div class="col-md-3">
                                    <label for="skills_match" class="form-label">Min. Skills Match</label>
                                    <select class="form-select" id="skills_match" name="skills_match">
                                        <option value="">Any Match</option>
                                        <option value="90" <?php echo $skills_match_filter === '90' ? 'selected' : ''; ?>>90% or higher</option>
                                        <option value="80" <?php echo $skills_match_filter === '80' ? 'selected' : ''; ?>>80% or higher</option>
                                        <option value="70" <?php echo $skills_match_filter === '70' ? 'selected' : ''; ?>>70% or higher</option>
                                        <option value="60" <?php echo $skills_match_filter === '60' ? 'selected' : ''; ?>>60% or higher</option>
                                        <option value="50" <?php echo $skills_match_filter === '50' ? 'selected' : ''; ?>>50% or higher</option>
                                    </select>
                                </div>

                                <!-- Sort Options -->
                                <div class="col-md-3">
                                    <label for="sort_by" class="form-label">Sort By</label>
                                    <select class="form-select" id="sort_by" name="sort_by">
                                        <option value="skills_match" <?php echo $sort_by === 'skills_match' ? 'selected' : ''; ?>>Skills Match</option>
                                        <option value="posted_date" <?php echo $sort_by === 'posted_date' ? 'selected' : ''; ?>>Posted Date</option>
                                        <option value="company" <?php echo $sort_by === 'company' ? 'selected' : ''; ?>>Company Name</option>
                                        <option value="title" <?php echo $sort_by === 'title' ? 'selected' : ''; ?>>Job Title</option>
                                    </select>
                                </div>

                                <!-- Sort Order -->
                                <div class="col-md-3">
                                    <label for="sort_order" class="form-label">Sort Order</label>
                                    <select class="form-select" id="sort_order" name="sort_order">
                                        <option value="desc" <?php echo $sort_order === 'desc' ? 'selected' : ''; ?>>Descending</option>
                                        <option value="asc" <?php echo $sort_order === 'asc' ? 'selected' : ''; ?>>Ascending</option>
                                    </select>
                                </div>

                                <!-- Filter Actions -->
                                <div class="col-md-6 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="bi bi-funnel me-1"></i> Apply Filters
                                    </button>
                                    <a href="?search=true" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle me-1"></i> Clear Filters
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Job Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Average Skills Match</h6>
                                    <?php
                                    $avg_match = 0;
                                    if (!empty($jobMatches['jobs'])) {
                                        $avg_match = array_reduce($jobMatches['jobs'], function($carry, $job) {
                                            return $carry + $job['skills_match'];
                                        }, 0) / count($jobMatches['jobs']);
                                    }
                                    ?>
                                    <h3 class="mb-0"><?php echo number_format($avg_match, 1); ?>%</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">High Match Jobs (>80%)</h6>
                                    <?php
                                    $high_matches = !empty($jobMatches['jobs']) ? count(array_filter($jobMatches['jobs'], function($job) {
                                        return $job['skills_match'] >= 80;
                                    })) : 0;
                                    ?>
                                    <h3 class="mb-0"><?php echo $high_matches; ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">With Salary Info</h6>
                                    <?php
                                    $with_salary = !empty($jobMatches['jobs']) ? count(array_filter($jobMatches['jobs'], function($job) {
                                        return !empty($job['salary']) && $job['salary'] !== 'Salary not specified';
                                    })) : 0;
                                    ?>
                                    <h3 class="mb-0"><?php echo $with_salary; ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <h6 class="card-title">Recent Jobs (24h)</h6>
                                    <?php
                                    $recent_jobs = !empty($jobMatches['jobs']) ? count(array_filter($jobMatches['jobs'], function($job) {
                                        return strpos($job['posted_at'], 'hour') !== false || 
                                               strpos($job['posted_at'], 'minute') !== false;
                                    })) : 0;
                                    ?>
                                    <h3 class="mb-0"><?php echo $recent_jobs; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Job Listings -->
                    <div class="row g-4">
                        <?php foreach ($jobMatches['jobs'] as $index => $job): ?>
                        <div class="col-lg-6">
                            <div class="card job-card h-100">
                                <div class="card-body">
                                    <!-- Skills Match Badge and Progress -->
                                    <div class="skills-match">
                                        <div class="badge bg-success position-relative">
                                            <?php echo number_format($job['skills_match']); ?>% Match
                                            <div class="progress position-absolute bottom-0 start-0 w-100" style="height: 4px;">
                                                <div class="progress-bar bg-white" role="progressbar" 
                                                     style="width: <?php echo $job['skills_match']; ?>%" 
                                                     aria-valuenow="<?php echo $job['skills_match']; ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Company Info -->
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="company-logo me-3">
                                            <?php if (!empty($job['company_logo'])): ?>
                                                <img src="<?php echo htmlspecialchars($job['company_logo']); ?>" 
                                                     alt="<?php echo htmlspecialchars($job['company']); ?>"
                                                     class="img-fluid">
                                            <?php else: ?>
                                                <?php echo substr($job['company'], 0, 1); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($job['title']); ?></h5>
                                            <h6 class="company-name"><?php echo htmlspecialchars($job['company']); ?></h6>
                                        </div>
                                    </div>

                                    <!-- Job Meta -->
                                    <div class="job-meta">
                                        <div class="meta-item">
                                            <i class="bi bi-geo-alt"></i>
                                            <span><?php echo htmlspecialchars($job['location']); ?></span>
                                        </div>
                                        <?php if (!empty($job['salary'])): ?>
                                        <div class="meta-item">
                                            <i class="bi bi-cash"></i>
                                            <span class="salary-badge"><?php echo htmlspecialchars($job['salary']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($job['job_type'])): ?>
                                        <div class="meta-item">
                                            <i class="bi bi-briefcase"></i>
                                            <span class="job-type-badge"><?php echo htmlspecialchars($job['job_type']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <div class="meta-item">
                                            <i class="bi bi-clock"></i>
                                            <span>Posted <?php echo htmlspecialchars($job['posted_at']); ?></span>
                                        </div>
                                    </div>

                                    <!-- Job Description -->
                                    <div class="job-description">
                                        <?php 
                                        $description = $job['description'];
                                        $maxLength = 200;
                                        if (strlen($description) > $maxLength) {
                                            $description = substr($description, 0, $maxLength) . '...';
                                        }
                                        echo htmlspecialchars($description);
                                        ?>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="action-buttons">
                                        <a href="job-details.php?job_id=<?php echo $index; ?>&source=matches" 
                                           class="btn btn-primary flex-grow-1">
                                            <i class="bi bi-eye me-1"></i> View Details
                                        </a>
                                        <button class="btn btn-outline-primary" onclick='saveJob(<?php echo json_encode($job); ?>)'>
                                            <i class="bi bi-bookmark"></i>
                                        </button>
                                        <button class="btn btn-outline-info" onclick='compareWithResume(<?php echo json_encode($job); ?>)'>
                                            <i class="bi bi-file-diff"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Compare with Resume Modal -->
                    <div class="modal fade" id="compareModal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Compare with Your Resume</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div id="compareContent"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
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
        const saveBtn = event.target.closest('.btn');
        const originalText = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Saving...';

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
                saveBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Saved';
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
                saveBtn.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i> ' + (data.message || 'Error');
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
            saveBtn.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i> Error';
            setTimeout(() => {
                saveBtn.classList.remove('btn-danger');
                saveBtn.classList.add('btn-outline-primary');
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }, 3000);
        });
    }

    // Compare with Resume functionality
    function compareWithResume(job) {
        const compareModal = new bootstrap.Modal(document.getElementById('compareModal'));
        const compareContent = document.getElementById('compareContent');
        
        // Get resume data from session
        fetch('includes/get_resume_comparison.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ job: job })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = `
                    <div class="skills-comparison mb-4">
                        <h6 class="fw-bold mb-3">Skills Match Analysis</h6>
                        <div class="progress mb-2" style="height: 25px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: ${job.skills_match}%" 
                                 aria-valuenow="${job.skills_match}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                ${job.skills_match}% Match
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Your Skills</h6>
                            <ul class="list-group">
                                ${data.resume_skills.map(skill => `
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        ${skill}
                                        ${data.matched_skills.includes(skill) ? 
                                            '<span class="badge bg-success rounded-pill"><i class="bi bi-check"></i></span>' : 
                                            '<span class="badge bg-secondary rounded-pill"><i class="bi bi-dash"></i></span>'}
                                    </li>
                                `).join('')}
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Required Skills</h6>
                            <ul class="list-group">
                                ${data.job_skills.map(skill => `
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        ${skill}
                                        ${data.resume_skills.includes(skill) ? 
                                            '<span class="badge bg-success rounded-pill"><i class="bi bi-check"></i></span>' : 
                                            '<span class="badge bg-warning text-dark rounded-pill"><i class="bi bi-exclamation"></i></span>'}
                                    </li>
                                `).join('')}
                            </ul>
                        </div>
                    </div>
                    
                    <div class="recommendations mt-4">
                        <h6 class="fw-bold mb-3">Recommendations</h6>
                        <ul class="list-group">
                            ${data.recommendations.map(rec => `
                                <li class="list-group-item">
                                    <i class="bi bi-lightbulb text-warning me-2"></i>
                                    ${rec}
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                `;
                compareContent.innerHTML = html;
            } else {
                compareContent.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${data.message || 'Error loading comparison data'}
                    </div>
                `;
            }
            compareModal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            compareContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Error loading comparison data
                </div>
            `;
            compareModal.show();
        });
    }

    // Filter and sort functionality
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.getElementById('filterForm');
        const formInputs = filterForm.querySelectorAll('select');
        
        // Save filter preferences to localStorage
        function saveFilterPreferences() {
            const preferences = {};
            formInputs.forEach(input => {
                preferences[input.name] = input.value;
            });
            localStorage.setItem('jobMatchesFilters', JSON.stringify(preferences));
        }
        
        // Load filter preferences from localStorage
        function loadFilterPreferences() {
            const preferences = JSON.parse(localStorage.getItem('jobMatchesFilters') || '{}');
            formInputs.forEach(input => {
                if (preferences[input.name] && !input.value) {
                    input.value = preferences[input.name];
                }
            });
        }
        
        // Apply filters when form is submitted
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(filterForm);
            const queryString = new URLSearchParams(formData).toString();
            saveFilterPreferences();
            window.location.href = '?' + queryString;
        });
        
        // Real-time filtering (debounced)
        let filterTimeout;
        formInputs.forEach(input => {
            input.addEventListener('change', function() {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(() => {
                    filterForm.dispatchEvent(new Event('submit'));
                }, 500);
            });
        });
        
        // Load saved preferences on page load
        loadFilterPreferences();
        
        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Alt + F to focus on filters
            if (e.altKey && e.key === 'f') {
                e.preventDefault();
                document.getElementById('location').focus();
            }
            // Alt + S to focus on sort
            if (e.altKey && e.key === 's') {
                e.preventDefault();
                document.getElementById('sort_by').focus();
            }
            // Alt + C to clear filters
            if (e.altKey && e.key === 'c') {
                e.preventDefault();
                window.location.href = '?search=true';
            }
        });
        
        // Add tooltips for keyboard shortcuts
        const filterCard = document.querySelector('.card');
        if (filterCard) {
            const shortcutsHtml = `
                <div class="text-muted small mt-2">
                    Keyboard shortcuts: 
                    <span class="badge bg-light text-dark">Alt + F</span> Focus filters,
                    <span class="badge bg-light text-dark">Alt + S</span> Focus sort,
                    <span class="badge bg-light text-dark">Alt + C</span> Clear filters
                </div>
            `;
            filterCard.querySelector('.card-body').insertAdjacentHTML('beforeend', shortcutsHtml);
        }
        
        // Add filter summary
        function updateFilterSummary() {
            const activeFilters = [];
            formInputs.forEach(input => {
                if (input.value) {
                    const label = input.options[input.selectedIndex].text;
                    activeFilters.push(`${input.labels[0].textContent.replace(':', '')}: ${label}`);
                }
            });
            
            if (activeFilters.length > 0) {
                const summaryHtml = `
                    <div class="alert alert-info d-flex align-items-center mt-3" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        <div>
                            <strong>Active Filters:</strong> ${activeFilters.join(' | ')}
                        </div>
                    </div>
                `;
                filterCard.insertAdjacentHTML('afterend', summaryHtml);
            }
        }
        
        // Update filter summary on page load
        updateFilterSummary();
    });
    </script>
</body>
</html> 