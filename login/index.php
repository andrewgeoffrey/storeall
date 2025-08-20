<?php
// Login Page - Main Entry Point
// This file handles the login functionality with environment tracking and MFA support

require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Auth.php';
require_once '../includes/Session.php';
require_once '../includes/Logger.php';
require_once '../includes/Email.php';
require_once '../includes/LoginTracker.php';

// Check if user is already logged in
$auth = Auth::getInstance();
if ($auth->isLoggedIn()) {
    $user = $auth->getCurrentUser();
    
    // Redirect based on user role
    switch ($user['role']) {
        case 'admin':
            header('Location: /admin/dashboard/');
            exit;
        case 'super_user':
            header('Location: /admin/dashboard/');
            exit;
        case 'owner':
            if (!empty($user['organization_slug'])) {
                header('Location: /' . $user['organization_slug'] . '/manage/');
            } else {
                header('Location: /admin/dashboard/');
            }
            exit;
        case 'customer':
            if (!empty($user['organization_slug'])) {
                header('Location: /' . $user['organization_slug'] . '/cust/');
            } else {
                header('Location: /');
            }
            exit;
        default:
            header('Location: /');
            exit;
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    $auth->logout();
    header('Location: /login/');
    exit;
}

// Get any error messages from session
$session = Session::getInstance();
$errorMessage = $session->get('login_error');
$successMessage = $session->get('login_success');
$session->delete('login_error');
$session->delete('login_success');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - StoreAll.io</title>
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
                    <i class="fas fa-shield-alt"></i>
                    <h1>StoreAll.io</h1>
                </div>
                <p class="subtitle">Secure Login Portal</p>
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
            
            <!-- Login Form -->
            <form id="loginForm" class="login-form">
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                    <label for="email">Email Address</label>
                </div>
                
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">Password</label>
                </div>
                
                <!-- Device Trust Option -->
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="rememberDevice" name="remember_device">
                    <label class="form-check-label" for="rememberDevice">
                        <i class="fas fa-lock"></i> Trust this device for 30 days (skip MFA)
                    </label>
                </div>
                
                <!-- Device Name Input -->
                <div class="form-floating mb-3" id="deviceNameSection" style="display: none;">
                    <input type="text" class="form-control" id="deviceName" name="device_name" placeholder="Device Name">
                    <label for="deviceName">Device Name (e.g., Home Computer, Work Laptop)</label>
                </div>
                
                <!-- Device Information -->
                <div class="device-info mb-3">
                    <h6><i class="fas fa-info-circle"></i> Device Information</h6>
                    <ul id="deviceInfoList">
                        <li><i class="fas fa-desktop"></i> <span id="devicePlatform">Loading...</span></li>
                        <li><i class="fas fa-globe"></i> <span id="deviceLocation">Loading...</span></li>
                        <li><i class="fas fa-clock"></i> <span id="deviceTimezone">Loading...</span></li>
                    </ul>
                </div>
                
                <!-- Loading Spinner -->
                <div class="loading-spinner text-center mb-3" id="loadingSpinner" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Authenticating...</p>
                </div>
                
                <!-- Login Button -->
                <button type="submit" class="btn btn-primary w-100 mb-3" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>
            
            <!-- MFA Section -->
            <div class="mfa-section" id="mfaSection" style="display: none;">
                <h5><i class="fas fa-key"></i> Two-Factor Authentication</h5>
                <p class="text-muted">We've sent a verification code to your email address.</p>
                
                <div class="form-floating mb-3">
                    <input type="text" class="form-control mfa-input" id="mfaCode" name="mfa_code" placeholder="000000" maxlength="6">
                    <label for="mfaCode">Enter 6-digit code</label>
                </div>
                
                <button type="button" class="btn btn-primary w-100 mb-3" id="verifyMfaBtn">
                    <i class="fas fa-check"></i> Verify Code
                </button>
                
                <div class="text-center">
                    <button type="button" class="btn btn-link" id="resendMfaBtn">
                        <i class="fas fa-redo"></i> Resend Code
                    </button>
                </div>
            </div>
            
            <!-- Password Reset Section -->
            <div class="password-reset-section" id="passwordResetSection" style="display: none;">
                <h5><i class="fas fa-key"></i> Reset Password</h5>
                <p class="text-muted">Enter your email address to receive a password reset link.</p>
                
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="resetEmail" name="reset_email" placeholder="Email">
                    <label for="resetEmail">Email Address</label>
                </div>
                
                <button type="button" class="btn btn-primary w-100 mb-3" id="sendResetBtn">
                    <i class="fas fa-paper-plane"></i> Send Reset Link
                </button>
                
                <div class="text-center">
                    <button type="button" class="btn btn-link" id="backToLoginBtn">
                        <i class="fas fa-arrow-left"></i> Back to Login
                    </button>
                </div>
            </div>
            
            <!-- Links -->
            <div class="login-links">
                <div class="row">
                    <div class="col-6 text-center">
                        <a href="#" onclick="showPasswordReset()" class="link-primary">
                            <i class="fas fa-unlock"></i> Forgot Password?
                        </a>
                    </div>
                    <div class="col-6 text-center">
                        <a href="/register/" class="link-primary">
                            <i class="fas fa-user-plus"></i> Create Account
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
    <script src="/js/login.js"></script>
</body>
</html>
