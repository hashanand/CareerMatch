<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - CareerMatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            text-align: center;
            padding: 2rem;
            max-width: 600px;
        }
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        .error-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #2c3e50;
        }
        .error-message {
            color: #6c757d;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        .btn-back {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            border: none;
            padding: 0.8rem 2rem;
            font-weight: 500;
            box-shadow: 0 5px 15px rgba(106, 17, 203, 0.2);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <?php
        $type = $_GET['type'] ?? 'general';
        $icon = 'exclamation-triangle';
        $title = 'Error';
        $message = 'An unexpected error occurred.';
        
        switch ($type) {
            case 'db':
                $icon = 'database-slash';
                $title = 'Database Connection Error';
                $message = 'We are currently experiencing technical difficulties with our database connection. Please try again later.';
                break;
            case '404':
                $icon = 'file-earmark-x';
                $title = 'Page Not Found';
                $message = 'The page you are looking for does not exist.';
                break;
            case 'auth':
                $icon = 'shield-lock';
                $title = 'Authentication Error';
                $message = 'You must be logged in to access this page.';
                break;
        }
        ?>
        
        <i class="bi bi-<?php echo $icon; ?> error-icon"></i>
        <h1 class="error-title"><?php echo $title; ?></h1>
        <p class="error-message"><?php echo $message; ?></p>
        <a href="index.php" class="btn btn-primary btn-back">
            <i class="bi bi-house me-2"></i> Back to Home
        </a>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 