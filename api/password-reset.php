<?php
// Password Reset API Endpoint
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';
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
    $action = $_POST['action'] ?? '';
    $email = strtolower(trim($_POST['email'] ?? ''));
    $token = $_POST['token'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Get environment data for tracking
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'unknown';
    
    // Initialize login tracker
    $loginTracker = new LoginTracker();
    
    // Generate device fingerprint
    $deviceFingerprint = $loginTracker->generateDeviceFingerprint($userAgent, $ipAddress, $_POST);
    
    // Get location data
    $locationData = $loginTracker->getLocationData($ipAddress);
    
    // Initialize database connection
    $db = Database::getInstance();
    
    switch ($action) {
        case 'reset_password':
            handlePasswordResetRequest($email, $ipAddress, $deviceFingerprint, $locationData);
            break;
            
        case 'verify_token':
            handleTokenVerification($email, $token, $ipAddress, $deviceFingerprint);
            break;
            
        case 'update_password':
            handlePasswordUpdate($email, $token, $newPassword, $confirmPassword, $ipAddress, $deviceFingerprint);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action specified'
            ]);
            break;
    }
    
} catch (Exception $e) {
    Logger::getInstance()->error('Password reset error', [
        'error' => $e->getMessage(),
        'email' => $email ?? 'unknown',
        'ip_address' => $ipAddress ?? 'unknown'
    ]);
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}

/**
 * Handle password reset request
 */
function handlePasswordResetRequest($email, $ipAddress, $deviceFingerprint, $locationData) {
    global $db;
    
    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please enter a valid email address'
        ]);
        exit;
    }
    
    // Check if user exists
    $user = $db->fetch("SELECT * FROM users WHERE email = ?", [$email]);
    
    if (!$user) {
        // Don't reveal if email exists or not for security
        echo json_encode([
            'success' => true,
            'message' => 'If an account with this email exists, a password reset link has been sent.'
        ]);
        exit;
    }
    
    // Check if email is verified
    if (!$user['email_verified_at']) {
        echo json_encode([
            'success' => false,
            'message' => 'Please verify your email address before requesting a password reset.'
        ]);
        exit;
    }
    
    // Check for recent password reset attempts (rate limiting)
    $recentAttempts = $db->fetchAll(
        "SELECT * FROM verification_tokens 
         WHERE user_id = ? AND type = 'password_reset' 
         AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
        [$user['id']]
    );
    
    if (count($recentAttempts) >= 3) {
        echo json_encode([
            'success' => false,
            'message' => 'Too many password reset attempts. Please wait 1 hour before trying again.'
        ]);
        exit;
    }
    
    // Generate reset token
    $resetToken = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Store reset token
    $db->insert('verification_tokens', [
        'user_id' => $user['id'],
        'token' => $resetToken,
        'type' => 'password_reset',
        'expires_at' => $expiresAt,
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    // Send password reset email
    $email = new Email();
    $resetLink = "http://localhost:8080/reset-password.php?email=" . urlencode($email) . "&token=" . $resetToken;
    
    $email->sendPasswordReset(
        $user['email'],
        $user['first_name'],
        $resetLink,
        $locationData
    );
    
    // Log the password reset request
    Logger::getInstance()->info('Password reset requested', [
        'user_id' => $user['id'],
        'email' => $user['email'],
        'ip_address' => $ipAddress,
        'device_fingerprint' => $deviceFingerprint,
        'location_data' => $locationData
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Password reset link sent to your email address. Please check your inbox.'
    ]);
}

/**
 * Handle token verification
 */
function handleTokenVerification($email, $token, $ipAddress, $deviceFingerprint) {
    global $db;
    
    // Validate inputs
    if (empty($email) || empty($token)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request parameters'
        ]);
        exit;
    }
    
    // Get user
    $user = $db->fetch("SELECT * FROM users WHERE email = ?", [$email]);
    
    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid reset link'
        ]);
        exit;
    }
    
    // Verify token
    $resetToken = $db->fetch(
        "SELECT * FROM verification_tokens 
         WHERE user_id = ? AND token = ? AND type = 'password_reset' 
         AND expires_at > NOW() AND used_at IS NULL",
        [$user['id'], $token]
    );
    
    if (!$resetToken) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid or expired reset link. Please request a new password reset.'
        ]);
        exit;
    }
    
    // Log token verification
    Logger::getInstance()->info('Password reset token verified', [
        'user_id' => $user['id'],
        'email' => $user['email'],
        'ip_address' => $ipAddress,
        'device_fingerprint' => $deviceFingerprint
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Token verified successfully',
        'user' => [
            'email' => $user['email'],
            'first_name' => $user['first_name']
        ]
    ]);
}

/**
 * Handle password update
 */
function handlePasswordUpdate($email, $token, $newPassword, $confirmPassword, $ipAddress, $deviceFingerprint) {
    global $db;
    
    // Validate inputs
    if (empty($email) || empty($token) || empty($newPassword) || empty($confirmPassword)) {
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required'
        ]);
        exit;
    }
    
    if ($newPassword !== $confirmPassword) {
        echo json_encode([
            'success' => false,
            'message' => 'Passwords do not match'
        ]);
        exit;
    }
    
    // Validate password strength
    $passwordValidation = validatePasswordStrength($newPassword);
    if (!$passwordValidation['valid']) {
        echo json_encode([
            'success' => false,
            'message' => $passwordValidation['message']
        ]);
        exit;
    }
    
    // Get user
    $user = $db->fetch("SELECT * FROM users WHERE email = ?", [$email]);
    
    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid reset link'
        ]);
        exit;
    }
    
    // Verify token
    $resetToken = $db->fetch(
        "SELECT * FROM verification_tokens 
         WHERE user_id = ? AND token = ? AND type = 'password_reset' 
         AND expires_at > NOW() AND used_at IS NULL",
        [$user['id'], $token]
    );
    
    if (!$resetToken) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid or expired reset link'
        ]);
        exit;
    }
    
    // Check if new password is different from current password
    if (password_verify($newPassword, $user['password_hash'])) {
        echo json_encode([
            'success' => false,
            'message' => 'New password must be different from your current password'
        ]);
        exit;
    }
    
    // Check password history (last 5 passwords)
    $passwordHistory = $db->fetchAll(
        "SELECT password_hash FROM password_history 
         WHERE user_id = ? 
         ORDER BY created_at DESC 
         LIMIT 5",
        [$user['id']]
    );
    
    foreach ($passwordHistory as $history) {
        if (password_verify($newPassword, $history['password_hash'])) {
            echo json_encode([
                'success' => false,
                'message' => 'New password cannot be the same as any of your last 5 passwords'
            ]);
            exit;
        }
    }
    
    // Begin transaction
    $db->beginTransaction();
    
    try {
        // Hash new password
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update user password
        $db->update('users', [
            'password_hash' => $newPasswordHash,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $user['id']]);
        
        // Add to password history
        $db->insert('password_history', [
            'user_id' => $user['id'],
            'password_hash' => $newPasswordHash,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Mark token as used
        $db->update('verification_tokens', [
            'used_at' => date('Y-m-d H:i:s')
        ], ['id' => $resetToken['id']]);
        
        // Clear all other password reset tokens for this user
        $db->update('verification_tokens', [
            'used_at' => date('Y-m-d H:i:s')
        ], [
            'user_id' => $user['id'],
            'type' => 'password_reset',
            'used_at' => null
        ]);
        
        // Clear failed login attempts
        $loginTracker = new LoginTracker();
        $loginTracker->clearFailedAttempts($email, $ipAddress, $deviceFingerprint);
        
        // Send password change notification
        $email = new Email();
        $email->sendPasswordChangeNotification(
            $user['email'],
            $user['first_name'],
            $ipAddress
        );
        
        $db->commit();
        
        // Log password change
        Logger::getInstance()->info('Password changed successfully', [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'ip_address' => $ipAddress,
            'device_fingerprint' => $deviceFingerprint
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Password updated successfully. You can now log in with your new password.'
        ]);
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
}

/**
 * Validate password strength
 */
function validatePasswordStrength($password) {
    $errors = [];
    
    if (strlen($password) < 12) {
        $errors[] = 'Password must be at least 12 characters long';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }
    
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Password must contain at least one special character';
    }
    
    if (empty($errors)) {
        return [
            'valid' => true,
            'message' => 'Password meets strength requirements'
        ];
    } else {
        return [
            'valid' => false,
            'message' => implode('. ', $errors)
        ];
    }
}
?>

