<?php
// Test creating verification_tokens table
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';

echo "Testing database connection and creating verification_tokens table...\n";

try {
    $db = Database::getInstance();
    
    // Check if verification_tokens table exists
    $tableExists = $db->tableExists('verification_tokens');
    echo "Verification tokens table exists: " . ($tableExists ? 'YES' : 'NO') . "\n";
    
    if (!$tableExists) {
        echo "Creating verification_tokens table...\n";
        
        $sql = "
        CREATE TABLE IF NOT EXISTS verification_tokens (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            token VARCHAR(255) UNIQUE NOT NULL,
            type ENUM('email_verification', 'password_reset') NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            used_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        
        $db->query($sql);
        echo "✅ Verification tokens table created successfully!\n";
        
        // Verify the table was created
        $tableExists = $db->tableExists('verification_tokens');
        echo "Verification tokens table exists after creation: " . ($tableExists ? 'YES' : 'NO') . "\n";
    }
    
    // Check other tables
    $tables = ['users', 'organizations', 'user_roles', 'locations', 'units', 'customers', 'bookings', 'waitlist', 'audit_logs', 'error_logs'];
    
    echo "\nChecking all tables:\n";
    foreach ($tables as $table) {
        $exists = $db->tableExists($table);
        echo "- {$table}: " . ($exists ? 'EXISTS' : 'MISSING') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
