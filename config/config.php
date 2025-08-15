<?php
// StoreAll.io Configuration File

// Environment settings
define('ENVIRONMENT', 'development'); // Change to 'production' for live site

// Application settings
define('APP_NAME', 'StoreAll.io');
define('APP_URL', 'http://localhost:8080');
define('APP_VERSION', '1.0.0');

// Database configuration
define('DB_HOST', 'mysql');
define('DB_NAME', 'storeall_dev');
define('DB_USER', 'storeall_user');
define('DB_PASS', 'storeall_pass');
define('DB_CHARSET', 'utf8mb4');

// Email configuration for development (MailHog)
define('MAIL_HOST', 'mailhog');
define('MAIL_PORT', 1025);
define('MAIL_USERNAME', '');
define('MAIL_PASSWORD', '');
define('MAIL_ENCRYPTION', '');
define('MAIL_FROM_ADDRESS', 'noreply@storeall.io');
define('MAIL_FROM_NAME', 'StoreAll.io');

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
    ini_set('session.cookie_samesite', 'Lax');
    session_name('storeall_session');
    session_start();
}

// Error reporting
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
}

// Timezone
date_default_timezone_set('UTC');

// Security settings
define('CSRF_TOKEN_NAME', 'storeall_csrf_token');
define('PASSWORD_MIN_LENGTH', 12);
define('SESSION_TIMEOUT', 3600); // 1 hour

// File upload settings
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// Logging
define('LOG_LEVEL', ENVIRONMENT === 'development' ? 'DEBUG' : 'INFO');
define('LOG_FILE', __DIR__ . '/../logs/app.log');

// Create logs directory if it doesn't exist
if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}

// Load core classes
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/Logger.php';
require_once __DIR__ . '/../includes/ErrorHandler.php';
require_once __DIR__ . '/../includes/PerformanceMonitor.php';
require_once __DIR__ . '/../includes/helpers.php';
?>