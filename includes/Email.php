<?php
/**
 * Email Class for StoreAll.io
 * Handles email sending functionality
 */
class Email {
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Send an email
     */
    public function send($to, $subject, $message, $headers = []) {
        // For development with MailHog, we'll use a simple approach
        // In production, you'd use a proper SMTP library like PHPMailer
        
        // Set default headers
        $defaultHeaders = [
            'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . '>',
            'Reply-To: ' . MAIL_FROM_ADDRESS,
            'Content-Type: text/html; charset=UTF-8',
            'X-Mailer: StoreAll.io'
        ];
        
        // Merge custom headers with defaults
        $allHeaders = array_merge($defaultHeaders, $headers);
        $headerString = implode("\r\n", $allHeaders);
        
        // Send email using PHP mail() function (works with MailHog)
        $result = mail($to, $subject, $message, $headerString);
        
        // Log the email attempt
        if (class_exists('Logger')) {
            Logger::getInstance()->info('Email sent via mail()', [
                'to' => $to,
                'subject' => $subject,
                'success' => $result,
                'message' => $result ? 'Email sent successfully' : 'Email sending failed'
            ]);
        }
        
        return $result;
    }
    
    /**
     * Send welcome email with verification link
     */
    public function sendWelcomeEmail($email, $firstName, $verificationToken) {
        $subject = 'Welcome to StoreAll.io - Confirm Your Account';
        $verificationUrl = APP_URL . '/verify-email/' . $verificationToken;
        
        $message = $this->getWelcomeEmailTemplate($firstName, $verificationUrl);
        
        return $this->send($email, $subject, $message);
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($email, $firstName, $resetToken) {
        $subject = 'Reset Your StoreAll.io Password';
        $resetUrl = APP_URL . '/reset-password/?email=' . urlencode($email) . '&token=' . $resetToken;
        
        $message = $this->getPasswordResetTemplate($firstName, $resetUrl);
        
        return $this->send($email, $subject, $message);
    }
    
    /**
     * Send MFA verification code
     */
    public function sendMFACode($email, $firstName, $mfaCode, $locationData = null) {
        $subject = 'Your StoreAll.io Login Verification Code';
        
        $message = $this->getMFATemplate($firstName, $mfaCode, $locationData);
        
        return $this->send($email, $subject, $message);
    }
    
    /**
     * Send login notification
     */
    public function sendLoginNotification($email, $firstName, $locationData, $deviceInfo) {
        $subject = 'New Login to Your StoreAll.io Account';
        
        $message = $this->getLoginNotificationTemplate($firstName, $locationData, $deviceInfo);
        
        return $this->send($email, $subject, $message);
    }
    
    /**
     * Send password change notification
     */
    public function sendPasswordChangeNotification($email, $firstName, $ipAddress) {
        $subject = 'Your StoreAll.io Password Has Been Changed';
        
        $message = $this->getPasswordChangeTemplate($firstName, $ipAddress);
        
        return $this->send($email, $subject, $message);
    }
    
    /**
     * Get welcome email HTML template
     */
    private function getWelcomeEmailTemplate($firstName, $verificationUrl) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Welcome to StoreAll.io</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #2563eb; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Welcome to StoreAll.io!</h1>
                </div>
                <div class='content'>
                    <h2>Hi {$firstName},</h2>
                    <p>Thank you for registering with StoreAll.io! We're excited to have you on board.</p>
                    <p>To complete your registration and start managing your storage units, please click the button below to verify your email address:</p>
                    
                    <div style='text-align: center;'>
                        <a href='{$verificationUrl}' class='button'>Verify Email Address</a>
                    </div>
                    
                    <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
                    <p style='word-break: break-all; color: #2563eb;'>{$verificationUrl}</p>
                    
                    <p><strong>Important:</strong> This verification link will expire in 24 hours.</p>
                    
                    <p>If you didn't create this account, you can safely ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>Best regards,<br>The StoreAll.io Team</p>
                    <p>This email was sent to you because you registered for a StoreAll.io account.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Get password reset email HTML template
     */
    private function getPasswordResetTemplate($firstName, $resetUrl) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Reset Your Password - StoreAll.io</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #dc2626; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Reset Your Password</h1>
                </div>
                <div class='content'>
                    <h2>Hi {$firstName},</h2>
                    <p>We received a request to reset your StoreAll.io password.</p>
                    <p>Click the button below to create a new password:</p>
                    
                    <div style='text-align: center;'>
                        <a href='{$resetUrl}' class='button'>Reset Password</a>
                    </div>
                    
                    <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
                    <p style='word-break: break-all; color: #dc2626;'>{$resetUrl}</p>
                    
                    <p><strong>Important:</strong> This reset link will expire in 1 hour.</p>
                    
                    <p>If you didn't request a password reset, you can safely ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>Best regards,<br>The StoreAll.io Team</p>
                    <p>This email was sent to you because you requested a password reset.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Get MFA verification email HTML template
     */
    private function getMFATemplate($firstName, $mfaCode, $locationData = null) {
        $locationInfo = '';
        if ($locationData) {
            $locationInfo = "
                <p><strong>Login Location:</strong> " . ($locationData['city'] ?? 'Unknown') . ", " . ($locationData['country'] ?? 'Unknown') . "</p>
                <p><strong>IP Address:</strong> " . ($locationData['query'] ?? 'Unknown') . "</p>
            ";
        }
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Login Verification - StoreAll.io</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .code { background: #059669; color: white; padding: 20px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 5px; border-radius: 10px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Login Verification</h1>
                </div>
                <div class='content'>
                    <h2>Hi {$firstName},</h2>
                    <p>We received a login request for your StoreAll.io account.</p>
                    {$locationInfo}
                    <p>Please enter this verification code to complete your login:</p>
                    
                    <div class='code'>{$mfaCode}</div>
                    
                    <p><strong>Important:</strong> This code will expire in 10 minutes.</p>
                    <p>If you didn't attempt to log in, please contact support immediately.</p>
                </div>
                <div class='footer'>
                    <p>Best regards,<br>The StoreAll.io Team</p>
                    <p>This email was sent to you because someone attempted to log into your account.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Get login notification email HTML template
     */
    private function getLoginNotificationTemplate($firstName, $locationData, $deviceInfo) {
        $locationInfo = '';
        if ($locationData) {
            $locationInfo = "
                <p><strong>Location:</strong> " . ($locationData['city'] ?? 'Unknown') . ", " . ($locationData['country'] ?? 'Unknown') . "</p>
                <p><strong>IP Address:</strong> " . ($locationData['query'] ?? 'Unknown') . "</p>
            ";
        }
        
        $deviceInfoText = '';
        if ($deviceInfo) {
            $deviceInfoText = "
                <p><strong>Device:</strong> " . ($deviceInfo['platform'] ?? 'Unknown') . "</p>
                <p><strong>Browser:</strong> " . ($deviceInfo['user_agent'] ?? 'Unknown') . "</p>
            ";
        }
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>New Login - StoreAll.io</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .info-box { background: #e5e7eb; padding: 15px; border-radius: 5px; margin: 15px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>New Login Detected</h1>
                </div>
                <div class='content'>
                    <h2>Hi {$firstName},</h2>
                    <p>We detected a new login to your StoreAll.io account.</p>
                    
                    <div class='info-box'>
                        <h3>Login Details:</h3>
                        {$locationInfo}
                        {$deviceInfoText}
                        <p><strong>Time:</strong> " . date('Y-m-d H:i:s T') . "</p>
                    </div>
                    
                    <p>If this was you, you can safely ignore this email.</p>
                    <p>If you don't recognize this login, please contact support immediately.</p>
                </div>
                <div class='footer'>
                    <p>Best regards,<br>The StoreAll.io Team</p>
                    <p>This email was sent to you because a new login was detected on your account.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Get password change notification email HTML template
     */
    private function getPasswordChangeTemplate($firstName, $ipAddress) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Password Changed - StoreAll.io</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .info-box { background: #e5e7eb; padding: 15px; border-radius: 5px; margin: 15px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Password Changed</h1>
                </div>
                <div class='content'>
                    <h2>Hi {$firstName},</h2>
                    <p>Your StoreAll.io password has been successfully changed.</p>
                    
                    <div class='info-box'>
                        <h3>Change Details:</h3>
                        <p><strong>IP Address:</strong> {$ipAddress}</p>
                        <p><strong>Time:</strong> " . date('Y-m-d H:i:s T') . "</p>
                    </div>
                    
                    <p>If you made this change, you can safely ignore this email.</p>
                    <p>If you didn't change your password, please contact support immediately.</p>
                </div>
                <div class='footer'>
                    <p>Best regards,<br>The StoreAll.io Team</p>
                    <p>This email was sent to you because your password was changed.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}