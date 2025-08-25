<?php
/**
 * MFA Database Setup
 * Creates the necessary database tables for MFA and device tracking
 * 
 * Access this file at: http://localhost:8080/setup_mfa_database.php
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Logger.php';
require_once __DIR__ . '/includes/ErrorHandler.php';
require_once __DIR__ . '/includes/PerformanceMonitor.php';
require_once __DIR__ . '/includes/Database.php';

echo "<h1>MFA Database Setup</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .result { margin: 10px 0; padding: 10px; border-radius: 3px; }
    .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
</style>";

if (isset($_POST['setup_database'])) {
    try {
        $db = Database::getInstance();
        $logger = Logger::getInstance();
        
        echo "<h2>Setting up MFA database tables...</h2>";
        
        // Read the SQL file
        $sqlFile = __DIR__ . '/database/mfa_tables.sql';
        
        if (!file_exists($sqlFile)) {
            echo "<div class='result error'>✗ SQL file not found: {$sqlFile}</div>";
            echo "<div class='result info'>Please ensure the database/mfa_tables.sql file exists in the project directory.</div>";
            exit;
        }
        
        $sql = file_get_contents($sqlFile);
        
        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($statements as $statement) {
            if (empty($statement)) continue;
            
            try {
                $result = $db->query($statement);
                echo "<div class='result success'>✓ Executed: " . substr($statement, 0, 50) . "...</div>";
                $successCount++;
            } catch (Exception $e) {
                echo "<div class='result error'>✗ Error executing: " . substr($statement, 0, 50) . "...<br>Error: " . $e->getMessage() . "</div>";
                $errorCount++;
            }
        }
        
        echo "<h3>Setup Complete</h3>";
        echo "<div class='result info'>✓ Successfully executed {$successCount} statements</div>";
        if ($errorCount > 0) {
            echo "<div class='result error'>✗ Failed to execute {$errorCount} statements</div>";
        }
        
        // Test the tables
        echo "<h3>Testing Tables</h3>";
        $tables = ['login_attempts', 'failed_login_attempts', 'trusted_devices', 'user_login_preferences', 'verification_tokens'];
        
        foreach ($tables as $table) {
            try {
                $result = $db->fetch("SHOW TABLES LIKE '{$table}'");
                if ($result) {
                    echo "<div class='result success'>✓ Table '{$table}' exists</div>";
                } else {
                    echo "<div class='result error'>✗ Table '{$table}' does not exist</div>";
                }
            } catch (Exception $e) {
                echo "<div class='result error'>✗ Error checking table '{$table}': " . $e->getMessage() . "</div>";
            }
        }
        
    } catch (Exception $e) {
        echo "<div class='result error'>✗ Database setup failed: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<p>This script will create the necessary database tables for the MFA system.</p>";
    echo "<p><strong>Warning:</strong> This will create new tables. Make sure you have a backup if needed.</p>";
    echo "<form method='post'>";
    echo "<button type='submit' name='setup_database' style='padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer;'>Setup MFA Database</button>";
    echo "</form>";
}
?>
