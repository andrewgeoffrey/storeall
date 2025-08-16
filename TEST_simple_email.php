<?php
// Simple Email Test
require_once 'config/config.php';
require_once 'includes/Email.php';

echo "<h1>Simple Email Test</h1>";

try {
    $email = Email::getInstance();
    $result = $email->send('test@example.com', 'Test Email', 'This is a test email from StoreAll.io');
    
    if ($result) {
        echo "✅ Email sent successfully!<br>";
        echo "📧 Check MailHog at: <a href='http://localhost:8025' target='_blank'>http://localhost:8025</a><br>";
    } else {
        echo "❌ Email failed to send<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<p><a href='index.php'>← Back to StoreAll.io</a></p>";
?>

