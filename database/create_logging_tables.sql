-- Comprehensive Logging Tables for StoreAll.io
-- This file creates all necessary tables for logging client-side errors, database errors, and performance metrics

USE storeall_dev;

-- Application Logs Table (for general application logging)
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
);

-- Performance Metrics Table
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
);

-- Client-Side Error Logs Table
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
);

-- Database Error Logs Table (enhanced)
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
);

-- API Request Logs Table
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
);

-- Email Logs Table
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
);

-- Verification Process Logs Table
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
);

-- Create indexes for better performance
CREATE INDEX idx_audit_logs_user_action ON audit_logs(user_id, action);
CREATE INDEX idx_error_logs_type_created ON error_logs(error_type, created_at);
CREATE INDEX idx_performance_metrics_type_duration ON performance_metrics(metric_type, duration_ms);
CREATE INDEX idx_client_errors_type_created ON client_errors(error_type, created_at);
CREATE INDEX idx_database_errors_type_created ON database_errors(error_type, created_at);
CREATE INDEX idx_api_requests_endpoint_status ON api_requests(endpoint, status_code);
CREATE INDEX idx_email_logs_type_status ON email_logs(email_type, status);
CREATE INDEX idx_verification_logs_type_action ON verification_logs(verification_type, action);

-- Insert sample data for testing
INSERT INTO application_logs (level, message, context, timestamp, ip_address, user_agent, request_uri, request_method) VALUES
('INFO', 'Application started', '{"version": "1.0.0", "environment": "development"}', NOW(), '127.0.0.1', 'Docker/1.0', '/', 'GET'),
('INFO', 'Database connection established', '{"host": "mysql", "database": "storeall_dev"}', NOW(), '127.0.0.1', 'Docker/1.0', '/', 'GET');

-- Show created tables
SHOW TABLES LIKE '%log%';



