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
        
        echo "<h2>🧪 Test Instructions</h2>";
        echo "<ol>";
        echo "<li>Click the verification URL above</li>";
        echo "<li>You should see the verification page (not redirect to home)</li>";
        echo "<li>If it works, you'll see a success message</li>";
        echo "<li>If it redirects to home, there's still an issue</li>";
        echo "</ol>";
        
        echo "<h2>🔍 Debug Information</h2>";
        echo "<p><strong>APP_URL:</strong> " . APP_URL . "</p>";
        echo "<p><strong>Token Length:</strong> " . strlen($token) . " characters</p>";
        echo "<p><strong>Token Format:</strong> " . (preg_match('/^[a-zA-Z0-9]+$/', $token) ? 'Valid' : 'Invalid') . "</p>";
        
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
echo "<h2>📋 Manual Test Steps</h2>";
echo "<ol>";
echo "<li>Register a new user at <a href='/'>StoreAll.io</a></li>";
echo "<li>Check MailHog at <a href='http://localhost:8025' target='_blank'>http://localhost:8025</a></li>";
echo "<li>Click the verification link in the email</li>";
echo "<li>The URL should look like: <code>http://localhost:8080/verify-email/abc123...</code></li>";
echo "<li>You should see the verification page, not redirect to home</li>";
echo "</ol>";
?>
