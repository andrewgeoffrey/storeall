<?php
/**
 * Force Reload LoginTracker
 * Clear any caching and force reload the LoginTracker class
 */

// Start output buffering
ob_start();

echo "<h1>Force Reload LoginTracker</h1>";

// Clear any opcache if available
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "<p>✓ OpCache cleared</p>";
} else {
    echo "<p>⚠ OpCache not available</p>";
}

// Include config first
require_once __DIR__ . '/config/config.php';
echo "<p>✓ Config loaded</p>";

// Check file modification time
$loginTrackerFile = __DIR__ . '/includes/LoginTracker.php';
$fileTime = filemtime($loginTrackerFile);
echo "<p>LoginTracker.php last modified: " . date('Y-m-d H:i:s', $fileTime) . "</p>";

// Force reload by using include instead of require_once
if (class_exists('LoginTracker')) {
    echo "<p>⚠ LoginTracker class already exists, trying to reload...</p>";
}

// Include the file
include $loginTrackerFile;
echo "<p>✓ LoginTracker.php included</p>";

// Check if class exists now
if (class_exists('LoginTracker')) {
    echo "<p>✓ LoginTracker class exists</p>";
    
    // Create instance
    $loginTracker = new LoginTracker();
    echo "<p>✓ LoginTracker instance created</p>";
    
    // Check for the cleanup method
    if (method_exists($loginTracker, 'cleanupExpiredTrustedDevices')) {
        echo "<p>✓ cleanupExpiredTrustedDevices method exists</p>";
        
        // Test the method
        try {
            $result = $loginTracker->cleanupExpiredTrustedDevices();
            echo "<p>✓ Method executed successfully: {$result} devices affected</p>";
        } catch (Exception $e) {
            echo "<p>✗ Method execution failed: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>✗ cleanupExpiredTrustedDevices method still missing</p>";
        
        // List all methods
        $methods = get_class_methods($loginTracker);
        echo "<p><strong>Available methods:</strong></p>";
        echo "<ul>";
        foreach ($methods as $method) {
            echo "<li>{$method}</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p>✗ LoginTracker class still not found</p>";
}

// Flush output buffer
ob_end_flush();
?>

