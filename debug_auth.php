<?php
/**
 * Debug Auth Class
 * Simple script to debug Auth class loading issues
 */

// Start output buffering
ob_start();

echo "<h1>Debug Auth Class</h1>";

// Include the config first
require_once __DIR__ . '/config/config.php';

echo "<p>Config loaded successfully</p>";

// Check if Auth class file exists
if (file_exists(__DIR__ . '/includes/Auth.php')) {
    echo "<p>✓ Auth.php file exists</p>";
} else {
    echo "<p>✗ Auth.php file not found</p>";
    exit;
}

// Include the Auth class
require_once __DIR__ . '/includes/Auth.php';

echo "<p>Auth.php included</p>";

// Check if Auth class exists
if (class_exists('Auth')) {
    echo "<p>✓ Auth class exists</p>";
    
    // Try to get instance
    try {
        $auth = Auth::getInstance();
        echo "<p>✓ Auth instance created successfully</p>";
        
        // Check if createUser method exists
        if (method_exists($auth, 'createUser')) {
            echo "<p>✓ createUser method exists</p>";
        } else {
            echo "<p>✗ createUser method not found</p>";
            
            // List all available methods
            $methods = get_class_methods($auth);
            echo "<p>Available methods: " . implode(', ', $methods) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p>✗ Error creating Auth instance: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>✗ Auth class not found</p>";
}

// Flush output buffer
ob_end_flush();
?>
