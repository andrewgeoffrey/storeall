-- StoreAll.io - Monitoring System Database Tables
-- This script creates the necessary tables for storing monitoring data

-- Table for storing error logs (both client-side and backend)
CREATE TABLE IF NOT EXISTS `error_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `level` enum('DEBUG','INFO','WARNING','ERROR','CRITICAL') NOT NULL DEFAULT 'ERROR',
    `message` text NOT NULL,
    `source` enum('client','backend','database','api') NOT NULL DEFAULT 'backend',
    `file_path` varchar(500) DEFAULT NULL,
    `line_number` int(11) DEFAULT NULL,
    `stack_trace` longtext DEFAULT NULL,
    `user_id` int(11) DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `additional_data` json DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_level` (`level`),
    KEY `idx_source` (`source`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_level_source` (`level`, `source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for storing performance metrics
CREATE TABLE IF NOT EXISTS `performance_metrics` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `metric_type` varchar(100) NOT NULL,
    `value` decimal(10,4) NOT NULL,
    `unit` varchar(20) DEFAULT NULL,
    `page_url` varchar(500) DEFAULT NULL,
    `query_hash` varchar(64) DEFAULT NULL,
    `user_id` int(11) DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `additional_data` json DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_metric_type` (`metric_type`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_metric_type_created` (`metric_type`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for storing system health snapshots
CREATE TABLE IF NOT EXISTS `system_health_snapshots` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `database_status` enum('healthy','warning','unhealthy') NOT NULL DEFAULT 'healthy',
    `api_status` enum('healthy','warning','unhealthy') NOT NULL DEFAULT 'healthy',
    `overall_status` enum('healthy','warning','unhealthy') NOT NULL DEFAULT 'healthy',
    `database_uptime` varchar(50) DEFAULT NULL,
    `active_connections` int(11) DEFAULT NULL,
    `slow_queries` int(11) DEFAULT NULL,
    `total_queries` int(11) DEFAULT NULL,
    `memory_usage_current` varchar(20) DEFAULT NULL,
    `memory_usage_peak` varchar(20) DEFAULT NULL,
    `memory_limit` varchar(20) DEFAULT NULL,
    `error_count_last_hour` int(11) DEFAULT 0,
    `critical_error_count` int(11) DEFAULT 0,
    `additional_data` json DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_overall_status` (`overall_status`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_status_created` (`overall_status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for storing user activity metrics
CREATE TABLE IF NOT EXISTS `user_activity_metrics` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `session_id` varchar(255) NOT NULL,
    `page_views` int(11) DEFAULT 0,
    `interactions` int(11) DEFAULT 0,
    `ajax_requests` int(11) DEFAULT 0,
    `errors_encountered` int(11) DEFAULT 0,
    `session_duration` int(11) DEFAULT 0, -- in seconds
    `started_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `ended_at` timestamp NULL DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `additional_data` json DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_session_id` (`session_id`),
    KEY `idx_started_at` (`started_at`),
    KEY `idx_user_session` (`user_id`, `session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for storing API performance metrics
CREATE TABLE IF NOT EXISTS `api_performance_metrics` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `endpoint` varchar(255) NOT NULL,
    `method` varchar(10) NOT NULL,
    `response_time` decimal(10,4) NOT NULL, -- in milliseconds
    `status_code` int(11) NOT NULL,
    `response_size` int(11) DEFAULT NULL,
    `user_id` int(11) DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `request_data` json DEFAULT NULL,
    `response_data` json DEFAULT NULL,
    `error_message` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_endpoint` (`endpoint`),
    KEY `idx_method` (`method`),
    KEY `idx_status_code` (`status_code`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_endpoint_method` (`endpoint`, `method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert initial system health snapshot
INSERT INTO `system_health_snapshots` (
    `database_status`, 
    `api_status`, 
    `overall_status`, 
    `created_at`
) VALUES (
    'healthy', 
    'healthy', 
    'healthy', 
    NOW()
);

-- Add comments for documentation
ALTER TABLE `error_logs` COMMENT = 'Stores all error logs from client-side and backend systems';
ALTER TABLE `performance_metrics` COMMENT = 'Stores performance metrics like page load times, query execution times';
ALTER TABLE `system_health_snapshots` COMMENT = 'Stores periodic snapshots of system health status';
ALTER TABLE `user_activity_metrics` COMMENT = 'Stores user activity and session metrics';
ALTER TABLE `api_performance_metrics` COMMENT = 'Stores API endpoint performance and response metrics';
