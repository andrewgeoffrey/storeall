<?php
// Load configuration first, before any output
if (file_exists('config/config.php')) {
    require_once 'config/config.php';
}

// Now we can output HTML
echo "<h1>StoreAll.io - Test Page</h1>";

// Test 1: Basic PHP
echo "<h2>Test 1: Basic PHP</h2>";
echo "<p>✅ PHP is working</p>";

// Test 2: Configuration
echo "<h2>Test 2: Configuration</h2>";
if (defined('ENVIRONMENT')) {
    echo "<p>✅ Config file loaded</p>";
    echo "<p>Environment: " . ENVIRONMENT . "</p>";
    echo "<p>App Name: " . APP_NAME . "</p>";
} else {
    echo "<p>❌ Config file not loaded</p>";
}

// Test 3: Include Files
echo "<h2>Test 3: Include Files</h2>";
$files = [
    'includes/Database.php',
    'includes/Auth.php',
    'includes/Session.php',
    'includes/Logger.php',
    'includes/ErrorHandler.php',
    'includes/PerformanceMonitor.php',
    'includes/helpers.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p>✅ $file exists</p>";
    } else {
        echo "<p>❌ $file not found</p>";
    }
}

// Test 4: Load Database Class
echo "<h2>Test 4: Database Class Loading</h2>";
try {
    if (file_exists('includes/Database.php')) {
        require_once 'includes/Database.php';
        echo "<p>✅ Database.php loaded</p>";
        
        if (class_exists('Database')) {
            echo "<p>✅ Database class exists</p>";
        } else {
            echo "<p>❌ Database class not found after loading file</p>";
        }
    } else {
        echo "<p>❌ Database.php file not found</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error loading Database.php: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 5: Database Connection
echo "<h2>Test 5: Database Connection</h2>";
try {
    if (class_exists('Database')) {
        $db = Database::getInstance();
        echo "<p>✅ Database instance created</p>";
        
        // Try a simple query
        $result = $db->query("SELECT 1 as test");
        echo "<p>✅ Database query successful</p>";
        
        $row = $result->fetch();
        echo "<p>✅ Query result: " . $row['test'] . "</p>";
    } else {
        echo "<p>❌ Database class not found</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 6: Session
echo "<h2>Test 6: Session</h2>";
if (session_status() === PHP_SESSION_NONE) {
    echo "<p>⚠️ Session not started</p>";
} else {
    echo "<p>✅ Session is active</p>";
    echo "<p>Session ID: " . session_id() . "</p>";
}

echo "<h2>All Tests Complete</h2>";
echo "<p><a href='index.php'>Go to main page</a></p>";
?>




