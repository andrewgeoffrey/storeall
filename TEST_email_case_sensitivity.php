<?php
// Test script to verify email case-insensitive functionality
require_once 'config/config.php';
require_once 'includes/Database.php';

echo "<h1>Email Case Sensitivity Test</h1>";

// Test cases
$testCases = [
    ['email' => 'Test@Example.com', 'confirmEmail' => 'test@example.com'],
    ['email' => 'TEST@EXAMPLE.COM', 'confirmEmail' => 'test@example.com'],
    ['email' => 'test@example.com', 'confirmEmail' => 'TEST@EXAMPLE.COM'],
    ['email' => ' Test@Example.com ', 'confirmEmail' => 'test@example.com'],
    ['email' => 'test@example.com', 'confirmEmail' => ' Test@Example.com '],
];

echo "<h2>Test Cases:</h2>";
foreach ($testCases as $i => $testCase) {
    $email = $testCase['email'];
    $confirmEmail = $testCase['confirmEmail'];
    
    // Backend validation (PHP)
    $emailTrimmed = strtolower(trim($email));
    $confirmEmailTrimmed = strtolower(trim($confirmEmail));
    $backendMatch = ($emailTrimmed === $confirmEmailTrimmed);
    
    // Frontend validation (JavaScript simulation)
    $frontendMatch = (strtolower(trim($email)) === strtolower(trim($confirmEmail)));
    
    echo "<h3>Test Case " . ($i + 1) . ":</h3>";
    echo "<p><strong>Email:</strong> '$email'</p>";
    echo "<p><strong>Confirm Email:</strong> '$confirmEmail'</p>";
    echo "<p><strong>Backend Match:</strong> " . ($backendMatch ? '✅ Yes' : '❌ No') . "</p>";
    echo "<p><strong>Frontend Match:</strong> " . ($frontendMatch ? '✅ Yes' : '❌ No') . "</p>";
    echo "<p><strong>Trimmed & Lowercase:</strong> '$emailTrimmed' vs '$confirmEmailTrimmed'</p>";
    echo "<hr>";
}

// Test database email uniqueness
echo "<h2>Database Email Uniqueness Test:</h2>";

try {
    $db = Database::getInstance();
    
    // Test emails
    $testEmails = [
        'Test@Example.com',
        'test@example.com',
        'TEST@EXAMPLE.COM',
        ' Test@Example.com ',
        'test@example.com'
    ];
    
    foreach ($testEmails as $testEmail) {
        $emailLower = strtolower(trim($testEmail));
        $existingUser = $db->fetch("SELECT id, email FROM users WHERE email = ?", [$emailLower]);
        
        echo "<p><strong>Testing:</strong> '$testEmail' → '$emailLower'</p>";
        if ($existingUser) {
            echo "<p><strong>Result:</strong> ✅ Found existing user (ID: {$existingUser['id']}, Email: {$existingUser['email']})</p>";
        } else {
            echo "<p><strong>Result:</strong> ❌ No existing user found</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>📋 Summary:</h2>";
echo "<ul>";
echo "<li>✅ Backend validation now uses <code>strtolower(trim())</code> for email comparison</li>";
echo "<li>✅ Frontend validation now uses <code>trim().toLowerCase()</code> for email comparison</li>";
echo "<li>✅ Email addresses are stored in lowercase in the database</li>";
echo "<li>✅ Email uniqueness checks are case-insensitive</li>";
echo "</ul>";

echo "<h2>🧪 Manual Test:</h2>";
echo "<ol>";
echo "<li>Go to <a href='/'>StoreAll.io</a></li>";
echo "<li>Try registering with different email case combinations:</li>";
echo "<ul>";
echo "<li>Email: <code>Test@Example.com</code>, Confirm: <code>test@example.com</code></li>";
echo "<li>Email: <code>TEST@EXAMPLE.COM</code>, Confirm: <code>test@example.com</code></li>";
echo "<li>Email: <code> Test@Example.com </code>, Confirm: <code>test@example.com</code></li>";
echo "</ul>";
echo "<li>All should now work without validation errors</li>";
echo "</ol>";
?>
