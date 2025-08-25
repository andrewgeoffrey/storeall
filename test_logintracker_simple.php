<?php
/**
 * Simple LoginTracker Test
 */

echo "<h1>Simple LoginTracker Test</h1>";

// Include config first
require_once __DIR__ . '/config/config.php';
echo "<p>✓ Config loaded</p>";

// Include LoginTracker
require_once __DIR__ . '/includes/LoginTracker.php';
echo "<p>✓ LoginTracker.php included</p>";

// Check if class exists
if (class_exists('LoginTracker')) {
    echo "<p>✓ LoginTracker class exists</p>";
    
    // Create instance
    $loginTracker = new LoginTracker();
    echo "<p>✓ LoginTracker instance created</p>";
    
    // Check if method exists
    if (method_exists($loginTracker, 'cleanupExpiredTrustedDevices')) {
        echo "<p>✓ cleanupExpiredTrustedDevices method exists</p>";
        
        // Test the method
        $result = $loginTracker->cleanupExpiredTrustedDevices();
        echo "<p>✓ Method executed successfully: {$result}</p>";
    } else {
        echo "<p>✗ cleanupExpiredTrustedDevices method does not exist</p>";
    }
} else {
    echo "<p>✗ LoginTracker class does not exist</p>";
    
    // List all declared classes
    $classes = get_declared_classes();
    echo "<p><strong>Declared classes:</strong></p>";
    echo "<ul>";
    foreach ($classes as $class) {
        if (strpos($class, 'Login') !== false) {
            echo "<li>{$class}</li>";
        }
    }
    echo "</ul>";
}
?>
