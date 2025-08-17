<?php
// Email verification handler
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Logger.php';

// Get token from URL (either from .htaccess rewrite or query parameter)
$token = $_GET['token'] ?? '';

// If no token provided, show error
if (empty($token)) {
    $error = 'No verification token provided.';
    $success = false;
} else {
    try {
        $db = Database::getInstance();
        
        // Get token data
        $tokenData = $db->fetch("
            SELECT vt.*, u.email, u.first_name, u.last_name 
            FROM verification_tokens vt 
            JOIN users u ON vt.user_id = u.id 
            WHERE vt.token = ? AND vt.type = 'email_verification'
        ", [$token]);
        
        if ($tokenData && !$tokenData['used_at']) {
            // Check if token is expired
            if (strtotime($tokenData['expires_at']) < time()) {
                $error = 'Verification link has expired. Please request a new one.';
                
                // Log expired token
                if (class_exists('Logger')) {
                    Logger::getInstance()->logVerificationEvent('email_verification', $tokenData['user_id'], 'token_expired', 'failed', [
                        'token' => $token,
                        'expires_at' => $tokenData['expires_at']
                    ]);
                }
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
                    Logger::getInstance()->logVerificationEvent('email_verification', $userId, 'token_verified', 'success', [
                        'token' => $token,
                        'email' => $tokenData['email']
                    ]);
                }
                
                $success = true;
                $message = 'Email verified successfully! You can now log in to your StoreAll.io account.';
                $userEmail = $tokenData['email'];
                $userName = $tokenData['first_name'];
            }
        } else {
            $error = 'Invalid or already used verification link.';
            
            // Log invalid token
            if (class_exists('Logger')) {
                Logger::getInstance()->logVerificationEvent('email_verification', 0, 'token_invalid', 'failed', [
                    'token' => $token,
                    'reason' => $tokenData ? 'already_used' : 'not_found'
                ]);
            }
        }
        
    } catch (Exception $e) {
        $error = 'An error occurred while verifying your email. Please try again.';
        
        // Log the error
        if (class_exists('Logger')) {
            Logger::getInstance()->error('Email verification failed', [
                'error' => $e->getMessage(),
                'token' => $token,
                'stack_trace' => $e->getTraceAsString()
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .verification-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 3rem;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        .success-icon {
            color: #28a745;
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .error-icon {
            color: #dc3545;
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="verification-card">
        <?php if (isset($success) && $success): ?>
            <i class="fas fa-check-circle success-icon"></i>
            <h2 class="mb-3">Email Verified Successfully!</h2>
            <p class="text-muted mb-4">
                Hello <?php echo htmlspecialchars($userName); ?>,<br>
                Your email address <strong><?php echo htmlspecialchars($userEmail); ?></strong> has been verified.
            </p>
            <p class="mb-4">You can now log in to your StoreAll.io account and start managing your storage business.</p>
            <a href="/" class="btn btn-primary">
                <i class="fas fa-home me-2"></i>Go to StoreAll.io
            </a>
        <?php else: ?>
            <i class="fas fa-exclamation-triangle error-icon"></i>
            <h2 class="mb-3">Verification Failed</h2>
            <p class="text-muted mb-4"><?php echo htmlspecialchars($error ?? 'An unknown error occurred.'); ?></p>
            <p class="mb-4">If you're having trouble, please contact our support team or try registering again.</p>
            <a href="/" class="btn btn-primary">
                <i class="fas fa-home me-2"></i>Return to StoreAll.io
            </a>
        <?php endif; ?>
    </div>
</body>
</html>
