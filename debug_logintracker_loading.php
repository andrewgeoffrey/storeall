<?php
/**
 * Debug LoginTracker Loading
 * Check if LoginTracker class can be loaded properly
 */

// Start output buffering
ob_start();

echo "<h1>Debug LoginTracker Loading</h1>";

// Include config first to load dependencies
require_once __DIR__ . '/config/config.php';
echo "<p>✓ Config loaded successfully</p>";

// Check if file exists
$loginTrackerFile = __DIR__ . '/includes/LoginTracker.php';
echo "<p>LoginTracker file path: {$loginTrackerFile}</p>";

if (file_exists($loginTrackerFile)) {
    echo "<p>✓ LoginTracker.php file exists</p>";
} else {
    echo "<p>✗ LoginTracker.php file does not exist</p>";
    exit;
}

// Try to include the file
try {
    require_once $loginTrackerFile;
    echo "<p>✓ LoginTracker.php included successfully</p>";
} catch (Exception $e) {
    echo "<p>✗ Error including LoginTracker.php: " . $e->getMessage() . "</p>";
    exit;
}

// Check if class exists
if (class_exists('LoginTracker')) {
    echo "<p>✓ LoginTracker class exists</p>";
} else {
    echo "<p>✗ LoginTracker class does not exist</p>";
    exit;
}

// Try to create an instance
try {
    $loginTracker = new LoginTracker();
    echo "<p>✓ LoginTracker instance created successfully</p>";
} catch (Exception $e) {
    echo "<p>✗ Error creating LoginTracker instance: " . $e->getMessage() . "</p>";
    exit;
}

// Check if the cleanup method exists
if (method_exists($loginTracker, 'cleanupExpiredTrustedDevices')) {
    echo "<p>✓ cleanupExpiredTrustedDevices method exists</p>";
    
    // Try to call the method
    try {
        $result = $loginTracker->cleanupExpiredTrustedDevices();
        echo "<p>✓ cleanupExpiredTrustedDevices() executed successfully</p>";
        echo "<p>Result: {$result} devices affected</p>";
    } catch (Exception $e) {
        echo "<p>✗ Error executing cleanupExpiredTrustedDevices(): " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>✗ cleanupExpiredTrustedDevices method does not exist</p>";
    
    // List all available methods
    $methods = get_class_methods($loginTracker);
    echo "<p><strong>Available methods:</strong></p>";
    echo "<ul>";
    foreach ($methods as $method) {
        echo "<li>{$method}</li>";
    }
    echo "</ul>";
}

// Flush output buffer
ob_end_flush();
?>
