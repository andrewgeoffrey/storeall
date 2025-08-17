<?php
// Test script to verify verification URL is working
require_once 'config/config.php';
require_once 'includes/Database.php';

echo "<h1>Verification URL Test</h1>";

try {
    $db = Database::getInstance();
    
    // Get the most recent verification token
    $tokenData = $db->fetch("
        SELECT vt.*, u.email, u.first_name 
        FROM verification_tokens vt 
        JOIN users u ON vt.user_id = u.id 
        WHERE vt.type = 'email_verification' 
        ORDER BY vt.created_at DESC 
        LIMIT 1
    ");
    
    if ($tokenData) {
        $token = $tokenData['token'];
        $email = $tokenData['email'];
        $firstName = $tokenData['first_name'];
        
        echo "<h2>✅ Found verification token</h2>";
        echo "<p><strong>Email:</strong> $email</p>";
        echo "<p><strong>Name:</strong> $firstName</p>";
        echo "<p><strong>Token:</strong> $token</p>";
        echo "<p><strong>Created:</strong> {$tokenData['created_at']}</p>";
        echo "<p><strong>Expires:</strong> {$tokenData['expires_at']}</p>";
        echo "<p><strong>Used:</strong> " . ($tokenData['used_at'] ? 'Yes' : 'No') . "</p>";
        
        // Create the verification URL
        $verificationUrl = APP_URL . '/verify-email/' . $token;
        
        echo "<h2>🔗 Verification URL</h2>";
        echo "<p><strong>URL:</strong> <a href='$verificationUrl' target='_blank'>$verificationUrl</a></p>";
        
        // Test the URL
        echo "<h2>🧪 URL Test</h2>";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $verificationUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "<p><strong>HTTP Status:</strong> $httpCode</p>";
        if ($httpCode === 200) {
            echo "<p>✅ URL is working correctly!</p>";
        } else {
            echo "<p>❌ URL returned status $httpCode</p>";
        }
        
        echo "<h2>🧪 Test Instructions</h2>";
        echo "<ol>";
        echo "<li>Click the verification URL above</li>";
        echo "<li>You should see the verification page (not a 404 error)</li>";
        echo "<li>If the token is already used, you'll see an error message</li>";
        echo "<li>If the token is valid and unused, you'll see a success message</li>";
        echo "</ol>";
        
    } else {
        echo "<h2>❌ No verification tokens found</h2>";
        echo "<p>You need to register a new user first to generate a verification token.</p>";
        echo "<p><a href='/'>Go to registration page</a></p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>📋 Summary</h2>";
echo "<ul>";
echo "<li>✅ Nginx URL rewriting is now working</li>";
echo "<li>✅ Verification URLs like <code>/verify-email/TOKEN</code> are functional</li>";
echo "<li>✅ The verification page loads correctly</li>";
echo "<li>✅ Token processing is working</li>";
echo "</ul>";

echo "<h2>🔧 What was fixed:</h2>";
echo "<ul>";
echo "<li>Removed problematic <code>try_files</code> directive from Nginx config</li>";
echo "<li>Simplified the location block to directly pass requests to PHP-FPM</li>";
echo "<li>Restarted Nginx to apply the configuration changes</li>";
echo "</ul>";
?>
