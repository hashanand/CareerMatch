<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

require_once 'includes/db_config.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_type = $_POST['user_type'] ?? '';
    $terms = isset($_POST['terms']);
    
    // Validate full name
    if (empty($fullname)) {
        $errors[] = "Full name is required";
    } elseif (strlen($fullname) < 2 || strlen($fullname) > 100) {
        $errors[] = "Full name must be between 2 and 100 characters";
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email already exists
        $user = getRow("SELECT user_id FROM users WHERE email = ?", [$email]);
        if ($user) {
            $errors[] = "Email already registered";
        }
    }
    
    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    } elseif (!preg_match("/[A-Z]/", $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    } elseif (!preg_match("/[a-z]/", $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    } elseif (!preg_match("/[0-9]/", $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    // Validate password confirmation
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Validate user type
    if (!in_array($user_type, ['graduate', 'student', 'employer'])) {
        $errors[] = "Invalid user type selected";
    }
    
    // Validate terms acceptance
    if (!$terms) {
        $errors[] = "You must accept the Terms of Service and Privacy Policy";
    }
    
    // If no errors, create user account
    if (empty($errors)) {
        try {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user data
            $sql = "INSERT INTO users (full_name, email, password_hash, user_type) VALUES (?, ?, ?, ?)";
            $user_id = insertData($sql, [$fullname, $email, $password_hash, $user_type]);
            
            if ($user_id) {
                // Set session variables
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $fullname;
                $_SESSION['last_activity'] = time();
                
                // Log successful registration
                $sql = "INSERT INTO login_history (user_id, status, ip_address, user_agent) VALUES (?, 'success', ?, ?)";
                insertData($sql, [
                    $user_id,
                    $_SERVER['REMOTE_ADDR'],
                    $_SERVER['HTTP_USER_AGENT']
                ]);
                
                // Redirect to profile setup
                header("Location: profile-setup.php");
                exit;
            } else {
                $errors[] = "Error creating account. Please try again.";
            }
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $errors[] = "An error occurred during registration. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CareerMatch</title>
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
        .form-control:focus, .form-select:focus {
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
                        <h2 class="fw-bold mb-4">Start Your Career Journey Today</h2>
                        <p class="lead mb-4">Join thousands of graduates who found their perfect job match with CareerMatch.</p>
                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                                <span>AI-powered job matching</span>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                                <span>Automatic skill extraction</span>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                                <span>Personalized job recommendations</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                                <span>Direct connection with employers</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 bg-white p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold">Create Your Account</h3>
                        <p class="text-muted">Join CareerMatch to find your perfect job match</p>
                    </div>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            Registration successful! Please check your email to verify your account.
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="fullname" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Enter your full name" value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Create a password">
                            <div class="form-text">Must be at least 8 characters long</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your password">
                        </div>
                        
                        <div class="mb-3">
                            <label for="user_type" class="form-label">I am a</label>
                            <select class="form-select" id="user_type" name="user_type">
                                <option value="graduate">Fresh Graduate</option>
                                <option value="student">Student</option>
                                <option value="employer">Employer</option>
                            </select>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="terms.php">Terms of Service</a> and <a href="privacy.php">Privacy Policy</a>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">Create Account</button>
                        
                        <div class="text-center">
                            <p class="mb-0">Already have an account? <a href="login.php">Log In</a></p>
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

