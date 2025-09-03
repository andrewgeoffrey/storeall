<?php
// Load configuration first
require_once __DIR__ . '/../../config/config.php';

// Check if Session class already exists to prevent conflicts
if (!class_exists('Session')) {
    require_once __DIR__ . '/../../includes/Session.php';
}

// Get the session ID from the storeall_session cookie (lowercase)
$sessionId = $_COOKIE['storeall_session'] ?? null;

if (!$sessionId) {
    // No session cookie, redirect to login
    header('Location: /login/');
    exit;
}

// Check if session is already active
if (session_status() === PHP_SESSION_NONE) {
    // No session active, set ID and start
    session_id($sessionId);
    session_start();
} elseif (session_status() === PHP_SESSION_ACTIVE) {
    // Session is already active, check if it's the right one
    if (session_id() !== $sessionId) {
        // Different session, close current and start new one
        session_write_close();
        session_id($sessionId);
        session_start();
    }
    // If same session ID, continue using current session
}

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login/');
    exit;
}

$user = [
    'id' => $_SESSION['user_id'],
    'email' => $_SESSION['email'] ?? '',
    'first_name' => $_SESSION['first_name'] ?? '',
    'last_name' => $_SESSION['last_name'] ?? '',
    'role' => $_SESSION['role']
];

// Load required classes for dashboard functionality
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Logger.php';

// Get system statistics
$db = Database::getInstance();
$stats = [];

try {
    // Check if users table exists first
    $tableExists = $db->tableExists('users');
    
    if ($tableExists) {
        // Total users count
        $stats['total_users'] = $db->fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
        
        // Total owners count
        $stats['total_owners'] = $db->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'owner'")['count'] ?? 0;
        
        // Total customers count
        $stats['total_customers'] = $db->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")['count'] ?? 0;
        
        // Active subscriptions count (check if subscription_status column exists)
        try {
            $stats['active_subscriptions'] = $db->fetch("SELECT COUNT(*) as count FROM users WHERE subscription_status = 'active'")['count'] ?? 0;
        } catch (Exception $e) {
            $stats['active_subscriptions'] = 0; // Column doesn't exist yet
        }
        
        // Trial users count
        try {
            $stats['trial_users'] = $db->fetch("SELECT COUNT(*) as count FROM users WHERE subscription_status = 'trial'")['count'] ?? 0;
        } catch (Exception $e) {
            $stats['trial_users'] = 0; // Column doesn't exist yet
        }
    } else {
        // Users table doesn't exist yet
        $stats = [
            'total_users' => 0,
            'total_owners' => 0, 
            'total_customers' => 0,
            'active_subscriptions' => 0,
            'trial_users' => 0
        ];
    }
    
} catch (Exception $e) {
    // Handle database errors gracefully
    $stats = [
        'total_users' => 0,
        'total_owners' => 0, 
        'total_customers' => 0,
        'active_subscriptions' => 0,
        'trial_users' => 0
    ];
    
    // Log the error for debugging
    error_log("Dashboard statistics error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - StoreAll.io</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js for monitoring charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        .main-content {
            background: #f8f9fa;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: transform 0.2s ease;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .stat-card .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
        }
        .health-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .health-healthy { background: #28a745; }
        .health-warning { background: #ffc107; }
        .health-unhealthy { background: #dc3545; }
        .monitoring-chart {
            height: 300px;
            margin: 20px 0;
        }
        .real-time-update {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        .error-log-entry {
            border-left: 4px solid #dc3545;
            padding: 10px;
            margin: 5px 0;
            background: #f8f9fa;
            border-radius: 0 8px 8px 0;
        }
        .error-log-entry.warning {
            border-left-color: #ffc107;
        }
        .error-log-entry.info {
            border-left-color: #17a2b8;
        }
        .performance-metric {
            background: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .metric-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #495057;
        }
        .metric-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">StoreAll.io</h4>
                        <p class="text-white-50">Admin Dashboard</p>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#overview" data-bs-toggle="tab">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                System Overview
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#monitoring" data-bs-toggle="tab">
                                <i class="fas fa-chart-line me-2"></i>
                                System Monitoring
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#users" data-bs-toggle="tab">
                                <i class="fas fa-users me-2"></i>
                                User Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#billing" data-bs-toggle="tab">
                                <i class="fas fa-credit-card me-2"></i>
                                Billing & Subscriptions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#support" data-bs-toggle="tab">
                                <i class="fas fa-headset me-2"></i>
                                Support Tools
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#profile" data-bs-toggle="tab">
                                <i class="fas fa-user me-2"></i>
                                My Profile
                            </a>
                        </li>
                    </ul>
                    
                    <div class="mt-4 text-center">
                        <small class="text-white-50">
                            Logged in as: <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                        </small>
                        <br>
                        <a href="/logout.php" class="btn btn-outline-light btn-sm mt-2">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="tab-content">
                    <!-- System Overview Tab -->
                    <div class="tab-pane fade show active" id="overview">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                            <h1 class="h2">System Overview</h1>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshStats()">
                                        <i class="fas fa-sync-alt me-1"></i> Refresh
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics Cards -->
                        <div class="row mb-4">
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card stat-card h-100">
                                    <div class="card-body text-center">
                                        <div class="stat-value"><?php echo $stats['total_users']; ?></div>
                                        <div class="stat-label">Total Users</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card stat-card h-100">
                                    <div class="card-body text-center">
                                        <div class="stat-value"><?php echo $stats['total_owners']; ?></div>
                                        <div class="stat-label">Property Owners</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card stat-card h-100">
                                    <div class="card-body text-center">
                                        <div class="stat-value"><?php echo $stats['total_customers']; ?></div>
                                        <div class="stat-label">Customers</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card stat-card h-100">
                                    <div class="card-body text-center">
                                        <div class="stat-value"><?php echo $stats['active_subscriptions']; ?></div>
                                        <div class="stat-label">Active Subscriptions</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-bolt me-2"></i>Quick Actions
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-primary" onclick="showUserManagement()">
                                                <i class="fas fa-users me-2"></i>Manage Users
                                            </button>
                                            <button class="btn btn-success" onclick="showSystemHealth()">
                                                <i class="fas fa-heartbeat me-2"></i>System Health
                                            </button>
                                            <button class="btn btn-info" onclick="showErrorLogs()">
                                                <i class="fas fa-exclamation-triangle me-2"></i>View Error Logs
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-chart-bar me-2"></i>Recent Activity
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="recentActivity">
                                            <p class="text-muted">Loading recent activity...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Monitoring Tab -->
                    <div class="tab-pane fade" id="monitoring">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                            <h1 class="h2">Live System Monitoring</h1>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="startRealTimeMonitoring()">
                                        <i class="fas fa-play me-1"></i> Start Real-time
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshMonitoringData()">
                                        <i class="fas fa-sync-alt me-1"></i> Refresh
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- System Health Status -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-heartbeat me-2"></i>System Health
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="systemHealth">
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="health-indicator health-healthy"></span>
                                                <span>Database: <strong id="dbStatus">Checking...</strong></span>
                                            </div>
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="health-indicator health-healthy"></span>
                                                <span>API: <strong id="apiStatus">Checking...</strong></span>
                                            </div>
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="health-indicator health-healthy"></span>
                                                <span>Overall: <strong id="overallStatus">Checking...</strong></span>
                                            </div>
                                            <div class="mt-3">
                                                <small class="text-muted">Last updated: <span id="lastHealthCheck">Never</span></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-database me-2"></i>Database Status
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="databaseStatus">
                                            <div class="mb-2">
                                                <strong>Version:</strong> <span id="dbVersion">Checking...</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Uptime:</strong> <span id="dbUptime">Checking...</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Connections:</strong> <span id="dbConnections">Checking...</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Slow Queries:</strong> <span id="dbSlowQueries">Checking...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-memory me-2"></i>Memory Usage
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="memoryUsage">
                                            <div class="mb-2">
                                                <strong>Current:</strong> <span id="memCurrent">Checking...</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Peak:</strong> <span id="memPeak">Checking...</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Limit:</strong> <span id="memLimit">Checking...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Performance Charts -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-chart-line me-2"></i>Page Load Performance
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="pageLoadChart" class="monitoring-chart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-chart-bar me-2"></i>Error Distribution
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="errorChart" class="monitoring-chart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Real-time Error Logs -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-exclamation-triangle me-2"></i>Recent Errors
                                            <span class="badge bg-danger ms-2 real-time-update" id="errorCount">0</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="errorLogs" style="max-height: 400px; overflow-y: auto;">
                                            <p class="text-muted">Loading error logs...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Management Tab -->
                    <div class="tab-pane fade" id="users">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                            <h1 class="h2">User Management</h1>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <p class="text-muted">User management functionality coming soon...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Billing & Subscriptions Tab -->
                    <div class="tab-pane fade" id="billing">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                            <h1 class="h2">Billing & Subscriptions</h1>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <p class="text-muted">Billing and subscription management coming soon...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Support Tools Tab -->
                    <div class="tab-pane fade" id="support">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                            <h1 class="h2">Support Tools</h1>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <p class="text-muted">Support tools and diagnostics coming soon...</p>
                            </div>
                        </div>
                    </div>

                    <!-- My Profile Tab -->
                    <div class="tab-pane fade" id="profile">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                            <h1 class="h2">My Profile</h1>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>Profile Information</h5>
                                        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                                        <p><strong>Role:</strong> <?php echo htmlspecialchars(ucfirst($user['role'])); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Account Actions</h5>
                                        <button class="btn btn-primary btn-sm me-2">
                                            <i class="fas fa-edit me-1"></i>Edit Profile
                                        </button>
                                        <button class="btn btn-warning btn-sm me-2">
                                            <i class="fas fa-key me-1"></i>Change Password
                                        </button>
                                        <button class="btn btn-info btn-sm">
                                            <i class="fas fa-cog me-1"></i>Preferences
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Include monitoring JavaScript -->
    <script src="monitoring.js"></script>
    
    <!-- Dashboard JavaScript -->
    <script>
        // Initialize charts
        let pageLoadChart, errorChart;
        let realTimeInterval;
        
        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
            loadInitialData();
            setupTabNavigation();
        });
        
        // Initialize charts
        function initializeCharts() {
            // Page Load Performance Chart
            const pageLoadCtx = document.getElementById('pageLoadChart').getContext('2d');
            pageLoadChart = new Chart(pageLoadCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Page Load Time (ms)',
                        data: [],
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Error Distribution Chart
            const errorCtx = document.getElementById('errorChart').getContext('2d');
            errorChart = new Chart(errorCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Errors', 'Warnings', 'Info'],
                    datasets: [{
                        data: [0, 0, 0],
                        backgroundColor: [
                            '#dc3545',
                            '#ffc107',
                            '#17a2b8'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
        
        // Load initial data
        function loadInitialData() {
            loadSystemHealth();
            loadDatabaseStatus();
            loadPerformanceMetrics();
            loadErrorLogs();
        }
        
        // Setup tab navigation
        function setupTabNavigation() {
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all links and tabs
                    navLinks.forEach(l => l.classList.remove('active'));
                    document.querySelectorAll('.tab-pane').forEach(tab => tab.classList.remove('show', 'active'));
                    
                    // Add active class to clicked link
                    this.classList.add('active');
                    
                    // Show corresponding tab
                    const targetId = this.getAttribute('href').substring(1);
                    const targetTab = document.getElementById(targetId);
                    targetTab.classList.add('show', 'active');
                    
                    // If monitoring tab is active, start real-time updates
                    if (targetId === 'monitoring') {
                        startRealTimeMonitoring();
                    } else {
                        stopRealTimeMonitoring();
                    }
                });
            });
        }
        
        // Load system health
        async function loadSystemHealth() {
            try {
                const response = await fetch('monitoring_api.php?action=get_system_health');
                const data = await response.json();
                
                if (data.success) {
                    updateSystemHealthDisplay(data.data);
                }
            } catch (error) {
                console.error('Failed to load system health:', error);
            }
        }
        
        // Update system health display
        function updateSystemHealthDisplay(health) {
            const dbStatus = document.getElementById('dbStatus');
            const apiStatus = document.getElementById('apiStatus');
            const overallStatus = document.getElementById('overallStatus');
            const lastHealthCheck = document.getElementById('lastHealthCheck');
            
            // Update database status
            dbStatus.textContent = health.database;
            dbStatus.className = `health-${health.database}`;
            
            // Update API status
            apiStatus.textContent = health.api;
            apiStatus.className = `health-${health.api}`;
            
            // Update overall status
            overallStatus.textContent = health.overall;
            overallStatus.className = `health-${health.overall}`;
            
            // Update last check time
            lastHealthCheck.textContent = health.last_check;
        }
        
        // Load database status
        async function loadDatabaseStatus() {
            try {
                const response = await fetch('monitoring_api.php?action=get_database_status');
                const data = await response.json();
                
                if (data.success) {
                    updateDatabaseStatusDisplay(data.data);
                }
            } catch (error) {
                console.error('Failed to load database status:', error);
            }
        }
        
        // Update database status display
        function updateDatabaseStatusDisplay(status) {
            document.getElementById('dbVersion').textContent = status.version || 'Unknown';
            document.getElementById('dbUptime').textContent = status.uptime || 'Unknown';
            document.getElementById('dbConnections').textContent = status.active_connections || 'Unknown';
            document.getElementById('dbSlowQueries').textContent = status.slow_queries || 'Unknown';
        }
        
        // Load performance metrics
        async function loadPerformanceMetrics() {
            try {
                const response = await fetch('monitoring_api.php?action=get_performance_metrics');
                const data = await response.json();
                
                if (data.success) {
                    updatePerformanceCharts(data.data);
                    updateMemoryUsage(data.data.memory_usage);
                }
            } catch (error) {
                console.error('Failed to load performance metrics:', error);
            }
        }
        
        // Update performance charts
        function updatePerformanceCharts(metrics) {
            // Update page load chart
            if (metrics.page_load_times && metrics.page_load_times.length > 0) {
                const labels = metrics.page_load_times.slice(0, 10).map(item => 
                    new Date(item.created_at).toLocaleTimeString()
                );
                const values = metrics.page_load_times.slice(0, 10).map(item => item.load_time);
                
                pageLoadChart.data.labels = labels;
                pageLoadChart.data.datasets[0].data = values;
                pageLoadChart.update();
            }
        }
        
        // Update memory usage display
        function updateMemoryUsage(memory) {
            if (memory) {
                document.getElementById('memCurrent').textContent = memory.current || 'Unknown';
                document.getElementById('memPeak').textContent = memory.peak || 'Unknown';
                document.getElementById('memLimit').textContent = memory.limit || 'Unknown';
            }
        }
        
        // Load error logs
        async function loadErrorLogs() {
            try {
                const response = await fetch('monitoring_api.php?action=get_recent_errors');
                const data = await response.json();
                
                if (data.success) {
                    updateErrorLogsDisplay(data.data);
                    updateErrorChart(data.data);
                }
            } catch (error) {
                console.error('Failed to load error logs:', error);
            }
        }
        
        // Update error logs display
        function updateErrorLogsDisplay(errors) {
            const errorLogsContainer = document.getElementById('errorLogs');
            const errorCount = document.getElementById('errorCount');
            
            if (errors.length === 0) {
                errorLogsContainer.innerHTML = '<p class="text-muted">No errors found</p>';
                errorCount.textContent = '0';
                return;
            }
            
            let html = '';
            errors.forEach(error => {
                const levelClass = error.level.toLowerCase();
                const time = new Date(error.created_at).toLocaleString();
                
                html += `
                    <div class="error-log-entry ${levelClass}">
                        <div class="d-flex justify-content-between">
                            <strong>${error.level}</strong>
                            <small class="text-muted">${time}</small>
                        </div>
                        <div>${error.message}</div>
                        <small class="text-muted">Source: ${error.source}</small>
                    </div>
                `;
            });
            
            errorLogsContainer.innerHTML = html;
            errorCount.textContent = errors.length;
        }
        
        // Update error chart
        function updateErrorChart(errors) {
            const errorCounts = { ERROR: 0, WARNING: 0, INFO: 0 };
            
            errors.forEach(error => {
                if (errorCounts.hasOwnProperty(error.level)) {
                    errorCounts[error.level] += error.count;
                }
            });
            
            errorChart.data.datasets[0].data = [
                errorCounts.ERROR,
                errorCounts.WARNING,
                errorCounts.INFO
            ];
            errorChart.update();
        }
        
        // Start real-time monitoring
        function startRealTimeMonitoring() {
            if (realTimeInterval) {
                clearInterval(realTimeInterval);
            }
            
            // Update every 30 seconds
            realTimeInterval = setInterval(() => {
                loadSystemHealth();
                loadDatabaseStatus();
                loadPerformanceMetrics();
                loadErrorLogs();
            }, 30000);
            
            // Load initial data immediately
            loadInitialData();
        }
        
        // Stop real-time monitoring
        function stopRealTimeMonitoring() {
            if (realTimeInterval) {
                clearInterval(realTimeInterval);
                realTimeInterval = null;
            }
        }
        
        // Refresh monitoring data
        function refreshMonitoringData() {
            loadInitialData();
        }
        
        // Refresh stats
        function refreshStats() {
            location.reload();
        }
        
        // Show user management
        function showUserManagement() {
            document.querySelector('a[href="#users"]').click();
        }
        
        // Show system health
        function showSystemHealth() {
            document.querySelector('a[href="#monitoring"]').click();
        }
        
        // Show error logs
        function showErrorLogs() {
            document.querySelector('a[href="#monitoring"]').click();
        }
    </script>
</body>
</html>
