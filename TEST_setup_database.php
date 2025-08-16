<?php
// Comprehensive database setup script
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';

echo "Setting up complete database schema...\n";

try {
    $db = Database::getInstance();
    
    // Ensure we're using the correct database
    echo "Ensuring we're using the correct database...\n";
    $db->query("USE storeall_dev");
    $currentDb = $db->fetchColumn("SELECT DATABASE()");
    echo "Current database: " . ($currentDb ?: 'NULL') . "\n";
    
    // Create all tables
    $tables = [
        'users' => "
            CREATE TABLE IF NOT EXISTS users (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                email_verified_at TIMESTAMP NULL,
                two_factor_enabled BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ",
        'user_roles' => "
            CREATE TABLE IF NOT EXISTS user_roles (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                role ENUM('admin', 'super_user', 'owner', 'customer') NOT NULL,
                organization_id BIGINT UNSIGNED NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ",
        'organizations' => "
            CREATE TABLE IF NOT EXISTS organizations (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                subdomain VARCHAR(100) UNIQUE NOT NULL,
                domain VARCHAR(255) NULL,
                tier ENUM('tier1', 'tier2', 'tier3') DEFAULT 'tier1',
                status ENUM('active', 'suspended', 'cancelled', 'trial') DEFAULT 'trial',
                trial_ends_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ",
        'locations' => "
            CREATE TABLE IF NOT EXISTS locations (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                organization_id BIGINT UNSIGNED NOT NULL,
                name VARCHAR(255) NOT NULL,
                address TEXT NOT NULL,
                phone VARCHAR(20) NULL,
                email VARCHAR(255) NULL,
                is_primary BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
            )
        ",
        'units' => "
            CREATE TABLE IF NOT EXISTS units (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                location_id BIGINT UNSIGNED NOT NULL,
                unit_number VARCHAR(50) NOT NULL,
                size DECIMAL(8,2) NOT NULL,
                unit_type ENUM('climate_controlled', 'standard', 'outdoor') NOT NULL,
                status ENUM('available', 'occupied', 'reserved', 'maintenance') DEFAULT 'available',
                monthly_rate DECIMAL(10,2) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE
            )
        ",
        'customers' => "
            CREATE TABLE IF NOT EXISTS customers (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                organization_id BIGINT UNSIGNED NOT NULL,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(20) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
            )
        ",
        'bookings' => "
            CREATE TABLE IF NOT EXISTS bookings (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                unit_id BIGINT UNSIGNED NOT NULL,
                customer_id BIGINT UNSIGNED NOT NULL,
                start_date DATE NOT NULL,
                end_date DATE NULL,
                status ENUM('active', 'cancelled', 'completed') DEFAULT 'active',
                monthly_rate DECIMAL(10,2) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
                FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
            )
        ",
        'waitlist' => "
            CREATE TABLE IF NOT EXISTS waitlist (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                organization_id BIGINT UNSIGNED NOT NULL,
                customer_id BIGINT UNSIGNED NOT NULL,
                unit_size DECIMAL(8,2) NOT NULL,
                unit_type ENUM('climate_controlled', 'standard', 'outdoor') NOT NULL,
                priority INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
                FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
            )
        ",
        'audit_logs' => "
            CREATE TABLE IF NOT EXISTS audit_logs (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NULL,
                action VARCHAR(255) NOT NULL,
                table_name VARCHAR(100) NULL,
                record_id BIGINT UNSIGNED NULL,
                old_values JSON NULL,
                new_values JSON NULL,
                ip_address VARCHAR(45) NULL,
                user_agent TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        ",
        'verification_tokens' => "
            CREATE TABLE IF NOT EXISTS verification_tokens (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                token VARCHAR(255) UNIQUE NOT NULL,
                type ENUM('email_verification', 'password_reset') NOT NULL,
                expires_at TIMESTAMP NOT NULL,
                used_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ",
        'error_logs' => "
            CREATE TABLE IF NOT EXISTS error_logs (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                error_type ENUM('client', 'server', 'database') NOT NULL,
                error_message TEXT NOT NULL,
                error_code VARCHAR(100) NULL,
                file_name VARCHAR(255) NULL,
                line_number INT NULL,
                stack_trace TEXT NULL,
                user_id BIGINT UNSIGNED NULL,
                ip_address VARCHAR(45) NULL,
                user_agent TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        "
    ];
    
    echo "Creating tables...\n";
    foreach ($tables as $tableName => $sql) {
        echo "Creating {$tableName}... ";
        $db->query($sql);
        echo "✅\n";
    }
    
    // Insert default admin user
    echo "Creating default admin user... ";
    $adminExists = $db->fetch("SELECT id FROM users WHERE email = 'admin@storeall.io'");
    if (!$adminExists) {
        $db->insert('users', [
            'email' => 'admin@storeall.io',
            'password_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // admin123
            'first_name' => 'System',
            'last_name' => 'Admin',
            'email_verified_at' => date('Y-m-d H:i:s')
        ]);
        
        $adminId = $db->connection->lastInsertId();
        $db->insert('user_roles', [
            'user_id' => $adminId,
            'role' => 'admin'
        ]);
        echo "✅\n";
    } else {
        echo "Already exists ✅\n";
    }
    
    // Create indexes
    echo "Creating indexes...\n";
    $indexes = [
        "CREATE INDEX idx_users_email ON users(email)",
        "CREATE INDEX idx_organizations_subdomain ON organizations(subdomain)",
        "CREATE INDEX idx_units_location_status ON units(location_id, status)",
        "CREATE INDEX idx_bookings_unit_status ON bookings(unit_id, status)",
        "CREATE INDEX idx_waitlist_organization_priority ON waitlist(organization_id, priority)",
        "CREATE INDEX idx_audit_logs_user_action ON audit_logs(user_id, action)",
        "CREATE INDEX idx_error_logs_type_created ON error_logs(error_type, created_at)"
    ];
    
    foreach ($indexes as $index) {
        try {
            $db->query($index);
        } catch (Exception $e) {
            // Index might already exist, that's okay
            echo "Index creation skipped (may already exist)\n";
        }
    }
    echo "✅ All indexes created\n";
    
    echo "\n🎉 Database setup complete!\n";
    
    // Verify all tables exist
    echo "\nVerifying all tables:\n";
    foreach (array_keys($tables) as $tableName) {
        $exists = $db->tableExists($tableName);
        echo "- {$tableName}: " . ($exists ? 'EXISTS' : 'MISSING') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
