<?php
/**
 * TEST_setup_database.php
 * Database setup and initialization script
 * 
 * This file is for testing and setup purposes only.
 * It should be deleted after initial setup is complete.
 */

// Prevent direct access in production
if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
    die('Setup files are not allowed in production environment.');
}

// Load configuration
require_once 'config/config.php';
require_once 'includes/Database.php';

echo "<h1>StoreAll.io - Database Setup</h1>\n";

try {
    $db = Database::getInstance();
    echo "<p>✓ Database connection successful</p>\n";
    
    // Create tables
    $tables = [
        'users' => "
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                phone VARCHAR(20),
                role ENUM('admin', 'super_user', 'owner', 'customer') NOT NULL,
                email_verified BOOLEAN DEFAULT FALSE,
                email_verification_token VARCHAR(255),
                password_reset_token VARCHAR(255),
                password_reset_expires DATETIME,
                last_login DATETIME,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_email (email),
                INDEX idx_role (role)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'organizations' => "
            CREATE TABLE IF NOT EXISTS organizations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                owner_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) UNIQUE NOT NULL,
                description TEXT,
                address TEXT,
                phone VARCHAR(20),
                email VARCHAR(255),
                website VARCHAR(255),
                tier ENUM('tier1', 'tier2', 'tier3') DEFAULT 'tier1',
                trial_ends_at DATETIME,
                subscription_status ENUM('trial', 'active', 'past_due', 'canceled') DEFAULT 'trial',
                stripe_customer_id VARCHAR(255),
                stripe_subscription_id VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_owner (owner_id),
                INDEX idx_slug (slug),
                INDEX idx_tier (tier)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'locations' => "
            CREATE TABLE IF NOT EXISTS locations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                organization_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                address TEXT NOT NULL,
                city VARCHAR(100) NOT NULL,
                state VARCHAR(50) NOT NULL,
                zip_code VARCHAR(20) NOT NULL,
                phone VARCHAR(20),
                is_primary BOOLEAN DEFAULT FALSE,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
                INDEX idx_organization (organization_id),
                INDEX idx_primary (is_primary)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'units' => "
            CREATE TABLE IF NOT EXISTS units (
                id INT AUTO_INCREMENT PRIMARY KEY,
                location_id INT NOT NULL,
                unit_number VARCHAR(50) NOT NULL,
                size VARCHAR(50) NOT NULL,
                dimensions VARCHAR(100),
                price DECIMAL(10,2) NOT NULL,
                status ENUM('available', 'occupied', 'reserved', 'maintenance') DEFAULT 'available',
                description TEXT,
                features JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE,
                UNIQUE KEY unique_unit_location (location_id, unit_number),
                INDEX idx_location (location_id),
                INDEX idx_status (status),
                INDEX idx_size (size)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'customers' => "
            CREATE TABLE IF NOT EXISTS customers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                organization_id INT NOT NULL,
                user_id INT,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(20),
                address TEXT,
                city VARCHAR(100),
                state VARCHAR(50),
                zip_code VARCHAR(20),
                custom_fields JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_organization (organization_id),
                INDEX idx_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'rentals' => "
            CREATE TABLE IF NOT EXISTS rentals (
                id INT AUTO_INCREMENT PRIMARY KEY,
                unit_id INT NOT NULL,
                customer_id INT NOT NULL,
                organization_id INT NOT NULL,
                start_date DATE NOT NULL,
                end_date DATE,
                monthly_rate DECIMAL(10,2) NOT NULL,
                security_deposit DECIMAL(10,2) DEFAULT 0,
                status ENUM('active', 'past_due', 'terminated', 'expired') DEFAULT 'active',
                stripe_subscription_id VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
                FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
                FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
                INDEX idx_unit (unit_id),
                INDEX idx_customer (customer_id),
                INDEX idx_organization (organization_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'waitlist' => "
            CREATE TABLE IF NOT EXISTS waitlist (
                id INT AUTO_INCREMENT PRIMARY KEY,
                organization_id INT NOT NULL,
                customer_id INT NOT NULL,
                unit_size VARCHAR(50) NOT NULL,
                location_id INT,
                priority INT DEFAULT 0,
                notes TEXT,
                status ENUM('waiting', 'notified', 'contacted', 'removed') DEFAULT 'waiting',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
                FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
                FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL,
                INDEX idx_organization (organization_id),
                INDEX idx_customer (customer_id),
                INDEX idx_size (unit_size),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'error_logs' => "
            CREATE TABLE IF NOT EXISTS error_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                error_type VARCHAR(50) NOT NULL,
                message TEXT NOT NULL,
                file_path VARCHAR(500),
                line_number INT,
                url VARCHAR(500),
                user_agent TEXT,
                ip_address VARCHAR(45),
                user_id INT,
                session_id VARCHAR(255),
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                stack_trace TEXT,
                metadata JSON,
                INDEX idx_error_type (error_type),
                INDEX idx_timestamp (timestamp),
                INDEX idx_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'performance_logs' => "
            CREATE TABLE IF NOT EXISTS performance_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                type VARCHAR(50) NOT NULL,
                duration DECIMAL(10,4),
                page_load_time DECIMAL(10,4),
                query_count INT,
                total_query_time DECIMAL(10,4),
                sql_query TEXT,
                parameters TEXT,
                file_path VARCHAR(500),
                line_number INT,
                url VARCHAR(500),
                user_agent TEXT,
                ip_address VARCHAR(45),
                user_id INT,
                session_id VARCHAR(255),
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                metadata JSON,
                INDEX idx_type (type),
                INDEX idx_timestamp (timestamp),
                INDEX idx_duration (duration),
                INDEX idx_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        "
    ];
    
    foreach ($tables as $tableName => $sql) {
        $result = $db->query($sql);
        echo "<p>✓ Table '$tableName' created/verified</p>\n";
    }
    
    // Create default admin user
    $adminEmail = 'admin@storeall.io';
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    
    $checkAdmin = $db->query("SELECT id FROM users WHERE email = ?", [$adminEmail]);
    
    if (empty($checkAdmin)) {
        $db->query("
            INSERT INTO users (email, password_hash, first_name, last_name, role, email_verified) 
            VALUES (?, ?, 'System', 'Administrator', 'admin', TRUE)
        ", [$adminEmail, $adminPassword]);
        
        echo "<p>✓ Default admin user created (admin@storeall.io / admin123)</p>\n";
    } else {
        echo "<p>✓ Admin user already exists</p>\n";
    }
    
    echo "<h2>Database setup completed successfully!</h2>\n";
    echo "<p><strong>Remember to delete this file after setup is complete.</strong></p>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>\n";
    echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>\n";
}
?>
