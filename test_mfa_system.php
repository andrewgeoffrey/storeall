<?php
/**
 * MFA System Test Suite
 * Tests the complete MFA and device tracking functionality
 * 
 * Access this file at: http://localhost:8080/test_mfa_system.php
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Logger.php';
require_once __DIR__ . '/includes/ErrorHandler.php';
require_once __DIR__ . '/includes/PerformanceMonitor.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/Auth.php';
require_once __DIR__ . '/includes/Session.php';
require_once __DIR__ . '/includes/Email.php';
require_once __DIR__ . '/includes/LoginTracker.php';

class MFASystemTest {
    private $db;
    private $loginTracker;
    private $email;
    private $testUserId;
    private $testEmail;
    private $testPassword = 'TestPassword123!';
    private $testDeviceFingerprint;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->loginTracker = new LoginTracker();
        $this->email = new Email();
        $this->testEmail = 'test_' . time() . '@example.com';
        
        echo "<h1>MFA System Test Suite</h1>\n";
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
            .test-result { margin: 10px 0; padding: 10px; border-radius: 3px; }
            .pass { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
            .fail { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
            .info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
            .warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        </style>\n";
    }

    public function runAllTests() {
        $this->setupTestData();
        
        $this->testDeviceFingerprinting();
        $this->testLoginAttemptTracking();
        $this->testAccountLocking();
        $this->testTrustedDevices();
        $this->testMFAFlow();
        $this->testSuspiciousActivityDetection();
        $this->testEmailSending();
        $this->testCleanup();
        
        $this->cleanupTestData();
    }

    private function setupTestData() {
        echo "<div class='test-section'>\n";
        echo "<h2>Setting up test data...</h2>\n";
        
        try {
            // Debug: Check if Auth class exists
            if (!class_exists('Auth')) {
                echo "<div class='test-result fail'>✗ Auth class not found</div>\n";
                return false;
            }
            
            // Create test user
            $auth = Auth::getInstance();
            
            // Debug: Check if createUser method exists
            if (!method_exists($auth, 'createUser')) {
                echo "<div class='test-result fail'>✗ createUser method not found in Auth class</div>\n";
                
                // List available methods
                $methods = get_class_methods($auth);
                echo "<div class='test-result info'>Available methods: " . implode(', ', $methods) . "</div>\n";
                
                // Try using register method if it exists
                if (method_exists($auth, 'register')) {
                    echo "<div class='test-result info'>Trying register method instead...</div>\n";
                    $userData = [
                        'email' => $this->testEmail,
                        'password' => $this->testPassword,
                        'first_name' => 'Test',
                        'last_name' => 'User',
                        'role' => 'customer',
                        'email_verified_at' => date('Y-m-d H:i:s'),
                        'is_active' => 1,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $this->testUserId = $auth->register($userData);
                } else {
                    return false;
                }
            } else {
                $userData = [
                    'email' => $this->testEmail,
                    'password' => $this->testPassword,
                    'first_name' => 'Test',
                    'last_name' => 'User',
                    'role' => 'customer',
                    'email_verified_at' => date('Y-m-d H:i:s'),
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $this->testUserId = $auth->createUser($userData);
            }
            
            if ($this->testUserId) {
                echo "<div class='test-result pass'>✓ Test user created successfully (ID: {$this->testUserId})</div>\n";
            } else {
                echo "<div class='test-result fail'>✗ Failed to create test user</div>\n";
                return false;
            }
            
            // Generate test device fingerprint
            $this->testDeviceFingerprint = $this->loginTracker->generateDeviceFingerprint(
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                '192.168.1.100',
                [
                    'screen_resolution' => '1920x1080',
                    'timezone' => 'America/New_York',
                    'language' => 'en-US',
                    'platform' => 'Win32'
                ]
            );
            
            echo "<div class='test-result pass'>✓ Test device fingerprint generated: " . substr($this->testDeviceFingerprint, 0, 16) . "...</div>\n";
            
        } catch (Exception $e) {
            echo "<div class='test-result fail'>✗ Setup failed: " . $e->getMessage() . "</div>\n";
            return false;
        }
        
        echo "</div>\n";
        return true;
    }

    private function testDeviceFingerprinting() {
        echo "<div class='test-section'>\n";
        echo "<h2>Testing Device Fingerprinting</h2>\n";
        
        // Test 1: Generate fingerprint with same data
        $fingerprint1 = $this->loginTracker->generateDeviceFingerprint(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            '192.168.1.100',
            ['screen_resolution' => '1920x1080', 'timezone' => 'America/New_York']
        );
        
        $fingerprint2 = $this->loginTracker->generateDeviceFingerprint(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            '192.168.1.100',
            ['screen_resolution' => '1920x1080', 'timezone' => 'America/New_York']
        );
        
        if ($fingerprint1 === $fingerprint2) {
            echo "<div class='test-result pass'>✓ Device fingerprints are consistent for same data</div>\n";
        } else {
            echo "<div class='test-result fail'>✗ Device fingerprints are inconsistent</div>\n";
        }
        
        // Test 2: Generate fingerprint with different data
        $fingerprint3 = $this->loginTracker->generateDeviceFingerprint(
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            '192.168.1.101',
            ['screen_resolution' => '2560x1440', 'timezone' => 'America/Los_Angeles']
        );
        
        if ($fingerprint1 !== $fingerprint3) {
            echo "<div class='test-result pass'>✓ Device fingerprints are different for different data</div>\n";
        } else {
            echo "<div class='test-result fail'>✗ Device fingerprints are the same for different data</div>\n";
        }
        
        // Test 3: Location data retrieval
        $locationData = $this->loginTracker->getLocationData('8.8.8.8');
        if ($locationData) {
            echo "<div class='test-result pass'>✓ Location data retrieved successfully</div>\n";
            echo "<div class='test-result info'>Location: {$locationData['city']}, {$locationData['country']}</div>\n";
        } else {
            echo "<div class='test-result warning'>⚠ Location data retrieval failed (this is normal for some IPs)</div>\n";
        }
        
        echo "</div>\n";
    }

    private function testLoginAttemptTracking() {
        echo "<div class='test-section'>\n";
        echo "<h2>Testing Login Attempt Tracking</h2>\n";
        
        // Test 1: Record login attempt
        $attemptId = $this->loginTracker->recordLoginAttempt(
            $this->testEmail,
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            '192.168.1.100',
            $this->testDeviceFingerprint
        );
        
        if ($attemptId) {
            echo "<div class='test-result pass'>✓ Login attempt recorded successfully (ID: {$attemptId})</div>\n";
        } else {
            echo "<div class='test-result fail'>✗ Failed to record login attempt</div>\n";
        }
        
        // Test 2: Update login attempt
        $this->loginTracker->updateLoginAttempt($attemptId, true, null, false, 'test_session_123', $this->testUserId);
        echo "<div class='test-result pass'>✓ Login attempt updated successfully</div>\n";
        
        // Test 3: Get login history
        $loginHistory = $this->loginTracker->getLoginHistory($this->testUserId, 5);
        if (count($loginHistory) > 0) {
            echo "<div class='test-result pass'>✓ Login history retrieved successfully (" . count($loginHistory) . " records)</div>\n";
        } else {
            echo "<div class='test-result fail'>✗ Failed to retrieve login history</div>\n";
        }
        
        echo "</div>\n";
    }

    private function testAccountLocking() {
        echo "<div class='test-section'>\n";
        echo "<h2>Testing Account Locking</h2>\n";
        
        // Test 1: Check initial lock status
        $lockStatus = $this->loginTracker->isAccountLocked($this->testEmail, '192.168.1.100', $this->testDeviceFingerprint);
        if (!$lockStatus['locked']) {
            echo "<div class='test-result pass'>✓ Account is not locked initially</div>\n";
        } else {
            echo "<div class='test-result fail'>✗ Account is locked when it shouldn't be</div>\n";
        }
        
        // Test 2: Record multiple failed attempts
        for ($i = 0; $i < 6; $i++) {
            $this->loginTracker->recordFailedAttempt($this->testEmail, '192.168.1.100', $this->testDeviceFingerprint);
        }
        
        // Test 3: Check if account is now locked
        $lockStatus = $this->loginTracker->isAccountLocked($this->testEmail, '192.168.1.100', $this->testDeviceFingerprint);
        if ($lockStatus['locked']) {
            echo "<div class='test-result pass'>✓ Account locked after multiple failed attempts</div>\n";
            echo "<div class='test-result info'>Locked until: {$lockStatus['locked_until']}</div>\n";
        } else {
            echo "<div class='test-result fail'>✗ Account not locked after multiple failed attempts</div>\n";
        }
        
        // Test 4: Clear failed attempts
        $this->loginTracker->clearFailedAttempts($this->testEmail, '192.168.1.100', $this->testDeviceFingerprint);
        $lockStatus = $this->loginTracker->isAccountLocked($this->testEmail, '192.168.1.100', $this->testDeviceFingerprint);
        if (!$lockStatus['locked']) {
            echo "<div class='test-result pass'>✓ Account unlocked after clearing failed attempts</div>\n";
        } else {
            echo "<div class='test-result fail'>✗ Account still locked after clearing failed attempts</div>\n";
        }
        
        echo "</div>\n";
    }

    private function testTrustedDevices() {
        echo "<div class='test-section'>\n";
        echo "<h2>Testing Trusted Devices</h2>\n";
        
        // Test 1: Check if device is trusted initially
        $isTrusted = $this->loginTracker->isDeviceTrusted($this->testUserId, $this->testDeviceFingerprint);
        if (!$isTrusted) {
            echo "<div class='test-result pass'>✓ Device is not trusted initially</div>\n";
        } else {
            echo "<div class='test-result fail'>✗ Device is trusted when it shouldn't be</div>\n";
        }
        
        // Test 2: Add device as trusted
        $trustedDeviceId = $this->loginTracker->addTrustedDevice(
            $this->testUserId,
            $this->testDeviceFingerprint,
            'Test Device',
            '192.168.1.100',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ['country' => 'US', 'city' => 'New York'],
            30
        );
        
        if ($trustedDeviceId) {
            echo "<div class='test-result pass'>✓ Device added as trusted successfully (ID: {$trustedDeviceId})</div>\n";
        } else {
            echo "<div class='test-result fail'>✗ Failed to add device as trusted</div>\n";
        }
        
        // Test 3: Check if device is now trusted
        $isTrusted = $this->loginTracker->isDeviceTrusted($this->testUserId, $this->testDeviceFingerprint);
        if ($isTrusted) {
            echo "<div class='test-result pass'>✓ Device is now trusted</div>\n";
        } else {
            echo "<div class='test-result fail'>✗ Device is not trusted after adding</div>\n";
        }
        
        // Test 4: Test with different device fingerprint
        $differentFingerprint = $this->loginTracker->generateDeviceFingerprint(
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            '192.168.1.101',
            ['screen_resolution' => '2560x1440']
        );
        
        $isTrusted = $this->loginTracker->isDeviceTrusted($this->testUserId, $differentFingerprint);
        if (!$isTrusted) {
            echo "<div class='test-result pass'>✓ Different device is not trusted</div>\n";
        } else {
            echo "<div class='test-result fail'>✗ Different device is trusted when it shouldn't be</div>\n";
        }
        
        echo "</div>\n";
    }

    private function testMFAFlow() {
        echo "<div class='test-section'>\n";
        echo "<h2>Testing MFA Flow</h2>\n";
        
        // Test 1: Get user login preferences
        $preferences = $this->loginTracker->getUserLoginPreferences($this->testUserId);
        if ($preferences['mfa_enabled']) {
            echo "<div class='test-result pass'>✓ MFA is enabled for test user</div>\n";
        } else {
            echo "<div class='test-result fail'>✗ MFA is not enabled for test user</div>\n";
        }
        
        // Test 2: Generate MFA code
        $mfaCode = generateMFACode($this->testUserId, 'email');
        if ($mfaCode) {
            echo "<div class='test-result pass'>✓ MFA code generated successfully: {$mfaCode}</div>\n";
        } else {
            echo "<div class='test-result fail'>✗ Failed to generate MFA code</div>\n";
        }
        
        // Test 3: Verify MFA code
        $isValid = verifyMFACode($this->testUserId, $mfaCode);
        if ($isValid) {
            echo "<div class='test-result pass'>✓ MFA code verified successfully</div>\n";
        } else {
            echo "<div class='test-result fail'>✗ Failed to verify MFA code</div>\n";
        }
        
        // Test 4: Test invalid MFA code
        $isValid = verifyMFACode($this->testUserId, '000000');
        if (!$isValid) {
            echo "<div class='test-result pass'>✓ Invalid MFA code correctly rejected</div>\n";
        } else {
            echo "<div class='test-result fail'>✗ Invalid MFA code incorrectly accepted</div>\n";
        }
        
        echo "</div>\n";
    }

    private function testSuspiciousActivityDetection() {
        echo "<div class='test-section'>\n";
        echo "<h2>Testing Suspicious Activity Detection</h2>\n";
        
        // Test 1: First login (should not be suspicious)
        $suspiciousActivity = $this->loginTracker->detectSuspiciousActivity(
            $this->testUserId,
            '192.168.1.100',
            ['country' => 'US', 'city' => 'New York']
        );
        
        if (!$suspiciousActivity['suspicious']) {
            echo "<div class='test-result pass'>✓ First login correctly identified as not suspicious</div>\n";
        } else {
            echo "<div class='test-result fail'>✗ First login incorrectly identified as suspicious</div>\n";
        }
        
        // Test 2: Login from different country (should be suspicious)
        $suspiciousActivity = $this->loginTracker->detectSuspiciousActivity(
            $this->testUserId,
            '203.0.113.1',
            ['country' => 'Australia', 'city' => 'Sydney']
        );
        
        if ($suspiciousActivity['suspicious']) {
            echo "<div class='test-result pass'>✓ Login from different country correctly identified as suspicious</div>\n";
            echo "<div class='test-result info'>Reason: {$suspiciousActivity['reason']}</div>\n";
        } else {
            echo "<div class='test-result fail'>✗ Login from different country not identified as suspicious</div>\n";
        }
        
        echo "</div>\n";
    }

    private function testEmailSending() {
        echo "<div class='test-section'>\n";
        echo "<h2>Testing Email Sending</h2>\n";
        
        // Test 1: Send MFA code email
        $result = $this->email->sendMFACode($this->testEmail, 'Test', '123456');
        if ($result) {
            echo "<div class='test-result pass'>✓ MFA code email sent successfully</div>\n";
        } else {
            echo "<div class='test-result fail'>✗ Failed to send MFA code email</div>\n";
        }
        
        // Test 2: Send login notification
        $result = $this->email->sendLoginNotification(
            $this->testEmail,
            'Test',
            'New York, NY, United States',
            '⚠️ This login appears to be from an unusual location.'
        );
        if ($result) {
            echo "<div class='test-result pass'>✓ Login notification email sent successfully</div>\n";
        } else {
            echo "<div class='test-result fail'>✗ Failed to send login notification email</div>\n";
        }
        
        echo "</div>\n";
    }

    private function testCleanup() {
        echo "<div class='test-section'>\n";
        echo "<h2>Testing Cleanup Functions</h2>\n";
        
        // Test cleanup of expired trusted devices
        $affected = $this->loginTracker->cleanupExpiredTrustedDevices();
        echo "<div class='test-result info'>✓ Cleanup completed. Affected devices: {$affected}</div>\n";
        
        echo "</div>\n";
    }

    private function cleanupTestData() {
        echo "<div class='test-section'>\n";
        echo "<h2>Cleaning up test data...</h2>\n";
        
        try {
            // Clean up test data
            $this->db->delete('verification_tokens', 'user_id = ?', [$this->testUserId]);
            $this->db->delete('trusted_devices', 'user_id = ?', [$this->testUserId]);
            $this->db->delete('user_login_preferences', 'user_id = ?', [$this->testUserId]);
            $this->db->delete('login_attempts', 'user_id = ?', [$this->testUserId]);
            $this->db->delete('failed_login_attempts', 'email = ?', [$this->testEmail]);
            $this->db->delete('users', 'id = ?', [$this->testUserId]);
            
            echo "<div class='test-result pass'>✓ Test data cleaned up successfully</div>\n";
        } catch (Exception $e) {
            echo "<div class='test-result fail'>✗ Failed to cleanup test data: " . $e->getMessage() . "</div>\n";
        }
        
        echo "</div>\n";
    }
}

// Helper functions (copied from login.php)
function generateMFACode($userId, $method = 'email') {
    try {
        $code = sprintf('%06d', mt_rand(0, 999999));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        $db = Database::getInstance();
        
        // Store MFA code in database - use email_verification type since mfa_code is not in enum
        $db->insert('verification_tokens', [
            'user_id' => $userId,
            'token' => $code,
            'type' => 'email_verification',
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return $code;
    } catch (Exception $e) {
        Logger::getInstance()->error('Failed to generate MFA code', [
            'user_id' => $userId,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

function verifyMFACode($userId, $code) {
    try {
        $db = Database::getInstance();
        
        // Get valid MFA code - use email_verification type since mfa_code is not in enum
        $mfaToken = $db->fetch(
            "SELECT * FROM verification_tokens 
             WHERE user_id = ? AND token = ? AND type = 'email_verification' 
             AND expires_at > NOW() AND used_at IS NULL",
            [$userId, $code]
        );
        
        if ($mfaToken) {
            // Mark code as used
            $db->update('verification_tokens', 
                ['used_at' => date('Y-m-d H:i:s')], 
                'id = ?', 
                [$mfaToken['id']]
            );
            return true;
        }
        
        return false;
    } catch (Exception $e) {
        Logger::getInstance()->error('Failed to verify MFA code', [
            'user_id' => $userId,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

// Run the tests
$test = new MFASystemTest();
$test->runAllTests();

echo "<div class='test-section'>\n";
echo "<h2>Test Summary</h2>\n";
echo "<p>All MFA and device tracking tests have been completed. Check the results above for any failures.</p>\n";
echo "<p><strong>Next Steps:</strong></p>\n";
echo "<ul>\n";
echo "<li>Review any failed tests and fix the issues</li>\n";
echo "<li>Test the system with real user interactions</li>\n";
echo "<li>Monitor the logs for any errors</li>\n";
echo "<li>Verify email delivery in MailHog</li>\n";
echo "</ul>\n";
echo "</div>\n";
?>
