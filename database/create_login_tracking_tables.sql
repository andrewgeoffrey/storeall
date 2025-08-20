-- Login Tracking and MFA Suppression Tables
-- This script creates tables for tracking login attempts, environment data, and MFA suppression

-- Table to track login attempts and environment data
CREATE TABLE IF NOT EXISTS login_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NOT NULL,
    device_fingerprint VARCHAR(255) NOT NULL,
    location_data JSON NULL,
    success BOOLEAN NOT NULL DEFAULT FALSE,
    failure_reason VARCHAR(255) NULL,
    mfa_required BOOLEAN NOT NULL DEFAULT FALSE,
    mfa_completed BOOLEAN NOT NULL DEFAULT FALSE,
    mfa_method VARCHAR(50) NULL, -- 'email', 'sms', 'totp'
    mfa_code VARCHAR(10) NULL,
    mfa_expires_at TIMESTAMP NULL,
    session_id VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_email (email),
    INDEX idx_ip_address (ip_address),
    INDEX idx_device_fingerprint (device_fingerprint),
    INDEX idx_created_at (created_at),
    INDEX idx_success (success),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Table to store trusted devices/environments for MFA suppression
CREATE TABLE IF NOT EXISTS trusted_devices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    device_fingerprint VARCHAR(255) NOT NULL,
    device_name VARCHAR(255) NULL, -- User-friendly name like "Home Computer"
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NOT NULL,
    location_data JSON NULL,
    mfa_suppressed_until TIMESTAMP NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_user_device (user_id, device_fingerprint),
    INDEX idx_user_id (user_id),
    INDEX idx_device_fingerprint (device_fingerprint),
    INDEX idx_mfa_suppressed_until (mfa_suppressed_until),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table to store user login preferences
CREATE TABLE IF NOT EXISTS user_login_preferences (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    mfa_enabled BOOLEAN NOT NULL DEFAULT TRUE,
    mfa_method VARCHAR(50) NOT NULL DEFAULT 'email', -- 'email', 'sms', 'totp'
    allow_trusted_devices BOOLEAN NOT NULL DEFAULT TRUE,
    trusted_device_duration_days INT NOT NULL DEFAULT 30,
    require_mfa_on_new_device BOOLEAN NOT NULL DEFAULT TRUE,
    notify_on_new_login BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table to track failed login attempts for rate limiting
CREATE TABLE IF NOT EXISTS failed_login_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    device_fingerprint VARCHAR(255) NOT NULL,
    attempt_count INT NOT NULL DEFAULT 1,
    first_attempt_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_attempt_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    locked_until TIMESTAMP NULL,
    
    UNIQUE KEY unique_email_ip_device (email, ip_address, device_fingerprint),
    INDEX idx_email (email),
    INDEX idx_ip_address (ip_address),
    INDEX idx_locked_until (locked_until)
);

-- Insert default login preferences for existing users
INSERT IGNORE INTO user_login_preferences (user_id, mfa_enabled, mfa_method, allow_trusted_devices, trusted_device_duration_days, require_mfa_on_new_device, notify_on_new_login)
SELECT id, TRUE, 'email', TRUE, 30, TRUE, TRUE FROM users;

-- Add indexes for performance
CREATE INDEX idx_login_attempts_user_success ON login_attempts(user_id, success);
CREATE INDEX idx_login_attempts_email_success ON login_attempts(email, success);
CREATE INDEX idx_trusted_devices_user_active ON trusted_devices(user_id, is_active);
