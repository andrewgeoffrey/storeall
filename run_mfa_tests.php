<?php
/**
 * MFA Test Runner
 * Simple test runner for the MFA system
 * 
 * Access this file at: http://localhost:8080/run_mfa_tests.php
 */

// Start output buffering to prevent session warnings
ob_start();

echo "<h1>MFA System Test Runner</h1>";
echo "<p>Click the button below to run the MFA system tests:</p>";
echo "<form method='post'>";
echo "<button type='submit' name='run_tests' style='padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 5px; cursor: pointer;'>Run MFA Tests</button>";
echo "</form>";

if (isset($_POST['run_tests'])) {
    echo "<hr>";
    echo "<h2>Running Tests...</h2>";
    
    // Include and run the test file
    include __DIR__ . '/test_mfa_system.php';
}

// Flush the output buffer
ob_end_flush();
?>
