<?php
// API Login Endpoint with Environment Tracking and MFA Support
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/Logger.php';
require_once __DIR__ . '/../includes/Email.php';
require_once __DIR__ . '/../includes/LoginTracker.php';

// Set content type to JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get form data
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $mfaCode = $_POST['mfa_code'] ?? '';
    $rememberDevice = $_POST['remember_device'] ?? false;
    $deviceName = $_POST['device_name'] ?? '';
    
    // Get environment data for fingerprinting
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'unknown';
    
    // Initialize login tracker
    $loginTracker = new LoginTracker();
    
    // Generate device fingerprint
    $deviceFingerprint = $loginTracker->generateDeviceFingerprint($userAgent, $ipAddress, $_POST);
    
    // Get location data
    $locationData = $loginTracker->getLocationData($ipAddress);
    
    // Validate required fields
    if (empty($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email address is required'
        ]);
        exit;
    }
    
    if (empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Password is required'
        ]);
        exit;
    }
    
    // Check if account is locked
    $lockStatus = $loginTracker->isAccountLocked($email, $ipAddress, $deviceFingerprint);
    if ($lockStatus['locked']) {
        $remainingTime = strtotime($lockStatus['locked_until']) - time();
        $minutes = ceil($remainingTime / 60);
        
        echo json_encode([
            'success' => false,
            'message' => "Account is temporarily locked due to too many failed attempts. Please try again in {$minutes} minutes.",
            'locked' => true,
            'locked_until' => $lockStatus['locked_until']
        ]);
        exit;
    }
    
    // Record login attempt
    $attemptId = $loginTracker->recordLoginAttempt($email, $userAgent, $ipAddress, $deviceFingerprint, $locationData);
    
    // Initialize database connection
    $db = Database::getInstance();
    
    // Get user by email
    $user = $db->fetch("SELECT * FROM users WHERE email = ?", [$email]);
    
    if (!$user) {
        // Record failed attempt
        $loginTracker->recordFailedAttempt($email, $ipAddress, $deviceFingerprint);
        $loginTracker->updateLoginAttempt($attemptId, false, 'Invalid email or password');
        
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        // Record failed attempt
        $loginTracker->recordFailedAttempt($email, $ipAddress, $deviceFingerprint);
        $loginTracker->updateLoginAttempt($attemptId, false, 'Invalid email or password', false, null, $user['id']);
        
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
        exit;
    }
    
    // Check if email is verified
    if (!$user['email_verified_at']) {
        $loginTracker->updateLoginAttempt($attemptId, false, 'Email not verified', false, null, $user['id']);
        
        echo json_encode([
            'success' => false,
            'message' => 'Please verify your email address before logging in. Check your email for a verification link.',
            'email_verification_required' => true
        ]);
        exit;
    }
    
    // Get user login preferences
    $loginPreferences = $loginTracker->getUserLoginPreferences($user['id']);
    
    // Check if device is trusted for MFA suppression
    $isDeviceTrusted = $loginTracker->isDeviceTrusted($user['id'], $deviceFingerprint);
    
    // Determine if MFA is required
    $mfaRequired = false;
    if ($loginPreferences['mfa_enabled'] && !$isDeviceTrusted) {
        // Check if this is a new device
        $recentLogins = $loginTracker->getLoginHistory($user['id'], 1);
        $isNewDevice = empty($recentLogins) || $recentLogins[0]['device_fingerprint'] !== $deviceFingerprint;
        
        if ($loginPreferences['require_mfa_on_new_device'] && $isNewDevice) {
            $mfaRequired = true;
        }
    }
    
    // If MFA code is provided, verify it
    if ($mfaRequired && !empty($mfaCode)) {
        // Verify MFA code (implement your MFA verification logic here)
        $mfaValid = verifyMFACode($user['id'], $mfaCode);
        
        if (!$mfaValid) {
            $loginTracker->updateLoginAttempt($attemptId, false, 'Invalid MFA code', true, null, $user['id']);
            
            echo json_encode([
                'success' => false,
                'message' => 'Invalid verification code',
                'mfa_required' => true
            ]);
            exit;
        }
        
        $mfaRequired = false; // MFA completed successfully
    }
    
    // If MFA is required but no code provided, send MFA code
    if ($mfaRequired) {
        // Generate and send MFA code
        $mfaCode = generateMFACode($user['id'], $loginPreferences['mfa_method']);
        
        $loginTracker->updateLoginAttempt($attemptId, false, null, true, null, $user['id']);
        
        echo json_encode([
            'success' => false,
            'message' => 'Verification code sent to your email',
            'mfa_required' => true,
            'mfa_method' => $loginPreferences['mfa_method']
        ]);
        exit;
    }
    
    // Login successful - create session
    $sessionId = Session::start();
    Session::set('user_id', $user['id']);
    Session::set('email', $user['email']);
    Session::set('first_name', $user['first_name']);
    Session::set('last_name', $user['last_name']);
    Session::set('role', $user['role'] ?? 'customer');
    Session::set('device_fingerprint', $deviceFingerprint);
    
    // Clear failed attempts
    $loginTracker->clearFailedAttempts($email, $ipAddress, $deviceFingerprint);
    
    // Update login attempt as successful
    $loginTracker->updateLoginAttempt($attemptId, true, null, false, $sessionId, $user['id']);
    
    // Add device as trusted if requested
    if ($rememberDevice && $loginPreferences['allow_trusted_devices']) {
        $deviceName = $deviceName ?: 'Unknown Device';
        $loginTracker->addTrustedDevice(
            $user['id'], 
            $deviceFingerprint, 
            $deviceName, 
            $ipAddress, 
            $userAgent, 
            $locationData, 
            $loginPreferences['trusted_device_duration_days']
        );
    }
    
    // Detect suspicious activity
    $suspiciousActivity = $loginTracker->detectSuspiciousActivity($user['id'], $ipAddress, $locationData);
    
    // Send login notification if enabled
    if ($loginPreferences['notify_on_new_login']) {
        sendLoginNotification($user, $locationData, $suspiciousActivity);
    }
    
    // Log successful login
    Logger::getInstance()->info('User logged in successfully', [
        'user_id' => $user['id'],
        'email' => $user['email'],
        'ip_address' => $ipAddress,
        'device_fingerprint' => $deviceFingerprint,
        'suspicious_activity' => $suspiciousActivity['suspicious']
    ]);
    
    // Return success response
    $response = [
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role' => $user['role'] ?? 'customer'
        ],
        'session_id' => $sessionId,
        'suspicious_activity' => $suspiciousActivity
    ];
    
    // Add trusted device info if device was remembered
    if ($rememberDevice && $loginPreferences['allow_trusted_devices']) {
        $response['device_trusted'] = true;
        $response['trusted_until'] = date('Y-m-d H:i:s', strtotime("+{$loginPreferences['trusted_device_duration_days']} days"));
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    Logger::getInstance()->error('Login error', [
        'error' => $e->getMessage(),
        'email' => $email ?? 'unknown',
        'ip_address' => $ipAddress ?? 'unknown'
    ]);
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred during login. Please try again.'
    ]);
}

/**
 * Generate MFA code and send to user
 */
function generateMFACode($userId, $method = 'email') {
    try {
        $code = sprintf('%06d', mt_rand(0, 999999));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        $db = Database::getInstance();
        
        // Store MFA code in database
        $db->insert('verification_tokens', [
            'user_id' => $userId,
            'token' => $code,
            'type' => 'mfa_code',
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Send MFA code via email
        if ($method === 'email') {
            $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
            $email = new Email();
            
            $email->sendMFACode($user['email'], $user['first_name'], $code);
        }
        
        return $code;
    } catch (Exception $e) {
        Logger::getInstance()->error('Failed to generate MFA code', [
            'user_id' => $userId,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

/**
 * Verify MFA code
 */
function verifyMFACode($userId, $code) {
    try {
        $db = Database::getInstance();
        
        // Get valid MFA code
        $mfaToken = $db->fetch(
            "SELECT * FROM verification_tokens 
             WHERE user_id = ? AND token = ? AND type = 'mfa_code' 
             AND expires_at > NOW() AND used_at IS NULL",
            [$userId, $code]
        );
        
        if ($mfaToken) {
            // Mark code as used
            $db->update('verification_tokens', 
                ['used_at' => date('Y-m-d H:i:s')], 
                ['id' => $mfaToken['id']]
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

/**
 * Send login notification email
 */
function sendLoginNotification($user, $locationData, $suspiciousActivity) {
    try {
        $email = new Email();
        
        $locationInfo = '';
        if ($locationData) {
            $locationInfo = "Location: {$locationData['city']}, {$locationData['region']}, {$locationData['country']}";
        }
        
        $suspiciousWarning = '';
        if ($suspiciousActivity['suspicious']) {
            $suspiciousWarning = "\n\n⚠️ This login appears to be from an unusual location. If this wasn't you, please contact support immediately.";
        }
        
        $email->sendLoginNotification(
            $user['email'], 
            $user['first_name'], 
            $locationInfo,
            $suspiciousWarning
        );
    } catch (Exception $e) {
        Logger::getInstance()->error('Failed to send login notification', [
            'user_id' => $user['id'],
            'error' => $e->getMessage()
        ]);
    }
}
?>
