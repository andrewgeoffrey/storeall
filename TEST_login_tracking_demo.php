<?php
// Demo script to showcase login tracking and MFA suppression functionality
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/LoginTracker.php';

echo "<h1>🔐 Login Tracking & MFA Suppression Demo</h1>";

try {
    $db = Database::getInstance();
    $loginTracker = new LoginTracker();
    
    echo "<h2>📊 Database Tables Created</h2>";
    echo "<ul>";
    echo "<li>✅ <strong>login_attempts</strong> - Tracks all login attempts with environment data</li>";
    echo "<li>✅ <strong>trusted_devices</strong> - Stores devices for MFA suppression</li>";
    echo "<li>✅ <strong>user_login_preferences</strong> - User login security preferences</li>";
    echo "<li>✅ <strong>failed_login_attempts</strong> - Rate limiting and account lockout</li>";
    echo "</ul>";
    
    echo "<h2>🔍 Environment Fingerprinting</h2>";
    
    // Simulate environment data
    $userAgent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36";
    $ipAddress = "192.168.1.100";
    $additionalData = [
        'screen_resolution' => '1920x1080',
        'timezone' => 'America/Denver',
        'language' => 'en-US',
        'platform' => 'Win32',
        'cookie_enabled' => 'true'
    ];
    
    $deviceFingerprint = $loginTracker->generateDeviceFingerprint($userAgent, $ipAddress, $additionalData);
    echo "<p><strong>Device Fingerprint:</strong> " . substr($deviceFingerprint, 0, 16) . "...</p>";
    
    // Get location data
    $locationData = $loginTracker->getLocationData($ipAddress);
    if ($locationData) {
        echo "<p><strong>Location Data:</strong></p>";
        echo "<ul>";
        echo "<li>Country: {$locationData['country']}</li>";
        echo "<li>Region: {$locationData['region']}</li>";
        echo "<li>City: {$locationData['city']}</li>";
        echo "<li>ISP: {$locationData['isp']}</li>";
        echo "<li>Mobile: " . ($locationData['mobile'] ? 'Yes' : 'No') . "</li>";
        echo "<li>Proxy: " . ($locationData['proxy'] ? 'Yes' : 'No') . "</li>";
        echo "</ul>";
    } else {
        echo "<p><em>Location data not available for local IP</em></p>";
    }
    
    echo "<h2>👥 User Login Preferences</h2>";
    
    // Get existing users
    $users = $db->fetchAll("SELECT id, email, first_name FROM users LIMIT 3");
    
    if ($users) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>User</th><th>MFA Enabled</th><th>Trusted Devices</th><th>MFA Method</th><th>Device Duration</th></tr>";
        
        foreach ($users as $user) {
            $preferences = $loginTracker->getUserLoginPreferences($user['id']);
            $trustedDevices = $loginTracker->getTrustedDevices($user['id']);
            
            echo "<tr>";
            echo "<td>{$user['first_name']} ({$user['email']})</td>";
            echo "<td>" . ($preferences['mfa_enabled'] ? '✅ Yes' : '❌ No') . "</td>";
            echo "<td>" . count($trustedDevices) . " devices</td>";
            echo "<td>{$preferences['mfa_method']}</td>";
            echo "<td>{$preferences['trusted_device_duration_days']} days</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>🔒 Security Features</h2>";
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px;'>";
    echo "<h3>✅ Rate Limiting & Account Lockout</h3>";
    echo "<ul>";
    echo "<li>Track failed login attempts per email/IP/device combination</li>";
    echo "<li>Automatic account lockout after 5 failed attempts</li>";
    echo "<li>15-minute lockout period</li>";
    echo "<li>Clear failed attempts on successful login</li>";
    echo "</ul>";
    
    echo "<h3>✅ Device Trusting & MFA Suppression</h3>";
    echo "<ul>";
    echo "<li>Users can mark devices as 'trusted' for 30 days</li>";
    echo "<li>MFA is suppressed for trusted devices</li>";
    echo "<li>Environment fingerprinting ensures device uniqueness</li>";
    echo "<li>Location tracking for suspicious activity detection</li>";
    echo "</ul>";
    
    echo "<h3>✅ Suspicious Activity Detection</h3>";
    echo "<ul>";
    echo "<li>Detect logins from different countries</li>";
    echo "<li>Track login patterns and unusual behavior</li>";
    echo "<li>Email notifications for suspicious logins</li>";
    echo "<li>Location-based security alerts</li>";
    echo "</ul>";
    
    echo "<h3>✅ Comprehensive Logging</h3>";
    echo "<ul>";
    echo "<li>All login attempts logged with full environment data</li>";
    echo "<li>IP address, user agent, device fingerprint</li>";
    echo "<li>Location data, success/failure status</li>";
    echo "<li>MFA usage and session tracking</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h2>🧪 Test the Login API</h2>";
    echo "<p>You can now test the login functionality with environment tracking:</p>";
    echo "<ul>";
    echo "<li><strong>API Endpoint:</strong> <code>/api/login.php</code></li>";
    echo "<li><strong>Method:</strong> POST</li>";
    echo "<li><strong>Required Fields:</strong> email, password</li>";
    echo "<li><strong>Optional Fields:</strong> mfa_code, remember_device, device_name</li>";
    echo "</ul>";
    
    echo "<h3>📝 Example Login Request:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
    echo "POST /api/login.php\n";
    echo "Content-Type: application/x-www-form-urlencoded\n\n";
    echo "email=user@example.com\n";
    echo "password=userpassword\n";
    echo "remember_device=1\n";
    echo "device_name=Home Computer\n";
    echo "screen_resolution=1920x1080\n";
    echo "timezone=America/Denver\n";
    echo "language=en-US\n";
    echo "platform=Win32\n";
    echo "cookie_enabled=true";
    echo "</pre>";
    
    echo "<h3>📋 Example Response:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => 1,
            'email' => 'user@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'role' => 'customer'
        ],
        'session_id' => 'abc123...',
        'device_trusted' => true,
        'trusted_until' => '2025-09-18 21:53:59',
        'suspicious_activity' => [
            'suspicious' => false,
            'reason' => null
        ]
    ], JSON_PRETTY_PRINT);
    echo "</pre>";
    
    echo "<h2>🎯 Key Benefits</h2>";
    echo "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px;'>";
    
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 8px;'>";
    echo "<h4>🔒 Enhanced Security</h4>";
    echo "<ul>";
    echo "<li>Environment fingerprinting</li>";
    echo "<li>Location-based security</li>";
    echo "<li>Rate limiting & lockouts</li>";
    echo "<li>Suspicious activity detection</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 8px;'>";
    echo "<h4>👤 Better User Experience</h4>";
    echo "<ul>";
    echo "<li>Optional MFA suppression</li>";
    echo "<li>Trusted device management</li>";
    echo "<li>Clear security feedback</li>";
    echo "<li>Flexible security preferences</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px;'>";
    echo "<h4>📊 Comprehensive Monitoring</h4>";
    echo "<ul>";
    echo "<li>Detailed login logs</li>";
    echo "<li>Environment tracking</li>";
    echo "<li>Security analytics</li>";
    echo "<li>Audit trail</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px;'>";
    echo "<h4>⚡ Performance Optimized</h4>";
    echo "<ul>";
    echo "<li>Efficient database queries</li>";
    echo "<li>Indexed for speed</li>";
    echo "<li>Minimal API overhead</li>";
    echo "<li>Scalable architecture</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "</div>";
    
    echo "<hr>";
    echo "<h2>🚀 Next Steps</h2>";
    echo "<ol>";
    echo "<li><strong>Test the Login API</strong> - Try logging in with different devices</li>";
    echo "<li><strong>Implement Frontend</strong> - Create login modal with environment data collection</li>";
    echo "<li><strong>Add MFA UI</strong> - Build MFA code input interface</li>";
    echo "<li><strong>Trusted Device Management</strong> - Allow users to manage trusted devices</li>";
    echo "<li><strong>Security Dashboard</strong> - Show users their login history and security settings</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error</h2>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<p><a href='/'>← Back to StoreAll.io</a></p>";
?>
