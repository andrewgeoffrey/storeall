<?php
/**
 * StoreAll.io - Main Entry Point
 * Development Environment Test
 */

// Display all errors in development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Database connection test
try {
    $pdo = new PDO(
        'mysql:host=mysql;dbname=storeall_dev;charset=utf8mb4',
        'storeall_user',
        'storeall_password',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    $dbStatus = 'Connected successfully!';
} catch (PDOException $e) {
    $dbStatus = 'Connection failed: ' . $e->getMessage();
}

// Redis connection test
try {
    $redis = new Redis();
    $redis->connect('redis', 6379);
    $redis->ping();
    $redisStatus = 'Connected successfully!';
} catch (Exception $e) {
    $redisStatus = 'Connection failed: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StoreAll.io - Development Environment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h1 class="h3 mb-0">
                            <i class="fas fa-warehouse me-2"></i>
                            StoreAll.io Development Environment
                        </h1>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            <h4 class="alert-heading">
                                <i class="fas fa-check-circle me-2"></i>
                                Environment Status
                            </h4>
                            <p class="mb-0">Your StoreAll.io development environment is running successfully!</p>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="fas fa-database me-2"></i>
                                            Database Connection
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-0">
                                            <strong>Status:</strong> 
                                            <span class="badge bg-success"><?php echo $dbStatus; ?></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="fas fa-memory me-2"></i>
                                            Redis Connection
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-0">
                                            <strong>Status:</strong> 
                                            <span class="badge bg-success"><?php echo $redisStatus; ?></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Next Steps
                                </h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="fas fa-arrow-right text-primary me-2"></i>
                                        Start building your core PHP classes
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-arrow-right text-primary me-2"></i>
                                        Implement user authentication system
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-arrow-right text-primary me-2"></i>
                                        Create the organization management system
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-arrow-right text-primary me-2"></i>
                                        Build the inventory management interface
                                    </li>
                                    <li class="mb-0">
                                        <i class="fas fa-arrow-right text-primary me-2"></i>
                                        Develop the customer portal
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-4 text-center">
                            <a href="http://localhost:8081" class="btn btn-outline-primary me-2" target="_blank">
                                <i class="fas fa-database me-2"></i>
                                phpMyAdmin
                            </a>
                            <a href="https://github.com" class="btn btn-outline-dark" target="_blank">
                                <i class="fab fa-github me-2"></i>
                                View on GitHub
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
