<?php
// Test script to debug API response
require_once 'config/config.php';
require_once 'includes/Database.php';

echo "<h1>API Response Debug Test</h1>";

// Simulate the registration data
$testData = [
    'firstName' => 'Test',
    'lastName' => 'User',
    'email' => 'test' . time() . '@example.com',
    'confirmEmail' => 'test' . time() . '@example.com',
    'companyName' => 'Test Company',
    'phone' => '555-123-4567',
    'website' => 'https://example.com',
    'password' => 'TestPassword123!',
    'confirmPassword' => 'TestPassword123!',
    'terms' => 'on'
];

echo "<h2>Test Data:</h2>";
echo "<pre>" . print_r($testData, true) . "</pre>";

// Make a test request to the API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://nginx/api/register.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($testData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h2>API Response:</h2>";
echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
echo "<p><strong>Raw Response:</strong></p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Parse the response
$headerSize = strpos($response, "\r\n\r\n");
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize + 4);

echo "<h2>Response Headers:</h2>";
echo "<pre>" . htmlspecialchars($headers) . "</pre>";

echo "<h2>Response Body:</h2>";
echo "<pre>" . htmlspecialchars($body) . "</pre>";

// Try to parse JSON
$jsonData = json_decode($body, true);
if ($jsonData === null) {
    echo "<h2>❌ JSON Parse Error:</h2>";
    echo "<p>Error: " . json_last_error_msg() . "</p>";
} else {
    echo "<h2>✅ JSON Parsed Successfully:</h2>";
    echo "<pre>" . print_r($jsonData, true) . "</pre>";
}

echo "<hr>";
echo "<h2>🔍 Debug Information:</h2>";
echo "<p><strong>Content-Type Header:</strong> " . (strpos($headers, 'Content-Type: application/json') !== false ? 'Present' : 'Missing') . "</p>";
echo "<p><strong>Response Length:</strong> " . strlen($body) . " characters</p>";
echo "<p><strong>First 100 chars:</strong> " . htmlspecialchars(substr($body, 0, 100)) . "</p>";
?>
