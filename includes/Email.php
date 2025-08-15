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
        
        // For development, we'll simulate email sending
        // In a real environment, you'd use mail() or SMTP
        $result = true; // Simulate success for development
        
        // Log the email attempt
        if (class_exists('Logger')) {
            Logger::getInstance()->info('Email sent (simulated)', [
                'to' => $to,
                'subject' => $subject,
                'success' => $result,
                'message' => 'Email simulation for development'
            ]);
        }
        
        return $result;
    }
    
    /**
     * Send welcome email with verification link
     */
    public function sendWelcomeEmail($email, $firstName, $verificationToken) {
        $subject = 'Welcome to StoreAll.io - Confirm Your Account';
        $verificationUrl = APP_URL . '/pages/verify-email.php?token=' . $verificationToken;
        
        $message = $this->getWelcomeEmailTemplate($firstName, $verificationUrl);
        
        return $this->send($email, $subject, $message);
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($email, $firstName, $resetToken) {
        $subject = 'Reset Your StoreAll.io Password';
        $resetUrl = APP_URL . '/pages/reset-password.php?token=' . $resetToken;
        
        $message = $this->getPasswordResetTemplate($firstName, $resetUrl);
        
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
}
?>
