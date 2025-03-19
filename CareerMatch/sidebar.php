<?php
// Get the current page filename
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
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
</style>

<div class="sidebar py-4">
    <div class="px-4 mb-4">
        <h5 class="fw-bold mb-0">Dashboard</h5>
    </div>
    <div class="px-3">
        <a href="dashboard.php" class="sidebar-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
            <i class="bi bi-house-door"></i> Home
        </a>
        <a href="profile.php" class="sidebar-link <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>">
            <i class="bi bi-person"></i> My Profile
        </a>
        <a href="resume.php" class="sidebar-link <?php echo $current_page === 'resume.php' ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-text"></i> Resume
        </a>
        <a href="job-matches.php?search=true" class="sidebar-link <?php echo $current_page === 'job-matches.php' ? 'active' : ''; ?>">
            <i class="bi bi-briefcase"></i> Job Matches
        </a>
        <a href="applications.php" class="sidebar-link <?php echo $current_page === 'applications.php' ? 'active' : ''; ?>">
            <i class="bi bi-send"></i> Applications
        </a>
        <a href="saved-jobs.php" class="sidebar-link <?php echo $current_page === 'saved-jobs.php' ? 'active' : ''; ?>">
            <i class="bi bi-bookmark"></i> Saved Jobs
        </a>
        <a href="messages.php" class="sidebar-link <?php echo $current_page === 'messages.php' ? 'active' : ''; ?>">
            <i class="bi bi-chat"></i> Messages
        </a>
        <a href="settings.php" class="sidebar-link <?php echo $current_page === 'settings.php' ? 'active' : ''; ?>">
            <i class="bi bi-gear"></i> Settings
        </a>
        <hr>
        <a href="logout.php" class="sidebar-link text-danger">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</div> 