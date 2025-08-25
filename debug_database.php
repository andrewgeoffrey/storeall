<?php
/**
 * Debug Database Connection and Tables
 * Check database connection and table creation issues
 */

// Start output buffering
ob_start();

echo "<h1>Debug Database Connection and Tables</h1>";

// Include the config first
require_once __DIR__ . '/config/config.php';

echo "<p>Config loaded successfully</p>";

try {
    $db = Database::getInstance();
    
    // Check database connection
    echo "<h2>Database Connection</h2>";
    echo "<p>Database Host: " . DB_HOST . "</p>";
    echo "<p>Database Name: " . DB_NAME . "</p>";
    echo "<p>Database User: " . DB_USER . "</p>";
    
    // Test connection
    $connection = $db->getConnection();
    echo "<p>✓ Database connection successful</p>";
    
    // Check current database
    $currentDb = $connection->query("SELECT DATABASE()")->fetchColumn();
    echo "<p>Current Database: {$currentDb}</p>";
    
    // List all tables
    echo "<h2>All Tables in Database</h2>";
    $tables = $connection->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (empty($tables)) {
        echo "<p>No tables found in database</p>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>{$table}</li>";
        }
        echo "</ul>";
    }
    
    // Try to create tables directly
    echo "<h2>Creating Tables Directly</h2>";
    
    $sqlStatements = [
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
    
    foreach ($sqlStatements as $index => $sql) {
        try {
            $connection->exec($sql);
            echo "<p>✓ Table " . ($index + 1) . " created successfully</p>";
        } catch (PDOException $e) {
            echo "<p>✗ Error creating table " . ($index + 1) . ": " . $e->getMessage() . "</p>";
        }
    }
    
    // Verify tables again
    echo "<h2>Verifying Tables After Creation</h2>";
    
    $mfaTables = ['login_attempts', 'failed_login_attempts', 'trusted_devices', 'user_login_preferences', 'verification_tokens'];
    
    foreach ($mfaTables as $table) {
        try {
            $result = $connection->query("SHOW TABLES LIKE '{$table}'")->fetch();
            if ($result) {
                echo "<p>✓ Table '{$table}' exists</p>";
                
                // Show table structure
                $columns = $connection->query("DESCRIBE {$table}")->fetchAll(PDO::FETCH_ASSOC);
                echo "<p><strong>Columns in {$table}:</strong></p>";
                echo "<ul>";
                foreach ($columns as $column) {
                    echo "<li>{$column['Field']} - {$column['Type']}</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>✗ Table '{$table}' does not exist</p>";
            }
        } catch (PDOException $e) {
            echo "<p>✗ Error checking table '{$table}': " . $e->getMessage() . "</p>";
        }
    }
    
    // Test tableExists method
    echo "<h2>Testing tableExists Method</h2>";
    
    foreach ($mfaTables as $table) {
        $exists = $db->tableExists($table);
        echo "<p>" . ($exists ? "✓" : "✗") . " tableExists('{$table}') returns " . ($exists ? "true" : "false") . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p>✗ Error: " . $e->getMessage() . "</p>";
}

// Flush output buffer
ob_end_flush();
?>
