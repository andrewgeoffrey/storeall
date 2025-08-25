<?php
require_once 'config/config.php';
require_once 'includes/LoginTracker.php';

echo "<h1>Debug Account Locking</h1>";

$loginTracker = new LoginTracker();
$testEmail = 'test_' . time() . '@example.com';

echo "<h2>Step 1: Check initial status</h2>";
$lockStatus = $loginTracker->isAccountLocked($testEmail, '192.168.1.100', 'test_fingerprint');
echo "Initial lock status: " . ($lockStatus['locked'] ? 'LOCKED' : 'NOT LOCKED') . "<br>";
if ($lockStatus['locked']) {
    echo "Locked until: " . $lockStatus['locked_until'] . "<br>";
    echo "Failed count: " . $lockStatus['failed_count'] . "<br>";
}

echo "<h2>Step 2: Record failed attempts</h2>";
for ($i = 1; $i <= 6; $i++) {
    echo "Recording failed attempt #{$i}<br>";
    $loginTracker->recordFailedAttempt($testEmail, '192.168.1.100', 'test_fingerprint');
    
    // Check status after each attempt
    $lockStatus = $loginTracker->isAccountLocked($testEmail, '192.168.1.100', 'test_fingerprint');
    echo "After attempt #{$i}: " . ($lockStatus['locked'] ? 'LOCKED' : 'NOT LOCKED') . " (count: " . ($lockStatus['failed_count'] ?? 0) . ")<br>";
}

echo "<h2>Step 3: Check final status</h2>";
$lockStatus = $loginTracker->isAccountLocked($testEmail, '192.168.1.100', 'test_fingerprint');
echo "Final lock status: " . ($lockStatus['locked'] ? 'LOCKED' : 'NOT LOCKED') . "<br>";
if ($lockStatus['locked']) {
    echo "Locked until: " . $lockStatus['locked_until'] . "<br>";
    echo "Failed count: " . $lockStatus['failed_count'] . "<br>";
}

echo "<h2>Step 4: Check database directly</h2>";
$db = Database::getInstance();
$sql = "SELECT * FROM failed_login_attempts WHERE email = ?";
$result = $db->fetch($sql, [$testEmail]);
if ($result) {
    echo "Database record found:<br>";
    echo "Email: " . $result['email'] . "<br>";
    echo "Attempt count: " . $result['attempt_count'] . "<br>";
    echo "Last attempt: " . $result['last_attempt_at'] . "<br>";
    echo "First attempt: " . $result['first_attempt_at'] . "<br>";
} else {
    echo "No database record found!<br>";
}

echo "<h2>Step 5: Clear attempts</h2>";
$loginTracker->clearFailedAttempts($testEmail, '192.168.1.100', 'test_fingerprint');
$lockStatus = $loginTracker->isAccountLocked($testEmail, '192.168.1.100', 'test_fingerprint');
echo "After clearing: " . ($lockStatus['locked'] ? 'LOCKED' : 'NOT LOCKED') . "<br>";
?>
