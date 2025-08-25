<?php
/**
 * Fix MFA Tables
 * Fixes any issues with the MFA database tables
 * 
 * Access this file at: http://localhost:8080/fix_mfa_tables.php
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Logger.php';
require_once __DIR__ . '/includes/ErrorHandler.php';
require_once __DIR__ . '/includes/PerformanceMonitor.php';
require_once __DIR__ . '/includes/Database.php';

echo "<h1>Fix MFA Tables</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .result { margin: 10px 0; padding: 10px; border-radius: 3px; }
    .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    .warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
</style>";

if (isset($_POST['fix_tables'])) {
    try {
        $db = Database::getInstance();
        $logger = Logger::getInstance();
        
        echo "<h2>Fixing MFA database tables...</h2>";
        
        // Check if user_login_preferences table has the correct structure
        $columns = $db->fetchAll("DESCRIBE user_login_preferences");
        $columnNames = array_column($columns, 'Field');
        
        echo "<div class='result info'>Current columns in user_login_preferences: " . implode(', ', $columnNames) . "</div>";
        
        $missingColumns = [];
        $requiredColumns = ['trust_device_days', 'notify_on_suspicious_login', 'notify_on_new_device'];
        
        foreach ($requiredColumns as $column) {
            if (!in_array($column, $columnNames)) {
                $missingColumns[] = $column;
            }
        }
        
        if (!empty($missingColumns)) {
            echo "<div class='result warning'>Missing columns: " . implode(', ', $missingColumns) . "</div>";
            
            // Add missing columns
            foreach ($missingColumns as $column) {
                $sql = "";
                switch ($column) {
                    case 'trust_device_days':
                        $sql = "ALTER TABLE user_login_preferences ADD COLUMN trust_device_days int(11) DEFAULT 30";
                        break;
                    case 'notify_on_suspicious_login':
                        $sql = "ALTER TABLE user_login_preferences ADD COLUMN notify_on_suspicious_login tinyint(1) DEFAULT 1";
                        break;
                    case 'notify_on_new_device':
                        $sql = "ALTER TABLE user_login_preferences ADD COLUMN notify_on_new_device tinyint(1) DEFAULT 1";
                        break;
                }
                
                if ($sql) {
                    try {
                        $db->query($sql);
                        echo "<div class='result success'>✓ Added column: {$column}</div>";
                    } catch (Exception $e) {
                        echo "<div class='result error'>✗ Failed to add column {$column}: " . $e->getMessage() . "</div>";
                    }
                }
            }
        } else {
            echo "<div class='result success'>✓ All required columns exist</div>";
        }
        
        // Now try to insert default preferences
        echo "<h3>Inserting default preferences...</h3>";
        try {
            $result = $db->query("
                INSERT IGNORE INTO user_login_preferences 
                (user_id, mfa_enabled, mfa_method, trust_device_days, notify_on_suspicious_login, notify_on_new_device)
                SELECT 
                    u.id,
                    1, -- mfa_enabled
                    'email', -- mfa_method
                    30, -- trust_device_days
                    1, -- notify_on_suspicious_login
                    1  -- notify_on_new_device
                FROM users u
                WHERE NOT EXISTS (
                    SELECT 1 FROM user_login_preferences ulp WHERE ulp.user_id = u.id
                )
            ");
            echo "<div class='result success'>✓ Default preferences inserted successfully</div>";
        } catch (Exception $e) {
            echo "<div class='result error'>✗ Failed to insert default preferences: " . $e->getMessage() . "</div>";
        }
        
        echo "<h3>Fix Complete</h3>";
        echo "<div class='result info'>✓ MFA tables have been fixed</div>";
        
    } catch (Exception $e) {
        echo "<div class='result error'>✗ Fix failed: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<p>This script will fix any issues with the MFA database tables.</p>";
    echo "<p><strong>What it does:</strong></p>";
    echo "<ul>";
    echo "<li>Checks if all required columns exist in user_login_preferences table</li>";
    echo "<li>Adds any missing columns</li>";
    echo "<li>Inserts default preferences for existing users</li>";
    echo "</ul>";
    echo "<form method='post'>";
    echo "<button type='submit' name='fix_tables' style='padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;'>Fix MFA Tables</button>";
    echo "</form>";
}
?>
