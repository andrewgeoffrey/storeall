<?php
/**
 * StoreAll.io - Main Entry Point
 * Storage Unit Management SaaS Platform
 * 
 * This file serves as the main entry point for the application.
 * It handles routing, authentication, and initializes the core system.
 */

// Start session
session_start();

// Define the base path
$basePath = __DIR__;

// Load configuration
require_once $basePath . '/config/config.php';

// Load core classes
require_once $basePath . '/includes/Database.php';
require_once $basePath . '/includes/Auth.php';
require_once $basePath . '/includes/Session.php';
require_once $basePath . '/includes/Logger.php';
require_once $basePath . '/includes/ErrorHandler.php';
require_once $basePath . '/includes/PerformanceMonitor.php';

// Load helper functions
require_once $basePath . '/includes/helpers.php';

// Initialize error handling and performance monitoring
ErrorHandler::init();
PerformanceMonitor::start();

try {
    // Initialize database connection
    $db = Database::getInstance();
    
    // Handle routing
    $request_uri = $_SERVER['REQUEST_URI'];
    $path = parse_url($request_uri, PHP_URL_PATH);
    $path = trim($path, '/');
    
    // Route to appropriate handler
    if (empty($path)) {
        // Main landing page - content directly in index.php
        $auth = Auth::getInstance();
        $currentUser = $auth->getCurrentUser();
        
        // Log page visit
        Logger::getInstance()->info('Landing page accessed', [
            'user_id' => $currentUser ? $currentUser['id'] : 'guest',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        // Landing page HTML content directly in index.php
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Storage Unit Management Platform</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= asset('css/main.css') ?>" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-warehouse me-2"></i>
                <?= APP_NAME ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#pricing">Pricing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if ($currentUser): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>
                                <?= htmlspecialchars($currentUser['username']) ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/dashboard">Dashboard</a></li>
                                <li><a class="dropdown-item" href="/profile">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/logout">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/login">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-light ms-2" href="/register">Get Started</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section bg-gradient-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">
                        Manage Your Storage Business Like a Pro
                    </h1>
                    <p class="lead mb-4">
                        Streamline your storage unit operations with our comprehensive management platform. 
                        Track units, manage customers, handle billing, and grow your business efficiently.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="/register" class="btn btn-light btn-lg">
                            <i class="fas fa-rocket me-2"></i>
                            Start Free Trial
                        </a>
                        <a href="#demo" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-play me-2"></i>
                            Watch Demo
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="https://via.placeholder.com/600x400/007bff/ffffff?text=StoreAll+Dashboard" 
                         alt="StoreAll Dashboard" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Powerful Features</h2>
                <p class="lead text-muted">Everything you need to run your storage business</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon bg-primary text-white rounded-circle mx-auto mb-3">
                                <i class="fas fa-warehouse fa-2x"></i>
                            </div>
                            <h5 class="card-title">Unit Management</h5>
                            <p class="card-text">Track unit availability, sizes, and pricing. Manage multiple locations with ease.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon bg-success text-white rounded-circle mx-auto mb-3">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                            <h5 class="card-title">Customer Portal</h5>
                            <p class="card-text">Let customers manage their own accounts, make payments, and access their units.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon bg-warning text-white rounded-circle mx-auto mb-3">
                                <i class="fas fa-credit-card fa-2x"></i>
                            </div>
                            <h5 class="card-title">Automated Billing</h5>
                            <p class="card-text">Integrated payment processing with Stripe. Automated invoicing and late fee management.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Development Notice -->
    <section class="bg-light py-4">
        <div class="container">
            <div class="alert alert-info mb-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle fa-2x me-3"></i>
                    <div>
                        <h5 class="alert-heading">Development Environment</h5>
                        <p class="mb-0">
                            You're currently viewing the StoreAll.io development environment. 
                            This is a working prototype with full database integration, error logging, and performance monitoring.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?= APP_NAME ?></h5>
                    <p class="text-muted">Storage unit management made simple.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">
                        &copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?= asset('js/error-handler.js') ?>"></script>
    <script src="<?= asset('js/performance-monitor.js') ?>"></script>
</body>
</html>
        <?php
    } elseif (strpos($path, 'admin') === 0) {
        // Admin routes
        require_once 'admin/index.php';
    } elseif (strpos($path, 'api') === 0) {
        // API routes
        require_once 'api/index.php';
    } elseif (strpos($path, 'billing') === 0) {
        // Billing routes
        require_once 'billing/index.php';
    } elseif (strpos($path, 'customers') === 0) {
        // Customer portal routes
        require_once 'customers/index.php';
    } else {
        // Check if it's a subdomain/owner route
        $subdomain = explode('/', $path)[0];
        if (is_dir("owners/$subdomain")) {
            require_once "owners/$subdomain/index.php";
        } else {
            // 404 page
            http_response_code(404);
            require_once 'public/404.php';
        }
    }
    
} catch (Exception $e) {
    // Log error
    Logger::logError('Main application error: ' . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    // Show error page
    http_response_code(500);
    require_once 'public/error.php';
}

// End performance monitoring
PerformanceMonitor::end();
?>
