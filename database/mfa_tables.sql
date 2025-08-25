-- MFA and Device Tracking Database Schema
-- This file creates all necessary tables for the MFA system

-- Table to track login attempts
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `email` varchar(255) NOT NULL,
    `user_agent` text,
    `ip_address` varchar(45) NOT NULL,
    `device_fingerprint` varchar(255),
    `location_data` json,
    `success` tinyint(1) DEFAULT 0,
    `mfa_required` tinyint(1) DEFAULT 0,
    `mfa_verified` tinyint(1) DEFAULT 0,
    `suspicious_activity` tinyint(1) DEFAULT 0,
    `session_id` varchar(255),
    `user_id` int(11),
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_email` (`email`),
    KEY `idx_ip_address` (`ip_address`),
    KEY `idx_device_fingerprint` (`device_fingerprint`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table to track failed login attempts for account locking
CREATE TABLE IF NOT EXISTS `failed_login_attempts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `email` varchar(255) NOT NULL,
    `ip_address` varchar(45) NOT NULL,
    `device_fingerprint` varchar(255),
    `attempt_count` int(11) DEFAULT 1,
    `first_attempt` timestamp DEFAULT CURRENT_TIMESTAMP,
    `last_attempt` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `locked_until` timestamp NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_email_ip_device` (`email`, `ip_address`, `device_fingerprint`),
    KEY `idx_email` (`email`),
    KEY `idx_ip_address` (`ip_address`),
    KEY `idx_locked_until` (`locked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table to store trusted devices
CREATE TABLE IF NOT EXISTS `trusted_devices` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `device_fingerprint` varchar(255) NOT NULL,
    `device_name` varchar(255),
    `ip_address` varchar(45),
    `user_agent` text,
    `location_data` json,
    `trusted_until` timestamp NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `last_used` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_user_device` (`user_id`, `device_fingerprint`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_device_fingerprint` (`device_fingerprint`),
    KEY `idx_trusted_until` (`trusted_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table to store user login preferences
CREATE TABLE IF NOT EXISTS `user_login_preferences` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table to store verification tokens (MFA codes, etc.)
CREATE TABLE IF NOT EXISTS `verification_tokens` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `token` varchar(255) NOT NULL,
    `type` enum('mfa_code', 'email_verification', 'password_reset') NOT NULL,
    `expires_at` timestamp NOT NULL,
    `used_at` timestamp NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_token` (`token`),
    KEY `idx_type` (`type`),
    KEY `idx_expires_at` (`expires_at`),
    KEY `idx_used_at` (`used_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default login preferences for existing users (if any)
INSERT IGNORE INTO `user_login_preferences` (`user_id`, `mfa_enabled`, `mfa_method`, `trust_device_days`, `notify_on_suspicious_login`, `notify_on_new_device`)
SELECT 
    u.id,
    1, -- mfa_enabled
    'email', -- mfa_method
    30, -- trust_device_days
    1, -- notify_on_suspicious_login
    1  -- notify_on_new_device
FROM `users` u
WHERE NOT EXISTS (
    SELECT 1 FROM `user_login_preferences` ulp WHERE ulp.user_id = u.id
);

