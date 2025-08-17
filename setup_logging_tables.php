<?php
// Setup script for comprehensive logging tables
require_once 'config/config.php';
require_once 'includes/Database.php';

echo "<h1>Setting up Comprehensive Logging Tables</h1>";

try {
    $db = Database::getInstance();
    
    // 1. Application Logs Table
    echo "<h2>1. Creating application_logs table...</h2>";
    $db->query("
        CREATE TABLE IF NOT EXISTS application_logs (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            level enum('DEBUG','INFO','WARNING','ERROR','CRITICAL') NOT NULL,
            message text NOT NULL,
            context json NULL,
            timestamp timestamp NOT NULL,
            ip_address varchar(45) NULL,
            user_agent text NULL,
            user_id bigint unsigned NULL,
            request_uri varchar(500) NULL,
            request_method varchar(10) NULL,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_level (level),
            INDEX idx_user_id (user_id),
            INDEX idx_created_at (created_at),
            INDEX idx_ip_address (ip_address),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )
    ");
    echo "✅ application_logs table created<br>";
    
    // 2. Performance Metrics Table
    echo "<h2>2. Creating performance_metrics table...</h2>";
    $db->query("
        CREATE TABLE IF NOT EXISTS performance_metrics (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            metric_type enum('database_query','api_request','page_load','email_send','file_upload','verification_process') NOT NULL,
            operation_name varchar(255) NOT NULL,
            duration_ms decimal(10,3) NOT NULL,
            memory_usage_mb decimal(10,3) NULL,
            cpu_usage_percent decimal(5,2) NULL,
            user_id bigint unsigned NULL,
            ip_address varchar(45) NULL,
            request_uri varchar(500) NULL,
            additional_data json NULL,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_metric_type (metric_type),
            INDEX idx_duration (duration_ms),
            INDEX idx_user_id (user_id),
            INDEX idx_created_at (created_at),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )
    ");
    echo "✅ performance_metrics table created<br>";
    
    // 3. Client Errors Table
    echo "<h2>3. Creating client_errors table...</h2>";
    $db->query("
        CREATE TABLE IF NOT EXISTS client_errors (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            error_type enum('javascript','validation','ajax','form_submission','ui_interaction') NOT NULL,
            error_message text NOT NULL,
            error_stack text NULL,
            error_file varchar(255) NULL,
            error_line int NULL,
            error_column int NULL,
            user_id bigint unsigned NULL,
            session_id varchar(255) NULL,
            ip_address varchar(45) NULL,
            user_agent text NULL,
            page_url varchar(500) NULL,
            referrer_url varchar(500) NULL,
            browser_info json NULL,
            additional_context json NULL,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_error_type (error_type),
            INDEX idx_user_id (user_id),
            INDEX idx_session_id (session_id),
            INDEX idx_created_at (created_at),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )
    ");
    echo "✅ client_errors table created<br>";
    
    // 4. Database Errors Table
    echo "<h2>4. Creating database_errors table...</h2>";
    $db->query("
        CREATE TABLE IF NOT EXISTS database_errors (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            error_type enum('connection','query','transaction','constraint','timeout','deadlock') NOT NULL,
            error_message text NOT NULL,
            error_code varchar(100) NULL,
            sql_query text NULL,
            query_params json NULL,
            table_name varchar(100) NULL,
            operation_type enum('SELECT','INSERT','UPDATE','DELETE','CREATE','ALTER','DROP') NULL,
            execution_time_ms decimal(10,3) NULL,
            user_id bigint unsigned NULL,
            ip_address varchar(45) NULL,
            request_uri varchar(500) NULL,
            stack_trace text NULL,
            additional_context json NULL,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_error_type (error_type),
            INDEX idx_table_name (table_name),
            INDEX idx_user_id (user_id),
            INDEX idx_created_at (created_at),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )
    ");
    echo "✅ database_errors table created<br>";
    
    // 5. API Requests Table
    echo "<h2>5. Creating api_requests table...</h2>";
    $db->query("
        CREATE TABLE IF NOT EXISTS api_requests (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            endpoint varchar(255) NOT NULL,
            method varchar(10) NOT NULL,
            status_code int NOT NULL,
            response_time_ms decimal(10,3) NOT NULL,
            request_size_bytes int NULL,
            response_size_bytes int NULL,
            user_id bigint unsigned NULL,
            ip_address varchar(45) NULL,
            user_agent text NULL,
            request_headers json NULL,
            request_body json NULL,
            response_body_preview text NULL,
            error_message text NULL,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_endpoint (endpoint),
            INDEX idx_status_code (status_code),
            INDEX idx_user_id (user_id),
            INDEX idx_created_at (created_at),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )
    ");
    echo "✅ api_requests table created<br>";
    
    // 6. Email Logs Table
    echo "<h2>6. Creating email_logs table...</h2>";
    $db->query("
        CREATE TABLE IF NOT EXISTS email_logs (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            email_type enum('welcome','verification','password_reset','notification','system') NOT NULL,
            recipient_email varchar(255) NOT NULL,
            subject varchar(255) NOT NULL,
            status enum('sent','failed','pending','bounced') NOT NULL,
            error_message text NULL,
            sent_at timestamp NULL,
            delivered_at timestamp NULL,
            opened_at timestamp NULL,
            clicked_at timestamp NULL,
            user_id bigint unsigned NULL,
            ip_address varchar(45) NULL,
            additional_data json NULL,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_email_type (email_type),
            INDEX idx_status (status),
            INDEX idx_recipient (recipient_email),
            INDEX idx_user_id (user_id),
            INDEX idx_created_at (created_at),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )
    ");
    echo "✅ email_logs table created<br>";
    
    // 7. Verification Logs Table
    echo "<h2>7. Creating verification_logs table...</h2>";
    $db->query("
        CREATE TABLE IF NOT EXISTS verification_logs (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            verification_type enum('email_verification','password_reset','two_factor') NOT NULL,
            user_id bigint unsigned NOT NULL,
            token_id bigint unsigned NULL,
            action enum('token_created','token_sent','token_verified','token_expired','token_invalid','token_used') NOT NULL,
            status enum('success','failed','pending') NOT NULL,
            error_message text NULL,
            ip_address varchar(45) NULL,
            user_agent text NULL,
            additional_context json NULL,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_verification_type (verification_type),
            INDEX idx_action (action),
            INDEX idx_status (status),
            INDEX idx_user_id (user_id),
            INDEX idx_created_at (created_at),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (token_id) REFERENCES verification_tokens(id) ON DELETE SET NULL
        )
    ");
    echo "✅ verification_logs table created<br>";
    
    // Insert sample data
    echo "<h2>8. Inserting sample data...</h2>";
    $db->insert('application_logs', [
        'level' => 'INFO',
        'message' => 'Logging system initialized',
        'context' => json_encode(['version' => '1.0.0', 'environment' => 'development']),
        'timestamp' => date('Y-m-d H:i:s'),
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Setup Script',
        'request_uri' => '/setup_logging_tables.php',
        'request_method' => 'GET'
    ]);
    echo "✅ Sample data inserted<br>";
    
    // Show all logging tables
    echo "<h2>9. All logging tables created successfully!</h2>";
    $tables = $db->fetchAll("SHOW TABLES LIKE '%log%'");
    echo "<ul>";
    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        echo "<li>✅ $tableName</li>";
    }
    echo "</ul>";
    
    echo "<h2>🎉 Comprehensive Logging System Ready!</h2>";
    echo "<p>The following logging capabilities are now available:</p>";
    echo "<ul>";
    echo "<li><strong>Client-side errors</strong> - JavaScript errors, validation errors, AJAX failures</li>";
    echo "<li><strong>Database errors</strong> - Connection issues, query failures, constraint violations</li>";
    echo "<li><strong>Performance metrics</strong> - Query times, API response times, page load times</li>";
    echo "<li><strong>API requests</strong> - All API calls with response times and status codes</li>";
    echo "<li><strong>Email logs</strong> - Email sending status and delivery tracking</li>";
    echo "<li><strong>Verification logs</strong> - Complete verification process tracking</li>";
    echo "<li><strong>Application logs</strong> - General application events and errors</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
?>
