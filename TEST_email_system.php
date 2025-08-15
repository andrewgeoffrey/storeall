<?php
// TEST FILE: Email System Test
// This file tests the email functionality with MailHog
// DELETE THIS FILE AFTER TESTING - NOT FOR PRODUCTION

// Load config first to avoid session warnings
require_once 'config/config.php';

echo "<h1>StoreAll.io - Email System Test</h1>";
echo "<p><strong>Test Date:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Test 1: Check if Email class exists
echo "<h2>Test 1: Email Class Check</h2>";
if (file_exists('includes/Email.php')) {
    echo "✅ Email class file exists: includes/Email.php<br>";
    require_once 'includes/Email.php';
    if (class_exists('Email')) {
        echo "✅ Email class loaded successfully<br>";
    } else {
        echo "❌ Email class not found<br>";
    }
} else {
    echo "❌ Email class file missing: includes/Email.php<br>";
}

// Test 2: Check MailHog configuration
echo "<h2>Test 2: MailHog Configuration</h2>";
echo "✅ Mail Host: " . MAIL_HOST . "<br>";
echo "✅ Mail Port: " . MAIL_PORT . "<br>";
echo "✅ From Address: " . MAIL_FROM_ADDRESS . "<br>";
echo "✅ From Name: " . MAIL_FROM_NAME . "<br>";

// Test 3: Test email sending
echo "<h2>Test 3: Email Sending Test</h2>";
if (class_exists('Email')) {
    try {
        $email = Email::getInstance();
        $testEmail = 'test@example.com';
        $testSubject = 'StoreAll.io - Email Test';
        $testMessage = "
        <html>
        <body>
            <h2>Email Test Successful!</h2>
            <p>This is a test email from StoreAll.io to verify that MailHog is working correctly.</p>
            <p><strong>Test Details:</strong></p>
            <ul>
                <li>Sent at: " . date('Y-m-d H:i:s') . "</li>
                <li>From: " . MAIL_FROM_NAME . " &lt;" . MAIL_FROM_ADDRESS . "&gt;</li>
                <li>To: " . $testEmail . "</li>
            </ul>
            <p>If you can see this email in MailHog, the email system is working!</p>
        </body>
        </html>
        ";
        
        $result = $email->send($testEmail, $testSubject, $testMessage);
        
        if ($result) {
            echo "✅ Test email sent successfully!<br>";
            echo "📧 Check MailHog at: <a href='http://localhost:8025' target='_blank'>http://localhost:8025</a><br>";
        } else {
            echo "❌ Test email failed to send<br>";
        }
    } catch (Exception $e) {
        echo "❌ Email test error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Email class not available for testing<br>";
}

// Test 4: Test welcome email template
echo "<h2>Test 4: Welcome Email Template Test</h2>";
if (class_exists('Email')) {
    try {
        $email = Email::getInstance();
        $testToken = 'test_verification_token_' . time();
        $result = $email->sendWelcomeEmail('welcome@example.com', 'Test User', $testToken);
        
        if ($result) {
            echo "✅ Welcome email template sent successfully!<br>";
            echo "📧 Check MailHog for welcome email template<br>";
        } else {
            echo "❌ Welcome email template failed to send<br>";
        }
    } catch (Exception $e) {
        echo "❌ Welcome email test error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Email class not available for template testing<br>";
}

// Test 5: MailHog Access Instructions
echo "<h2>Test 5: MailHog Access</h2>";
echo "<p><strong>To view emails in MailHog:</strong></p>";
echo "<ol>";
echo "<li>Make sure Docker containers are running: <code>docker-compose up -d</code></li>";
echo "<li>Open MailHog web interface: <a href='http://localhost:8025' target='_blank'>http://localhost:8025</a></li>";
echo "<li>You should see any emails sent by the application</li>";
echo "<li>Click on an email to view its contents</li>";
echo "</ol>";

// Test 6: Registration Email Flow
echo "<h2>Test 6: Registration Email Flow</h2>";
echo "<p><strong>Complete Email Testing Process:</strong></p>";
echo "<ol>";
echo "<li>1. Open <a href='index.php'>StoreAll.io</a></li>";
echo "<li>2. Click 'Get Started' or 'Start Free Trial'</li>";
echo "<li>3. Fill out the registration form with a real email address</li>";
echo "<li>4. Submit the form</li>";
echo "<li>5. Check MailHog at <a href='http://localhost:8025' target='_blank'>http://localhost:8025</a></li>";
echo "<li>6. You should see a welcome email with verification link</li>";
echo "<li>7. Click the verification link to test email verification</li>";
echo "</ol>";

echo "<h2>Test Summary</h2>";
echo "<p><strong>Email System Status:</strong> Ready for testing</p>";
echo "<p><strong>MailHog URL:</strong> <a href='http://localhost:8025' target='_blank'>http://localhost:8025</a></p>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li>1. Restart Docker containers: <code>docker-compose down && docker-compose up -d</code></li>";
echo "<li>2. Test registration form to send real emails</li>";
echo "<li>3. Check MailHog for received emails</li>";
echo "<li>4. Test email verification links</li>";
echo "</ul>";

echo "<p><strong>Note:</strong> This is a test file. Delete after testing is complete.</p>";
echo "<p><a href='index.php'>← Back to StoreAll.io</a></p>";
?>
