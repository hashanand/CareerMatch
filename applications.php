<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Initialize Python application tracker
function get_application_insights($user_id) {
    $command = escapeshellcmd("python application_tracker.py --user_id " . escapeshellarg($user_id) . " --action get_insights");
    $output = shell_exec($command);
    return json_decode($output, true);
}

$user_id = $_SESSION['user_id'];
$insights = get_application_insights($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Tracking - CareerMatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.css">
    <style>
        .dashboard-container {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .metric-card {
            border-radius: 15px;
            transition: transform 0.2s;
        }
        .metric-card:hover {
            transform: translateY(-5px);
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
        .recommendation-card {
            border-left: 4px solid #6a11cb;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="dashboard-container">
        <div class="container-fluid py-4">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-2">
                    <?php include 'sidebar.php'; ?>
                </div>

                <!-- Main Content -->
                <div class="col-lg-10">
                    <div class="row mb-4">
                        <div class="col-12">
                            <h2 class="fw-bold">Application Tracking</h2>
                            <p class="text-muted">Real-time insights and analytics for your job applications</p>
                        </div>
                    </div>

                    <!-- Key Metrics -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="card metric-card bg-primary text-white h-100">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2">Total Applications</h6>
                                    <h2 class="card-title mb-0"><?php echo $insights['total_applications']; ?></h2>
                                    <div class="mt-2">
                                        <small>Applications submitted</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card metric-card bg-success text-white h-100">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2">Success Rate</h6>
                                    <h2 class="card-title mb-0"><?php echo number_format($insights['success_rate'], 1); ?>%</h2>
                                    <div class="mt-2">
                                        <small>Applications accepted</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card metric-card bg-info text-white h-100">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2">Interview Rate</h6>
                                    <h2 class="card-title mb-0"><?php echo $insights['interview_scheduled']; ?></h2>
                                    <div class="mt-2">
                                        <small>Interviews scheduled</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card metric-card bg-warning text-white h-100">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2">Pending</h6>
                                    <h2 class="card-title mb-0"><?php echo $insights['pending_applications']; ?></h2>
                                    <div class="mt-2">
                                        <small>Awaiting response</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-8">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Application Trends</h5>
                                    <div class="chart-container">
                                        <canvas id="applicationTrends"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Best Performing Categories</h5>
                                    <div class="chart-container">
                                        <canvas id="categoryPerformance"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ML Insights -->
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-lightbulb-fill text-warning me-2"></i>
                                        AI-Powered Recommendations
                                    </h5>
                                    <?php foreach ($insights['recommended_improvements'] as $recommendation): ?>
                                    <div class="card recommendation-card mb-3">
                                        <div class="card-body">
                                            <h6 class="card-subtitle mb-2 text-primary"><?php echo htmlspecialchars($recommendation['area']); ?></h6>
                                            <p class="card-text"><?php echo htmlspecialchars($recommendation['recommendation']); ?></p>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-graph-up-arrow text-success me-2"></i>
                                        Success Probability Analysis
                                    </h5>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Category</th>
                                                    <th>Success Rate</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($insights['best_performing_categories'] as $category): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($category['category']); ?></td>
                                                    <td>
                                                        <div class="progress">
                                                            <div class="progress-bar bg-success" role="progressbar" 
                                                                 style="width: <?php echo $category['success_rate']; ?>%">
                                                                <?php echo number_format($category['success_rate'], 1); ?>%
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Application Trends Chart
        const trendsCtx = document.getElementById('applicationTrends').getContext('2d');
        const trendsData = <?php echo json_encode($insights['application_trends']); ?>;
        
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: trendsData.map(item => item.date),
                datasets: [
                    {
                        label: 'Total Applications',
                        data: trendsData.map(item => item.applications),
                        borderColor: '#6a11cb',
                        tension: 0.4
                    },
                    {
                        label: 'Accepted',
                        data: trendsData.map(item => item.accepted),
                        borderColor: '#2575fc',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });

        // Category Performance Chart
        const categoryCtx = document.getElementById('categoryPerformance').getContext('2d');
        const categoryData = <?php echo json_encode($insights['best_performing_categories']); ?>;
        
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categoryData.map(item => item.category),
                datasets: [{
                    data: categoryData.map(item => item.success_rate),
                    backgroundColor: ['#6a11cb', '#2575fc', '#00ff87']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html> 