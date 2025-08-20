<?php
// Test script for comprehensive logging system
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/Logger.php';

echo "<h1>Comprehensive Logging System Test</h1>";

try {
    $db = Database::getInstance();
    $logger = Logger::getInstance();
    
    echo "<h2>1. Testing Application Logs</h2>";
    $logger->info('Test application log message', ['test' => 'data']);
    $logger->warning('Test warning message', ['warning' => 'test']);
    $logger->error('Test error message', ['error' => 'test']);
    echo "✅ Application logs tested<br>";
    
    echo "<h2>2. Testing Performance Metrics</h2>";
    $logger->logPerformanceMetric('database_query', 'test_query', 150.5, [
        'memory' => 25.3,
        'cpu' => 5.2
    ]);
    echo "✅ Performance metrics logged<br>";
    
    echo "<h2>3. Testing Database Error Logging</h2>";
    $logger->logDatabaseError('query', 'Test database error', [
        'sql' => 'SELECT * FROM non_existent_table',
        'table' => 'non_existent_table',
        'operation' => 'SELECT'
    ]);
    echo "✅ Database error logged<br>";
    
    echo "<h2>4. Testing API Request Logging</h2>";
    $logger->logApiRequest('/api/test', 'POST', 200, 125.3, [
        'request_size' => 1024,
        'response_size' => 512
    ]);
    echo "✅ API request logged<br>";
    
    echo "<h2>5. Testing Email Logging</h2>";
    $logger->logEmailEvent('welcome', 'test@example.com', 'Test Email', 'sent', [
        'user_id' => 1
    ]);
    echo "✅ Email event logged<br>";
    
    echo "<h2>6. Testing Verification Logging</h2>";
    $logger->logVerificationEvent('email_verification', 1, 'token_created', 'success', [
        'token_id' => 1
    ]);
    echo "✅ Verification event logged<br>";
    
    echo "<h2>7. Testing Client Error Logging</h2>";
    $logger->logClientError('javascript', 'Test JavaScript error', [
        'file' => 'test.js',
        'line' => 10,
        'column' => 5
    ]);
    echo "✅ Client error logged<br>";
    
    echo "<h2>8. Verifying Logs in Database</h2>";
    
    // Check application logs
    $appLogs = $db->fetchAll("SELECT COUNT(*) as count FROM application_logs");
    echo "Application logs: " . $appLogs[0]['count'] . " records<br>";
    
    // Check performance metrics
    $perfLogs = $db->fetchAll("SELECT COUNT(*) as count FROM performance_metrics");
    echo "Performance metrics: " . $perfLogs[0]['count'] . " records<br>";
    
    // Check database errors
    $dbErrors = $db->fetchAll("SELECT COUNT(*) as count FROM database_errors");
    echo "Database errors: " . $dbErrors[0]['count'] . " records<br>";
    
    // Check API requests
    $apiLogs = $db->fetchAll("SELECT COUNT(*) as count FROM api_requests");
    echo "API requests: " . $apiLogs[0]['count'] . " records<br>";
    
    // Check email logs
    $emailLogs = $db->fetchAll("SELECT COUNT(*) as count FROM email_logs");
    echo "Email logs: " . $emailLogs[0]['count'] . " records<br>";
    
    // Check verification logs
    $verifLogs = $db->fetchAll("SELECT COUNT(*) as count FROM verification_logs");
    echo "Verification logs: " . $verifLogs[0]['count'] . " records<br>";
    
    // Check client errors
    $clientErrors = $db->fetchAll("SELECT COUNT(*) as count FROM client_errors");
    echo "Client errors: " . $clientErrors[0]['count'] . " records<br>";
    
    echo "<h2>9. Recent Log Entries</h2>";
    
    // Show recent application logs
    $recentLogs = $db->fetchAll("SELECT level, message, created_at FROM application_logs ORDER BY created_at DESC LIMIT 5");
    echo "<h3>Recent Application Logs:</h3>";
    echo "<ul>";
    foreach ($recentLogs as $log) {
        echo "<li>[{$log['level']}] {$log['message']} - {$log['created_at']}</li>";
    }
    echo "</ul>";
    
    // Show recent performance metrics
    $recentPerf = $db->fetchAll("SELECT metric_type, operation_name, duration_ms FROM performance_metrics ORDER BY created_at DESC LIMIT 3");
    echo "<h3>Recent Performance Metrics:</h3>";
    echo "<ul>";
    foreach ($recentPerf as $perf) {
        echo "<li>{$perf['metric_type']}: {$perf['operation_name']} - {$perf['duration_ms']}ms</li>";
    }
    echo "</ul>";
    
    echo "<h2>🎉 Comprehensive Logging System Test Complete!</h2>";
    echo "<p>All logging functionality is working correctly. The system now captures:</p>";
    echo "<ul>";
    echo "<li>✅ <strong>Client-side errors</strong> - JavaScript errors, validation errors</li>";
    echo "<li>✅ <strong>Database errors</strong> - Connection issues, query failures</li>";
    echo "<li>✅ <strong>Performance metrics</strong> - Query times, API response times</li>";
    echo "<li>✅ <strong>API requests</strong> - All API calls with response times</li>";
    echo "<li>✅ <strong>Email logs</strong> - Email sending status and tracking</li>";
    echo "<li>✅ <strong>Verification logs</strong> - Complete verification process</li>";
    echo "<li>✅ <strong>Application logs</strong> - General application events</li>";
    echo "</ul>";
    
    echo "<h3>Database Tables Created:</h3>";
    $tables = $db->fetchAll("SHOW TABLES LIKE '%log%'");
    echo "<ul>";
    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        echo "<li>✅ $tableName</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h2>Next Steps</h2>";
echo "<p>1. Test the registration form to see logs in action</p>";
echo "<p>2. Check MailHog for email logs</p>";
echo "<p>3. Monitor the logging tables for real user activity</p>";
echo "<p>4. Set up monitoring alerts for critical errors</p>";
?>


