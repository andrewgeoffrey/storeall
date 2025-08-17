<?php
// Test script to debug and fix email verification
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/Logger.php';

echo "<h1>Email Verification Debug Test</h1>";

try {
    $db = Database::getInstance();
    
    // Check if verification_tokens table exists and has data
    echo "<h2>1. Checking verification_tokens table</h2>";
    
    $tables = $db->fetchAll("SHOW TABLES LIKE 'verification_tokens'");
    if (empty($tables)) {
        echo "❌ verification_tokens table does not exist<br>";
    } else {
        echo "✅ verification_tokens table exists<br>";
        
        // Check table structure
        $columns = $db->fetchAll("DESCRIBE verification_tokens");
        echo "<h3>Table structure:</h3>";
        echo "<ul>";
        foreach ($columns as $column) {
            echo "<li>{$column['Field']} - {$column['Type']} - {$column['Null']} - {$column['Key']}</li>";
        }
        echo "</ul>";
        
        // Check for tokens
        $tokens = $db->fetchAll("SELECT * FROM verification_tokens ORDER BY created_at DESC LIMIT 5");
        echo "<h3>Recent verification tokens:</h3>";
        if (empty($tokens)) {
            echo "❌ No verification tokens found<br>";
        } else {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>User ID</th><th>Token</th><th>Type</th><th>Expires</th><th>Used</th><th>Created</th></tr>";
            foreach ($tokens as $token) {
                echo "<tr>";
                echo "<td>{$token['id']}</td>";
                echo "<td>{$token['user_id']}</td>";
                echo "<td>" . substr($token['token'], 0, 20) . "...</td>";
                echo "<td>{$token['type']}</td>";
                echo "<td>{$token['expires_at']}</td>";
                echo "<td>" . ($token['used_at'] ?: 'Not used') . "</td>";
                echo "<td>{$token['created_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    // Check users table
    echo "<h2>2. Checking users table</h2>";
    $users = $db->fetchAll("SELECT id, email, first_name, email_verified_at FROM users ORDER BY created_at DESC LIMIT 5");
    if (empty($users)) {
        echo "❌ No users found<br>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Email</th><th>First Name</th><th>Email Verified</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['first_name']}</td>";
            echo "<td>" . ($user['email_verified_at'] ?: 'Not verified') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test verification process with a sample token
    echo "<h2>3. Testing verification process</h2>";
    if (!empty($tokens)) {
        $testToken = $tokens[0]['token'];
        echo "Testing with token: " . substr($testToken, 0, 20) . "...<br>";
        
        // Simulate the verification query
        $tokenData = $db->fetch(
            "SELECT vt.user_id, vt.expires_at, vt.used_at, u.email 
             FROM verification_tokens vt 
             JOIN users u ON vt.user_id = u.id 
             WHERE vt.token = ? AND vt.type = 'email_verification'",
            [$testToken]
        );
        
        if ($tokenData) {
            echo "✅ Token found in database<br>";
            echo "User ID: {$tokenData['user_id']}<br>";
            echo "Email: {$tokenData['email']}<br>";
            echo "Expires: {$tokenData['expires_at']}<br>";
            echo "Used: " . ($tokenData['used_at'] ?: 'Not used') . "<br>";
            
            // Check if token is expired
            if (strtotime($tokenData['expires_at']) < time()) {
                echo "❌ Token is expired<br>";
            } else {
                echo "✅ Token is not expired<br>";
            }
            
            if ($tokenData['used_at']) {
                echo "❌ Token has already been used<br>";
            } else {
                echo "✅ Token has not been used<br>";
            }
        } else {
            echo "❌ Token not found in database<br>";
        }
    }
    
    // Test the actual verification URL
    echo "<h2>4. Testing verification URL</h2>";
    if (!empty($tokens)) {
        $testToken = $tokens[0]['token'];
        $verificationUrl = APP_URL . '/verify-email.php?token=' . $testToken;
        echo "Verification URL: <a href='{$verificationUrl}' target='_blank'>{$verificationUrl}</a><br>";
        echo "APP_URL: " . APP_URL . "<br>";
    }
    
    // Test email sending
    echo "<h2>5. Testing email sending</h2>";
    if (!empty($users)) {
        $testUser = $users[0];
        echo "Testing with user: {$testUser['email']}<br>";
        
        // Generate a new test token
        $testVerificationToken = bin2hex(random_bytes(32));
        $testVerificationExpiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Store test token
        $db->insert('verification_tokens', [
            'user_id' => $testUser['id'],
            'token' => $testVerificationToken,
            'type' => 'email_verification',
            'expires_at' => $testVerificationExpiry,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        echo "✅ Test token created: " . substr($testVerificationToken, 0, 20) . "...<br>";
        
        // Test verification URL
        $testVerificationUrl = APP_URL . '/verify-email.php?token=' . $testVerificationToken;
        echo "Test verification URL: <a href='{$testVerificationUrl}' target='_blank'>{$testVerificationUrl}</a><br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "If you see verification tokens in the database but verification is failing, the issue might be:";
echo "<ul>";
echo "<li>Incorrect APP_URL in config.php</li>";
echo "<li>Docker networking issues</li>";
echo "<li>Token expiration</li>";
echo "<li>Database connection issues</li>";
echo "</ul>";
echo "<p>Try clicking the test verification URLs above to see if they work.</p>";
?>
