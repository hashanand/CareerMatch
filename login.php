<?php
require_once 'includes/session_manager.php';
require_once 'includes/functions.php';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
} elseif (!isset($_SESSION['user_id']) && validateRememberMeToken()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

// Check for logout message
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $success = "You have been successfully logged out.";
} elseif (isset($_GET['timeout']) && $_GET['timeout'] === '1') {
    $error = "Your session has expired. Please log in again.";
}

// Maximum failed login attempts before account lockout
define('MAX_LOGIN_ATTEMPTS', 5);
// Lockout duration in seconds (30 minutes)
define('LOCKOUT_DURATION', 1800);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Simple validation
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password";
    } else {
        $result = authenticateUser($email, $password);
        
        if ($result['success']) {
            $user = $result['user'];
            
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['last_activity'] = time();
            
            // Handle remember me
            if ($remember) {
                setRememberMeToken($user['user_id']);
            }
            
            // Log successful login
            $sql = "INSERT INTO login_history (user_id, status, ip_address, user_agent) VALUES (?, 'success', ?, ?)";
            insertData($sql, [
                $user['user_id'],
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT']
            ]);
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CareerMatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .auth-container {
            min-height: calc(100vh - 72px);
            display: flex;
            align-items: center;
            background-color: #f8f9fa;
        }
        .auth-card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .auth-image {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 2rem;
            height: 100%;
        }
        .form-control:focus {
            border-color: #6a11cb;
            box-shadow: 0 0 0 0.25rem rgba(106, 17, 203, 0.25);
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="auth-container py-5">
        <div class="container">
            <div class="row g-0 auth-card">
                <div class="col-lg-6 d-none d-lg-block">
                    <div class="auth-image h-100">
                        <h2 class="fw-bold mb-4">Welcome Back!</h2>
                        <p class="lead mb-4">Log in to access your personalized job recommendations and continue your career journey.</p>
                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                                <span>Track your job applications</span>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                                <span>Get new job matches</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                                <span>Connect with employers directly</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 bg-white p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold">Log In to Your Account</h3>
                        <p class="text-muted">Access your CareerMatch profile</p>
                    </div>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email">
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password">
                            <div class="d-flex justify-content-end mt-1">
                                <a href="forgot-password.php" class="text-decoration-none small">Forgot password?</a>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">Log In</button>
                        
                        <div class="text-center">
                            <p class="mb-0">Don't have an account? <a href="register.php">Sign Up</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

