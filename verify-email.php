<?php
// Email Verification Handler for StoreAll.io
// This file processes email verification tokens

// Load configuration and core classes
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/Logger.php';

// Get verification token from URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = 'Invalid verification link.';
} else {
    try {
        $db = Database::getInstance();
        
        // Find user with this verification token
        $tokenData = $db->fetch(
            "SELECT vt.user_id, vt.expires_at, vt.used_at, u.email 
             FROM verification_tokens vt 
             JOIN users u ON vt.user_id = u.id 
             WHERE vt.token = ? AND vt.type = 'email_verification'",
            [$token]
        );
        
        if ($tokenData && !$tokenData['used_at']) {
            // Check if token is expired
            if (strtotime($tokenData['expires_at']) < time()) {
                $error = 'Verification link has expired. Please request a new one.';
            } else {
                $userId = $tokenData['user_id'];
                
                // Update user's email verification status
                $db->update('users', 
                    ['email_verified_at' => date('Y-m-d H:i:s')], 
                    'id = ?',
                    [$userId]
                );
                
                // Mark token as used
                $db->update('verification_tokens',
                    ['used_at' => date('Y-m-d H:i:s')],
                    'token = ?',
                    [$token]
                );
                
                // Log the verification
                if (class_exists('Logger')) {
                    Logger::getInstance()->info('Email verified', [
                        'user_id' => $userId,
                        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                    ]);
                }
                
                $success = true;
                $message = 'Email verified successfully! You can now log in to your StoreAll.io account.';
            }
        } else {
            $error = 'Invalid or expired verification link.';
        }
        
    } catch (Exception $e) {
        $error = 'An error occurred while verifying your email. Please try again.';
        
        if (class_exists('Logger')) {
            Logger::getInstance()->error('Email verification failed', [
                'error' => $e->getMessage(),
                'token' => $token,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - StoreAll.io</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .verification-card {
            background: white;
            border-radius: 1rem;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        .verification-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            color: white;
            font-size: 2rem;
        }
        .error-icon {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
    </style>
</head>
<body>
    <div class="verification-card">
        <?php if (isset($success) && $success): ?>
            <div class="verification-icon">
                <i class="fas fa-check"></i>
            </div>
            <h2 class="mb-3">Email Verified!</h2>
            <p class="text-muted mb-4"><?= htmlspecialchars($message) ?></p>
            <a href="/" class="btn btn-primary">
                <i class="fas fa-home me-2"></i>Go to StoreAll.io
            </a>
        <?php else: ?>
            <div class="verification-icon error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2 class="mb-3">Verification Failed</h2>
            <p class="text-muted mb-4"><?= htmlspecialchars($error ?? 'An error occurred.') ?></p>
            <a href="/" class="btn btn-primary">
                <i class="fas fa-home me-2"></i>Go to StoreAll.io
            </a>
        <?php endif; ?>
    </div>
</body>
</html>


