<?php
// Test email sending
echo "Testing email sending...\n";

// Test basic mail() function
$to = "test@example.com";
$subject = "Test Email from StoreAll.io";
$message = "This is a test email to verify the mail() function is working.";
$headers = "From: noreply@storeall.io\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

echo "Sending test email to: $to\n";
$result = mail($to, $subject, $message, $headers);

if ($result) {
    echo "✅ Email sent successfully!\n";
} else {
    echo "❌ Email sending failed!\n";
}

// Check PHP mail configuration
echo "\nPHP Mail Configuration:\n";
echo "sendmail_path: " . ini_get('sendmail_path') . "\n";
echo "SMTP: " . ini_get('SMTP') . "\n";
echo "smtp_port: " . ini_get('smtp_port') . "\n";

// Test if we can connect to MailHog
echo "\nTesting MailHog connection...\n";
$fp = fsockopen('mailhog', 1025, $errno, $errstr, 5);
if ($fp) {
    echo "✅ Can connect to MailHog on port 1025\n";
    fclose($fp);
} else {
    echo "❌ Cannot connect to MailHog: $errstr ($errno)\n";
}

// Test localhost connection
echo "\nTesting localhost connection...\n";
$fp = fsockopen('localhost', 1025, $errno, $errstr, 5);
if ($fp) {
    echo "✅ Can connect to localhost:1025\n";
    fclose($fp);
} else {
    echo "❌ Cannot connect to localhost:1025: $errstr ($errno)\n";
}
?>
