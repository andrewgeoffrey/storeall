<?php
// Password Reset Page - Main Entry Point
// This file handles password reset functionality with token verification

require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Auth.php';
require_once '../includes/Session.php';
require_once '../includes/Logger.php';
require_once '../includes/Email.php';
require_once '../includes/LoginTracker.php';

// Get URL parameters
$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

// Validate parameters
$isValidToken = false;
$user = null;
$errorMessage = '';
$successMessage = '';

if (!empty($email) && !empty($token)) {
    try {
        $db = Database::getInstance();
        
        // Get user
        $user = $db->fetch("SELECT * FROM users WHERE email = ?", [$email]);
        
        if ($user) {
            // Verify token
            $resetToken = $db->fetch(
                "SELECT * FROM verification_tokens 
                 WHERE user_id = ? AND token = ? AND type = 'password_reset' 
                 AND expires_at > NOW() AND used_at IS NULL",
                [$user['id'], $token]
            );
            
            if ($resetToken) {
                $isValidToken = true;
            } else {
                $errorMessage = 'Invalid or expired reset link. Please request a new password reset.';
            }
        } else {
            $errorMessage = 'Invalid reset link.';
        }
        
    } catch (Exception $e) {
        Logger::getInstance()->error('Password reset page error', [
            'error' => $e->getMessage(),
            'email' => $email,
            'token' => $token
        ]);
        $errorMessage = 'An error occurred while processing your request.';
    }
} else {
    $errorMessage = 'Invalid reset link. Missing required parameters.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isValidToken) {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($newPassword) || empty($confirmPassword)) {
        $errorMessage = 'All fields are required.';
    } elseif ($newPassword !== $confirmPassword) {
        $errorMessage = 'Passwords do not match.';
    } else {
        // Validate password strength
        $passwordValidation = validatePasswordStrength($newPassword);
        if (!$passwordValidation['valid']) {
            $errorMessage = $passwordValidation['message'];
        } else {
            // Process password update
            try {
                $result = processPasswordUpdate($email, $token, $newPassword);
                if ($result['success']) {
                    $successMessage = $result['message'];
                } else {
                    $errorMessage = $result['message'];
                }
            } catch (Exception $e) {
                Logger::getInstance()->error('Password update error', [
                    'error' => $e->getMessage(),
                    'email' => $email
                ]);
                $errorMessage = 'An error occurred while updating your password.';
            }
        }
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

/**
 * Process password update
 */
function processPasswordUpdate($email, $token, $newPassword) {
    $db = Database::getInstance();
    
    // Get user
    $user = $db->fetch("SELECT * FROM users WHERE email = ?", [$email]);
    
    if (!$user) {
        return ['success' => false, 'message' => 'Invalid reset link'];
    }
    
    // Verify token
    $resetToken = $db->fetch(
        "SELECT * FROM verification_tokens 
         WHERE user_id = ? AND token = ? AND type = 'password_reset' 
         AND expires_at > NOW() AND used_at IS NULL",
        [$user['id'], $token]
    );
    
    if (!$resetToken) {
        return ['success' => false, 'message' => 'Invalid or expired reset link'];
    }
    
    // Check if new password is different from current password
    if (password_verify($newPassword, $user['password_hash'])) {
        return ['success' => false, 'message' => 'New password must be different from your current password'];
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
            return ['success' => false, 'message' => 'New password cannot be the same as any of your last 5 passwords'];
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
        $loginTracker->clearFailedAttempts($email, $_SERVER['REMOTE_ADDR'] ?? 'unknown', '');
        
        // Send password change notification
        $emailService = new Email();
        $emailService->sendPasswordChangeNotification(
            $user['email'],
            $user['first_name'],
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        );
        
        $db->commit();
        
        // Log password change
        Logger::getInstance()->info('Password changed successfully', [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        return [
            'success' => true,
            'message' => 'Password updated successfully! You can now log in with your new password.'
        ];
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - StoreAll.io</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="/css/login.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-key"></i>
                    <h1>Reset Password</h1>
                </div>
                <p class="subtitle">Create a new secure password</p>
            </div>
            
            <!-- Alert Messages -->
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($errorMessage); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($successMessage): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($successMessage); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($isValidToken && !$successMessage): ?>
                <!-- Password Reset Form -->
                <form method="POST" class="login-form" id="resetPasswordForm">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="newPassword" name="new_password" placeholder="New Password" required>
                        <label for="newPassword">New Password</label>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="Confirm Password" required>
                        <label for="confirmPassword">Confirm Password</label>
                    </div>
                    
                    <!-- Password Strength Indicator -->
                    <div class="password-strength mb-3">
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar" id="strengthBar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small class="text-muted" id="strengthText">Password strength will appear here</small>
                    </div>
                    
                    <!-- Password Requirements -->
                    <div class="password-requirements mb-3">
                        <h6><i class="fas fa-list-check"></i> Password Requirements</h6>
                        <ul class="list-unstyled small">
                            <li id="req-length"><i class="fas fa-circle text-muted"></i> At least 12 characters</li>
                            <li id="req-uppercase"><i class="fas fa-circle text-muted"></i> One uppercase letter</li>
                            <li id="req-lowercase"><i class="fas fa-circle text-muted"></i> One lowercase letter</li>
                            <li id="req-number"><i class="fas fa-circle text-muted"></i> One number</li>
                            <li id="req-special"><i class="fas fa-circle text-muted"></i> One special character</li>
                        </ul>
                    </div>
                    
                    <!-- Loading Spinner -->
                    <div class="loading-spinner text-center mb-3" id="loadingSpinner" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Updating password...</p>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary w-100 mb-3" id="submitBtn">
                        <i class="fas fa-save"></i> Update Password
                    </button>
                </form>
                
            <?php elseif (!$isValidToken && !$successMessage): ?>
                <!-- Invalid Token Message -->
                <div class="login-form text-center">
                    <div class="mb-4">
                        <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                    </div>
                    <h5>Invalid Reset Link</h5>
                    <p class="text-muted">The password reset link is invalid or has expired.</p>
                    <a href="/login/" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Login
                    </a>
                </div>
                
            <?php elseif ($successMessage): ?>
                <!-- Success Message -->
                <div class="login-form text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                    </div>
                    <h5>Password Updated Successfully!</h5>
                    <p class="text-muted">Your password has been updated. You can now log in with your new password.</p>
                    <a href="/login/" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Go to Login
                    </a>
                </div>
            <?php endif; ?>
            
            <!-- Links -->
            <div class="login-links">
                <div class="row">
                    <div class="col-12 text-center">
                        <a href="/login/" class="link-primary">
                            <i class="fas fa-arrow-left"></i> Back to Login
                        </a>
                    </div>
                </div>
                
                <div class="row mt-2">
                    <div class="col-12 text-center">
                        <a href="/" class="link-secondary">
                            <i class="fas fa-home"></i> Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/password-reset.js"></script>
</body>
</html>

