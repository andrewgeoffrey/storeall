<?php
// Test script to debug registration and verification token creation
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/Logger.php';
require_once 'includes/Email.php';

echo "<h1>Registration Debug Test</h1>";

try {
    $db = Database::getInstance();
    
    // Test data
    $testData = [
        'firstName' => 'Test',
        'lastName' => 'User',
        'email' => 'test' . time() . '@example.com',
        'confirmEmail' => 'test' . time() . '@example.com',
        'companyName' => 'Test Company',
        'phone' => '555-123-4567',
        'website' => 'https://testcompany.com',
        'password' => 'TestPassword123!',
        'confirmPassword' => 'TestPassword123!',
        'terms' => 'on',
        'newsletter' => 'on'
    ];
    
    echo "<h2>1. Testing registration process</h2>";
    echo "Test email: {$testData['email']}<br>";
    
    // Start transaction
    $db->beginTransaction();
    
    try {
        // Check if user already exists
        $existingUser = $db->fetch("SELECT id FROM users WHERE email = ?", [$testData['email']]);
        if ($existingUser) {
            echo "❌ User already exists<br>";
            $db->rollback();
            return;
        }
        
        // Create user
        $userId = $db->insert('users', [
            'first_name' => $testData['firstName'],
            'last_name' => $testData['lastName'],
            'email' => $testData['email'],
            'password_hash' => password_hash($testData['password'], PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        echo "✅ User created with ID: {$userId}<br>";
        
        // Create subdomain
        $subdomain = createSubdomain($testData['companyName']);
        
        // Check if subdomain already exists and add a number if needed
        $existingOrg = $db->fetch("SELECT id FROM organizations WHERE subdomain = ?", [$subdomain]);
        if ($existingOrg) {
            $counter = 1;
            do {
                $newSubdomain = $subdomain . $counter;
                $existingOrg = $db->fetch("SELECT id FROM organizations WHERE subdomain = ?", [$newSubdomain]);
                $counter++;
            } while ($existingOrg && $counter < 100);
            $subdomain = $newSubdomain;
        }
        
        echo "✅ Subdomain created: {$subdomain}<br>";
        
        // Create organization
        $orgId = $db->insert('organizations', [
            'name' => $testData['companyName'],
            'subdomain' => $subdomain,
            'domain' => $testData['website'] ?: null,
            'tier' => 'tier1',
            'status' => 'trial',
            'trial_ends_at' => date('Y-m-d H:i:s', strtotime('+14 days')),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        echo "✅ Organization created with ID: {$orgId}<br>";
        
        // Assign owner role
        $db->insert('user_roles', [
            'user_id' => $userId,
            'role' => 'owner',
            'organization_id' => $orgId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        echo "✅ User role assigned<br>";
        
        // Create primary location
        $locationId = $db->insert('locations', [
            'organization_id' => $orgId,
            'name' => $testData['companyName'] . ' - Main Location',
            'address' => 'Address to be updated',
            'phone' => $testData['phone'] ?: null,
            'email' => $testData['email'],
            'is_primary' => true,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        echo "✅ Primary location created with ID: {$locationId}<br>";
        
        // Generate email verification token
        $verificationToken = bin2hex(random_bytes(32));
        $verificationExpiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        echo "✅ Verification token generated: " . substr($verificationToken, 0, 20) . "...<br>";
        echo "✅ Token expires: {$verificationExpiry}<br>";
        
        // Store verification token in database
        $tokenId = $db->insert('verification_tokens', [
            'user_id' => $userId,
            'token' => $verificationToken,
            'type' => 'email_verification',
            'expires_at' => $verificationExpiry,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        echo "✅ Verification token stored with ID: {$tokenId}<br>";
        
        // Commit transaction
        $db->commit();
        echo "✅ Transaction committed successfully<br>";
        
        // Verify token was created
        $storedToken = $db->fetch("SELECT * FROM verification_tokens WHERE id = ?", [$tokenId]);
        if ($storedToken) {
            echo "✅ Token verified in database<br>";
            echo "Token: " . substr($storedToken['token'], 0, 20) . "...<br>";
            echo "User ID: {$storedToken['user_id']}<br>";
            echo "Type: {$storedToken['type']}<br>";
            echo "Expires: {$storedToken['expires_at']}<br>";
        } else {
            echo "❌ Token not found in database<br>";
        }
        
        // Test email sending
        echo "<h2>2. Testing email sending</h2>";
        if (class_exists('Email')) {
            $emailResult = Email::getInstance()->sendWelcomeEmail($testData['email'], $testData['firstName'], $verificationToken);
            echo $emailResult ? "✅ Email sent successfully" : "❌ Email sending failed";
            echo "<br>";
        } else {
            echo "❌ Email class not available<br>";
        }
        
        // Test verification URL
        echo "<h2>3. Testing verification URL</h2>";
        $verificationUrl = APP_URL . '/verify-email.php?token=' . $verificationToken;
        echo "Verification URL: <a href='{$verificationUrl}' target='_blank'>{$verificationUrl}</a><br>";
        
        // Test verification process
        echo "<h2>4. Testing verification process</h2>";
        $tokenData = $db->fetch(
            "SELECT vt.user_id, vt.expires_at, vt.used_at, u.email 
             FROM verification_tokens vt 
             JOIN users u ON vt.user_id = u.id 
             WHERE vt.token = ? AND vt.type = 'email_verification'",
            [$verificationToken]
        );
        
        if ($tokenData) {
            echo "✅ Token found for verification<br>";
            echo "User ID: {$tokenData['user_id']}<br>";
            echo "Email: {$tokenData['email']}<br>";
            echo "Expires: {$tokenData['expires_at']}<br>";
            echo "Used: " . ($tokenData['used_at'] ?: 'Not used') . "<br>";
            
            // Simulate verification
            if (!$tokenData['used_at'] && strtotime($tokenData['expires_at']) > time()) {
                echo "✅ Token is valid for verification<br>";
                
                // Update user's email verification status
                $db->update('users', 
                    ['email_verified_at' => date('Y-m-d H:i:s')], 
                    'id = ?',
                    [$tokenData['user_id']]
                );
                
                // Mark token as used
                $db->update('verification_tokens',
                    ['used_at' => date('Y-m-d H:i:s')],
                    'token = ?',
                    [$verificationToken]
                );
                
                echo "✅ Email verification completed successfully<br>";
                
                // Verify the changes
                $updatedUser = $db->fetch("SELECT email_verified_at FROM users WHERE id = ?", [$tokenData['user_id']]);
                $updatedToken = $db->fetch("SELECT used_at FROM verification_tokens WHERE token = ?", [$verificationToken]);
                
                echo "✅ User email verified at: " . ($updatedUser['email_verified_at'] ?: 'Not verified') . "<br>";
                echo "✅ Token used at: " . ($updatedToken['used_at'] ?: 'Not used') . "<br>";
            } else {
                echo "❌ Token is not valid for verification<br>";
            }
        } else {
            echo "❌ Token not found for verification<br>";
        }
        
    } catch (Exception $e) {
        $db->rollback();
        echo "❌ Error during registration: " . $e->getMessage() . "<br>";
        echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

/**
 * Create a URL-friendly subdomain from company name
 */
function createSubdomain($companyName) {
    // Remove special characters and convert to lowercase
    $subdomain = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($companyName));
    
    // Limit length
    if (strlen($subdomain) > 50) {
        $subdomain = substr($subdomain, 0, 50);
    }
    
    // Ensure it's not empty
    if (empty($subdomain)) {
        $subdomain = 'company' . time();
    }
    
    return $subdomain;
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "This test should create a new user, organization, and verification token.<br>";
echo "If successful, you should be able to click the verification URL above.<br>";
?>
