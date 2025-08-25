<?php
/**
 * Test Method Exists
 * Simple test to verify if cleanupExpiredTrustedDevices method exists
 */

// Start output buffering
ob_start();

echo "<h1>Test Method Exists</h1>";

// Include config first
require_once __DIR__ . '/config/config.php';
echo "<p>✓ Config loaded</p>";

// Include LoginTracker
require_once __DIR__ . '/includes/LoginTracker.php';
echo "<p>✓ LoginTracker included</p>";

// Create instance
$loginTracker = new LoginTracker();
echo "<p>✓ LoginTracker instance created</p>";

// Check if method exists
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
    echo "<p>✗ cleanupExpiredTrustedDevices method does not exist</p>";
    
    // List all methods
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
