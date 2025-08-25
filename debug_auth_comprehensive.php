<?php
/**
 * Comprehensive Debug Auth Class
 * Detailed debugging of Auth class and methods
 */

// Start output buffering
ob_start();

echo "<h1>Comprehensive Debug Auth Class</h1>";

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
        
        // Get all methods
        $methods = get_class_methods($auth);
        echo "<p><strong>All available methods:</strong></p>";
        echo "<ul>";
        foreach ($methods as $method) {
            echo "<li>{$method}</li>";
        }
        echo "</ul>";
        
        // Check if createUser method exists
        if (method_exists($auth, 'createUser')) {
            echo "<p>✓ createUser method exists</p>";
            
            // Try to call createUser with test data
            try {
                $testUserData = [
                    'email' => 'test@example.com',
                    'password' => 'testpassword',
                    'first_name' => 'Test',
                    'last_name' => 'User',
                    'role' => 'customer',
                    'email_verified_at' => date('Y-m-d H:i:s'),
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $userId = $auth->createUser($testUserData);
                if ($userId) {
                    echo "<p>✓ createUser method works! Created user with ID: {$userId}</p>";
                    
                    // Clean up - delete the test user
                    $db = Database::getInstance();
                    $db->delete('users', 'id = ?', [$userId]);
                    echo "<p>✓ Test user cleaned up</p>";
                } else {
                    echo "<p>✗ createUser method returned false</p>";
                }
            } catch (Exception $e) {
                echo "<p>✗ Error calling createUser: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p>✗ createUser method not found</p>";
        }
        
        // Check if register method exists
        if (method_exists($auth, 'register')) {
            echo "<p>✓ register method exists</p>";
        } else {
            echo "<p>✗ register method not found</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>✗ Error creating Auth instance: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>✗ Auth class not found</p>";
}

// Also test LoginTracker
echo "<h2>Testing LoginTracker</h2>";

if (file_exists(__DIR__ . '/includes/LoginTracker.php')) {
    echo "<p>✓ LoginTracker.php file exists</p>";
    
    require_once __DIR__ . '/includes/LoginTracker.php';
    
    if (class_exists('LoginTracker')) {
        echo "<p>✓ LoginTracker class exists</p>";
        
        try {
            $loginTracker = new LoginTracker();
            echo "<p>✓ LoginTracker instance created successfully</p>";
            
            // Check for cleanupExpiredTrustedDevices method
            if (method_exists($loginTracker, 'cleanupExpiredTrustedDevices')) {
                echo "<p>✓ cleanupExpiredTrustedDevices method exists</p>";
            } else {
                echo "<p>✗ cleanupExpiredTrustedDevices method not found</p>";
                
                // List all available methods
                $methods = get_class_methods($loginTracker);
                echo "<p>Available methods: " . implode(', ', $methods) . "</p>";
            }
        } catch (Exception $e) {
            echo "<p>✗ Error creating LoginTracker instance: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>✗ LoginTracker class not found</p>";
    }
} else {
    echo "<p>✗ LoginTracker.php file not found</p>";
}

// Flush output buffer
ob_end_flush();
?>
