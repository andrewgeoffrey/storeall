<?php
// Test verification process
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/Logger.php';

echo "Testing verification process...\n";

try {
    $db = Database::getInstance();
    
    // Check if verification_tokens table exists
    $tableExists = $db->tableExists('verification_tokens');
    echo "Verification tokens table exists: " . ($tableExists ? 'YES' : 'NO') . "\n";
    
    if ($tableExists) {
        // Check for any verification tokens
        $tokens = $db->fetchAll("SELECT * FROM verification_tokens ORDER BY created_at DESC LIMIT 5");
        echo "Found " . count($tokens) . " verification tokens:\n";
        
        foreach ($tokens as $token) {
            echo "- Token: " . substr($token['token'], 0, 20) . "...\n";
            echo "  User ID: " . $token['user_id'] . "\n";
            echo "  Type: " . $token['type'] . "\n";
            echo "  Expires: " . $token['expires_at'] . "\n";
            echo "  Used: " . ($token['used_at'] ? $token['used_at'] : 'Not used') . "\n";
            echo "  Created: " . $token['created_at'] . "\n\n";
        }
        
        // Check users table
        $users = $db->fetchAll("SELECT id, email, email_verified_at FROM users ORDER BY created_at DESC LIMIT 5");
        echo "Found " . count($users) . " users:\n";
        
        foreach ($users as $user) {
            echo "- User ID: " . $user['id'] . "\n";
            echo "  Email: " . $user['email'] . "\n";
            echo "  Verified: " . ($user['email_verified_at'] ? $user['email_verified_at'] : 'Not verified') . "\n\n";
        }
    }
    
    // Test the verification query
    if (isset($_GET['token'])) {
        $token = $_GET['token'];
        echo "Testing token: " . substr($token, 0, 20) . "...\n";
        
        $tokenData = $db->fetch(
            "SELECT vt.user_id, vt.expires_at, vt.used_at, u.email 
             FROM verification_tokens vt 
             JOIN users u ON vt.user_id = u.id 
             WHERE vt.token = ? AND vt.type = 'email_verification'",
            [$token]
        );
        
        if ($tokenData) {
            echo "Token found!\n";
            echo "User ID: " . $tokenData['user_id'] . "\n";
            echo "Email: " . $tokenData['email'] . "\n";
            echo "Expires: " . $tokenData['expires_at'] . "\n";
            echo "Used: " . ($tokenData['used_at'] ? $tokenData['used_at'] : 'Not used') . "\n";
            echo "Expired: " . (strtotime($tokenData['expires_at']) < time() ? 'YES' : 'NO') . "\n";
        } else {
            echo "Token not found!\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
