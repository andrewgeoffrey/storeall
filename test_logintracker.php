<?php
/**
 * Test LoginTracker Methods
 * Simple test to verify LoginTracker methods exist and work
 */

// Start output buffering
ob_start();

echo "<h1>Test LoginTracker Methods</h1>";

// Include the config first
require_once __DIR__ . '/config/config.php';

echo "<p>Config loaded successfully</p>";

// Include LoginTracker class
require_once __DIR__ . '/includes/LoginTracker.php';

echo "<p>LoginTracker.php included</p>";

try {
    $loginTracker = new LoginTracker();
    echo "<p>✓ LoginTracker instance created successfully</p>";
    
    // Test method existence
    echo "<h2>Testing Method Existence</h2>";
    
    $methods = [
        'cleanupExpiredTrustedDevices',
        'removeTrustedDevice', 
        'getTrustedDevices',
        'updateUserLoginPreferences'
    ];
    
    foreach ($methods as $method) {
        if (method_exists($loginTracker, $method)) {
            echo "<p>✓ Method '{$method}' exists</p>";
        } else {
            echo "<p>✗ Method '{$method}' does not exist</p>";
        }
    }
    
    // Test cleanup method specifically
    echo "<h2>Testing cleanupExpiredTrustedDevices Method</h2>";
    
    if (method_exists($loginTracker, 'cleanupExpiredTrustedDevices')) {
        try {
            $result = $loginTracker->cleanupExpiredTrustedDevices();
            echo "<p>✓ cleanupExpiredTrustedDevices() executed successfully</p>";
            echo "<p>Result: {$result} devices affected</p>";
        } catch (Exception $e) {
            echo "<p>✗ Error executing cleanupExpiredTrustedDevices(): " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>✗ cleanupExpiredTrustedDevices method not found</p>";
    }
    
    // List all available methods
    echo "<h2>All Available Methods</h2>";
    $allMethods = get_class_methods($loginTracker);
    echo "<ul>";
    foreach ($allMethods as $method) {
        echo "<li>{$method}</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>✗ Error: " . $e->getMessage() . "</p>";
}

// Flush output buffer
ob_end_flush();
?>
