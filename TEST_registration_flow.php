<?php
// Test script to debug registration flow
require_once 'config/config.php';
require_once 'includes/Database.php';

echo "<h1>Registration Flow Debug Test</h1>";

// Simulate the exact data from the user's form
$testData = [
    'firstName' => 'Jeff',
    'lastName' => 'Dodds',
    'email' => 'stm@andrewgeoffrey.net',
    'confirmEmail' => 'stm@andrewgeoffrey.net',
    'companyName' => 'idaho',
    'phone' => '208-906-3309',
    'website' => 'https://example.com',
    'password' => 'TestPassword123!',
    'confirmPassword' => 'TestPassword123!',
    'terms' => 'on'
];

echo "<h2>Test Data:</h2>";
echo "<pre>" . print_r($testData, true) . "</pre>";

// Test the validation logic
echo "<h2>Validation Test:</h2>";

$firstName = trim($testData['firstName']);
$lastName = trim($testData['lastName']);
$email = strtolower(trim($testData['email']));
$confirmEmail = strtolower(trim($testData['confirmEmail']));
$companyName = trim($testData['companyName']);
$phone = trim($testData['phone']);
$website = trim($testData['website']);
$password = $testData['password'];
$confirmPassword = $testData['confirmPassword'];
$terms = $testData['terms'];

$errors = [];

// Validate required fields
if (empty($firstName)) {
    $errors['firstName'] = 'First name is required';
}

if (empty($lastName)) {
    $errors['lastName'] = 'Last name is required';
}

if (empty($email)) {
    $errors['email'] = 'Email address is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address';
}

if (empty($confirmEmail)) {
    $errors['confirmEmail'] = 'Please confirm your email address';
} elseif (strtolower(trim($email)) !== strtolower(trim($confirmEmail))) {
    $errors['confirmEmail'] = 'Email addresses do not match';
}

if (empty($companyName)) {
    $errors['companyName'] = 'Company name is required';
}

if (empty($password)) {
    $errors['password'] = 'Password is required';
} elseif (strlen($password) < 12) {
    $errors['password'] = 'Password must be at least 12 characters long';
} elseif (!preg_match('/[A-Z]/', $password)) {
    $errors['password'] = 'Password must contain at least one uppercase letter';
} elseif (!preg_match('/[a-z]/', $password)) {
    $errors['password'] = 'Password must contain at least one lowercase letter';
} elseif (!preg_match('/\d/', $password)) {
    $errors['password'] = 'Password must contain at least one number';
} elseif (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
    $errors['password'] = 'Password must contain at least one special character';
}

if (empty($confirmPassword)) {
    $errors['confirmPassword'] = 'Please confirm your password';
} elseif ($password !== $confirmPassword) {
    $errors['confirmPassword'] = 'Passwords do not match';
}

if (empty($terms)) {
    $errors['terms'] = 'You must agree to the Terms of Service and Privacy Policy';
}

echo "<h3>Validation Results:</h3>";
if (empty($errors)) {
    echo "<p>✅ All validation passed!</p>";
} else {
    echo "<p>❌ Validation errors found:</p>";
    echo "<ul>";
    foreach ($errors as $field => $error) {
        echo "<li><strong>$field:</strong> $error</li>";
    }
    echo "</ul>";
}

// Test database connection
echo "<h2>Database Connection Test:</h2>";
try {
    $db = Database::getInstance();
    echo "<p>✅ Database connection successful</p>";
    
    // Check if email already exists
    $existingUser = $db->fetch("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existingUser) {
        echo "<p>❌ Email already exists (User ID: {$existingUser['id']})</p>";
    } else {
        echo "<p>✅ Email is available for registration</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test API response
echo "<h2>API Response Test:</h2>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://nginx/api/register.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($testData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
echo "<p><strong>Response:</strong></p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Try to parse JSON
$jsonData = json_decode($response, true);
if ($jsonData === null) {
    echo "<p>❌ JSON Parse Error: " . json_last_error_msg() . "</p>";
} else {
    echo "<p>✅ JSON Parsed Successfully:</p>";
    echo "<pre>" . print_r($jsonData, true) . "</pre>";
    
    if ($jsonData['success']) {
        echo "<p>🎉 Registration successful!</p>";
    } else {
        echo "<p>❌ Registration failed: " . ($jsonData['message'] ?? 'Unknown error') . "</p>";
    }
}

echo "<hr>";
echo "<h2>🔍 Debug Information:</h2>";
echo "<p><strong>Email (trimmed & lowercase):</strong> '$email'</p>";
echo "<p><strong>Confirm Email (trimmed & lowercase):</strong> '$confirmEmail'</p>";
echo "<p><strong>Email Match:</strong> " . (strtolower(trim($email)) === strtolower(trim($confirmEmail)) ? '✅ Yes' : '❌ No') . "</p>";
echo "<p><strong>Password Length:</strong> " . strlen($password) . " characters</p>";
echo "<p><strong>Password Strength:</strong> " . (strlen($password) >= 12 ? '✅ Strong enough' : '❌ Too short') . "</p>";
?>


