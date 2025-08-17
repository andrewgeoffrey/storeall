<?php
// Test script to verify the verification page works
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/Logger.php';

echo "<h1>Verification Page Test</h1>";

try {
    $db = Database::getInstance();
    
    // Get the most recent verification token
    $token = $db->fetch("SELECT token FROM verification_tokens ORDER BY created_at DESC LIMIT 1");
    
    if ($token) {
        $verificationUrl = APP_URL . '/verify-email.php?token=' . $token['token'];
        echo "<h2>Testing verification URL</h2>";
        echo "URL: <a href='{$verificationUrl}' target='_blank'>{$verificationUrl}</a><br>";
        
        // Test the verification process directly
        echo "<h2>Testing verification process</h2>";
        
        $tokenData = $db->fetch(
            "SELECT vt.user_id, vt.expires_at, vt.used_at, u.email 
             FROM verification_tokens vt 
             JOIN users u ON vt.user_id = u.id 
             WHERE vt.token = ? AND vt.type = 'email_verification'",
            [$token['token']]
        );
        
        if ($tokenData) {
            echo "✅ Token found<br>";
            echo "User ID: {$tokenData['user_id']}<br>";
            echo "Email: {$tokenData['email']}<br>";
            echo "Expires: {$tokenData['expires_at']}<br>";
            echo "Used: " . ($tokenData['used_at'] ?: 'Not used') . "<br>";
            
            if ($tokenData['used_at']) {
                echo "❌ Token has already been used<br>";
            } elseif (strtotime($tokenData['expires_at']) < time()) {
                echo "❌ Token has expired<br>";
            } else {
                echo "✅ Token is valid for verification<br>";
                
                // Simulate the verification process
                $db->update('users', 
                    ['email_verified_at' => date('Y-m-d H:i:s')], 
                    'id = ?',
                    [$tokenData['user_id']]
                );
                
                $db->update('verification_tokens',
                    ['used_at' => date('Y-m-d H:i:s')],
                    'token = ?',
                    [$token['token']]
                );
                
                echo "✅ Verification completed successfully<br>";
                
                // Verify the changes
                $updatedUser = $db->fetch("SELECT email_verified_at FROM users WHERE id = ?", [$tokenData['user_id']]);
                echo "✅ User email verified at: " . ($updatedUser['email_verified_at'] ?: 'Not verified') . "<br>";
            }
        } else {
            echo "❌ Token not found<br>";
        }
    } else {
        echo "❌ No verification tokens found in database<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h2>Next Steps</h2>";
echo "1. Click the verification URL above to test the actual verification page<br>";
echo "2. Check if the page loads correctly and shows success/error messages<br>";
echo "3. Verify that the user's email_verified_at field is updated in the database<br>";
?>
