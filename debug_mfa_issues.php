<?php
/**
 * Debug MFA Issues
 * Check MFA-related database tables and functions
 */

// Start output buffering
ob_start();

echo "<h1>Debug MFA Issues</h1>";

// Include the config first
require_once __DIR__ . '/config/config.php';

echo "<p>Config loaded successfully</p>";

try {
    $db = Database::getInstance();
    
    // Check if verification_tokens table exists
    echo "<h2>Checking Database Tables</h2>";
    
    $tables = ['verification_tokens', 'login_attempts', 'trusted_devices', 'user_login_preferences'];
    
    foreach ($tables as $table) {
        if ($db->tableExists($table)) {
            echo "<p>✓ Table '{$table}' exists</p>";
            
            // Show table structure
            $columns = $db->getTableColumns($table);
            echo "<p><strong>Columns in {$table}:</strong></p>";
            echo "<ul>";
            foreach ($columns as $column) {
                echo "<li>{$column['Field']} - {$column['Type']}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>✗ Table '{$table}' does not exist</p>";
        }
    }
    
    // Test MFA code generation
    echo "<h2>Testing MFA Code Generation</h2>";
    
    // Create a test user for MFA testing
    $auth = Auth::getInstance();
    $testUserData = [
        'email' => 'mfa_test@example.com',
        'password' => 'testpassword',
        'first_name' => 'MFA',
        'last_name' => 'Test',
        'role' => 'customer',
        'email_verified_at' => date('Y-m-d H:i:s'),
        'is_active' => 1,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $testUserId = $auth->register($testUserData);
    
    if ($testUserId) {
        echo "<p>✓ Test user created (ID: {$testUserId})</p>";
        
        // Test MFA code generation
        try {
            $code = sprintf('%06d', mt_rand(0, 999999));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            
            $result = $db->insert('verification_tokens', [
                'user_id' => $testUserId,
                'token' => $code,
                'type' => 'mfa_code',
                'expires_at' => $expiresAt,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($result) {
                echo "<p>✓ MFA code generated and stored (ID: {$result})</p>";
                
                // Test MFA code verification
                $mfaToken = $db->fetch(
                    "SELECT * FROM verification_tokens 
                     WHERE user_id = ? AND token = ? AND type = 'mfa_code' 
                     AND expires_at > NOW() AND used_at IS NULL",
                    [$testUserId, $code]
                );
                
                if ($mfaToken) {
                    echo "<p>✓ MFA code verification works</p>";
                    
                    // Mark as used
                    $db->update('verification_tokens', 
                        ['used_at' => date('Y-m-d H:i:s')], 
                        'id = ?', 
                        [$mfaToken['id']]
                    );
                    echo "<p>✓ MFA code marked as used</p>";
                } else {
                    echo "<p>✗ MFA code verification failed</p>";
                }
            } else {
                echo "<p>✗ Failed to store MFA code</p>";
            }
        } catch (Exception $e) {
            echo "<p>✗ Error generating MFA code: " . $e->getMessage() . "</p>";
        }
        
        // Clean up test user
        $db->delete('verification_tokens', 'user_id = ?', [$testUserId]);
        $db->delete('users', 'id = ?', [$testUserId]);
        echo "<p>✓ Test user cleaned up</p>";
    } else {
        echo "<p>✗ Failed to create test user</p>";
    }
    
    // Test LoginTracker methods
    echo "<h2>Testing LoginTracker Methods</h2>";
    
    require_once __DIR__ . '/includes/LoginTracker.php';
    $loginTracker = new LoginTracker();
    
    // Check if cleanupExpiredTrustedDevices method exists
    if (method_exists($loginTracker, 'cleanupExpiredTrustedDevices')) {
        echo "<p>✓ cleanupExpiredTrustedDevices method exists</p>";
        
        // Test the method
        try {
            $affected = $loginTracker->cleanupExpiredTrustedDevices();
            echo "<p>✓ cleanupExpiredTrustedDevices executed successfully (affected: {$affected})</p>";
        } catch (Exception $e) {
            echo "<p>✗ Error calling cleanupExpiredTrustedDevices: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>✗ cleanupExpiredTrustedDevices method not found</p>";
        
        // List all available methods
        $methods = get_class_methods($loginTracker);
        echo "<p>Available methods: " . implode(', ', $methods) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p>✗ Error: " . $e->getMessage() . "</p>";
}

// Flush output buffer
ob_end_flush();
?>
