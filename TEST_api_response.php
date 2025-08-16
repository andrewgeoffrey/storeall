<?php
// Test API response
echo "Testing API response...\n";

// Simulate a POST request to the API
$postData = [
    'firstName' => 'Test',
    'lastName' => 'User',
    'email' => 'test@example.com',
    'confirmEmail' => 'test@example.com',
    'companyName' => 'Test Company',
    'phone' => '555-123-4567',
    'website' => 'https://example.com',
    'password' => 'TestPassword123!',
    'confirmPassword' => 'TestPassword123!',
    'terms' => 'on',
    'newsletter' => 'on'
];

// Make a request to the API using internal Docker network
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://nginx/api/register.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "Response: " . $response . "\n";

// Try to decode JSON
$decoded = json_decode($response, true);
if ($decoded) {
    echo "Decoded JSON:\n";
    print_r($decoded);
} else {
    echo "Failed to decode JSON\n";
    echo "JSON Error: " . json_last_error_msg() . "\n";
}
?>
