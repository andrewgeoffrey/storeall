<?php
/**
 * Create MFA Tables
 * Creates all required MFA and device tracking database tables
 */

// Start output buffering
ob_start();

echo "<h1>Creating MFA Database Tables</h1>";

// Include the config first
require_once __DIR__ . '/config/config.php';

echo "<p>Config loaded successfully</p>";

try {
    $db = Database::getInstance();
    
    // SQL statements to create MFA tables
    $sqlStatements = [
        // Table to track login attempts
        "CREATE TABLE IF NOT EXISTS `login_attempts` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `email` varchar(255) NOT NULL,
            `user_agent` text,
            `ip_address` varchar(45) NOT NULL,
            `device_fingerprint` varchar(255),
            `location_data` json,
            `success` tinyint(1) DEFAULT 0,
            `mfa_required` tinyint(1) DEFAULT 0,
            `session_id` varchar(255),
            `user_id` int(11),
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_email` (`email`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_ip_address` (`ip_address`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        // Table to track failed login attempts
        "CREATE TABLE IF NOT EXISTS `failed_login_attempts` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `email` varchar(255) NOT NULL,
            `ip_address` varchar(45) NOT NULL,
            `device_fingerprint` varchar(255),
            `first_attempt` timestamp DEFAULT CURRENT_TIMESTAMP,
            `last_attempt` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `attempt_count` int(11) DEFAULT 1,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_email_ip` (`email`, `ip_address`),
            KEY `idx_email` (`email`),
            KEY `idx_ip_address` (`ip_address`),
            KEY `idx_last_attempt` (`last_attempt`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        // Table to store trusted devices
        "CREATE TABLE IF NOT EXISTS `trusted_devices` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `device_fingerprint` varchar(255) NOT NULL,
            `device_name` varchar(255),
            `ip_address` varchar(45),
            `user_agent` text,
            `location_data` json,
            `trusted_until` timestamp NOT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_user_device` (`user_id`, `device_fingerprint`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_trusted_until` (`trusted_until`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        // Table to store user login preferences
        "CREATE TABLE IF NOT EXISTS `user_login_preferences` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `mfa_enabled` tinyint(1) DEFAULT 1,
            `mfa_method` enum('email', 'sms', 'app') DEFAULT 'email',
            `trust_device_days` int(11) DEFAULT 30,
            `notify_on_suspicious_login` tinyint(1) DEFAULT 1,
            `notify_on_new_device` tinyint(1) DEFAULT 1,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_user_id` (`user_id`),
            KEY `idx_user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        // Table to store verification tokens (MFA codes, password resets, etc.)
        "CREATE TABLE IF NOT EXISTS `verification_tokens` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `token` varchar(255) NOT NULL,
            `type` varchar(50) NOT NULL,
            `expires_at` timestamp NOT NULL,
            `used_at` timestamp NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_token` (`token`),
            KEY `idx_type` (`type`),
            KEY `idx_expires_at` (`expires_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
    ];
    
    echo "<h2>Creating Tables...</h2>";
    
    foreach ($sqlStatements as $index => $sql) {
        try {
            $db->query($sql);
            echo "<p>✓ Table " . ($index + 1) . " created successfully</p>";
        } catch (Exception $e) {
            echo "<p>✗ Error creating table " . ($index + 1) . ": " . $e->getMessage() . "</p>";
        }
    }
    
    // Verify tables were created
    echo "<h2>Verifying Tables...</h2>";
    
    $tables = ['login_attempts', 'failed_login_attempts', 'trusted_devices', 'user_login_preferences', 'verification_tokens'];
    
    foreach ($tables as $table) {
        if ($db->tableExists($table)) {
            echo "<p>✓ Table '{$table}' exists</p>";
            
            // Show table structure
            $columns = $db->getTableColumns($table);
            echo "<p><strong>Columns in {$table}:</strong></p>";
            echo "<ul>";
            foreach ($columns as $column) {
                echo "<li>{$column['Field']} - {$column['Type']}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>✗ Table '{$table}' does not exist</p>";
        }
    }
    
    // Insert default preferences for existing users
    echo "<h2>Setting up Default Preferences...</h2>";
    
    try {
        $sql = "INSERT IGNORE INTO user_login_preferences (user_id, mfa_enabled, mfa_method, trust_device_days, notify_on_suspicious_login, notify_on_new_device)
                SELECT id, 1, 'email', 30, 1, 1 FROM users WHERE id NOT IN (SELECT user_id FROM user_login_preferences)";
        $db->query($sql);
        echo "<p>✓ Default preferences inserted for existing users</p>";
    } catch (Exception $e) {
        echo "<p>⚠ Warning: Could not insert default preferences: " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>Setup Complete!</h2>";
    echo "<p>All MFA database tables have been created successfully.</p>";
    echo "<p><a href='run_mfa_tests.php'>Run MFA Tests</a></p>";
    
} catch (Exception $e) {
    echo "<p>✗ Error: " . $e->getMessage() . "</p>";
}

// Flush output buffer
ob_end_flush();
?>
