<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'includes/db_connect.php';

// Get saved jobs for the user
$stmt = $conn->prepare("SELECT * FROM saved_jobs WHERE user_id = ? ORDER BY saved_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$saved_jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Store saved jobs in session for job details page
$_SESSION['saved_jobs'] = $saved_jobs;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Jobs - CareerMatch</title>
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
        .job-description {
            color: #6c757d;
            font-size: 0.95rem;
            line-height: 1.6;
            margin: 1rem 0;
            position: relative;
            transition: max-height 0.3s ease-out;
        }
        .job-description.collapsed {
            max-height: 4.8rem;
            overflow: hidden;
        }
        .job-description.collapsed::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 100%;
            height: 20px;
            background: linear-gradient(180deg, transparent, white);
        }
        .show-more-btn {
            color: #6a11cb;
            background: none;
            border: none;
            padding: 0;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            margin-top: 0.5rem;
        }
        .show-more-btn:hover {
            color: #2575fc;
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
        .action-buttons .btn-outline-danger {
            color: #dc3545;
            border-color: rgba(220, 53, 69, 0.2);
        }
        .action-buttons .btn-outline-danger:hover {
            background: linear-gradient(135deg, #dc3545 0%, #ff4757 100%);
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
                            <h4 class="fw-bold mb-1">Saved Jobs</h4>
                            <p class="text-muted mb-0">You have <?php echo count($saved_jobs); ?> saved jobs</p>
                        </div>
                    </div>

                    <?php if (empty($saved_jobs)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-bookmark display-1 text-muted mb-3"></i>
                        <h5>No Saved Jobs</h5>
                        <p class="text-muted">Jobs you save will appear here</p>
                        <a href="job-matches.php" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i> Find Jobs
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($saved_jobs as $index => $job): ?>
                        <div class="col-lg-6">
                            <div class="card job-card h-100">
                                <div class="card-body">
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
                                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($job['job_title']); ?></h5>
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
                                    <div class="job-description collapsed" id="description-<?php echo $index; ?>">
                                        <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                                    </div>
                                    <button class="show-more-btn" onclick="toggleDescription(<?php echo $index; ?>)">
                                        <span class="text">Show More</span>
                                        <i class="bi bi-chevron-down"></i>
                                    </button>

                                    <!-- Action Buttons -->
                                    <div class="action-buttons">
                                        <a href="job-details.php?job_id=<?php echo $job['id']; ?>&source=saved" 
                                           class="btn btn-primary flex-grow-1">
                                            <i class="bi bi-eye me-1"></i> View Details
                                        </a>
                                        <button class="btn btn-outline-danger" onclick="removeJob(<?php echo $job['id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function removeJob(jobId) {
            if (confirm('Are you sure you want to remove this job from your saved jobs?')) {
                fetch('includes/remove_job.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'job_id=' + jobId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error removing job');
                });
            }
        }

        function toggleDescription(index) {
            const description = document.getElementById(`description-${index}`);
            const button = description.nextElementSibling;
            const text = button.querySelector('.text');
            const icon = button.querySelector('.bi');

            if (description.classList.contains('collapsed')) {
                description.classList.remove('collapsed');
                text.textContent = 'Show Less';
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-up');
            } else {
                description.classList.add('collapsed');
                text.textContent = 'Show More';
                icon.classList.remove('bi-chevron-up');
                icon.classList.add('bi-chevron-down');
            }
        }
    </script>
</body>
</html> 