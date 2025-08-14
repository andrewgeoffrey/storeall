<?php
// Session configuration - MUST be the very first thing, before any output
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_name('storeall_session');
    session_start();
}

/**
 * StoreAll Configuration File
 * Main configuration settings for the application
 */

// Environment setting
define('ENVIRONMENT', 'development');

// Database configuration for Docker
define('DB_HOST', 'mysql');
define('DB_NAME', 'storeall_dev');
define('DB_USER', 'storeall_user');
define('DB_PASS', 'storeall_password');
define('DB_CHARSET', 'utf8mb4');

// Application settings
define('APP_NAME', 'StoreAll.io');
define('APP_URL', 'http://localhost:8080');
define('APP_VERSION', '1.0.0');

// Security settings
define('SESSION_NAME', 'storeall_session');
define('SESSION_LIFETIME', 3600);
define('PASSWORD_COST', 12);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Timezone
date_default_timezone_set('UTC');

// Helper functions
function isProduction() {
    return ENVIRONMENT === 'production';
}

function isDevelopment() {
    return ENVIRONMENT === 'development';
}

function getAppUrl($path = '') {
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

function redirect($path) {
    header('Location: ' . getAppUrl($path));
    exit;
}

// CSRF protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_token() {
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>