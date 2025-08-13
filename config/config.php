<?php
/**
 * StoreAll Configuration File
 * Main configuration settings for the application
 */

// Environment setting
define('ENVIRONMENT', 'production'); // Change to 'production' for live site

// Database configuration
define('DB_HOST', 'localhost'); // Your database host
define('DB_NAME', 'n300265_storeall'); // Your database name
define('DB_USER', 'n300265_jdodds_storeall'); // Your database username
define('DB_PASS', '33A3E8D0A6838F95ABB848F682F6BF49BF5BA5D85478A31451D9F73435498EB4'); // Your database password
define('DB_CHARSET', 'utf8mb4');

// Application settings
define('APP_NAME', 'StoreAll');
define('APP_URL', 'http://storeall.andrewgeoffrey.net');
define('APP_VERSION', '1.0.0');

// Security settings
define('SESSION_NAME', 'storeall_session');
define('SESSION_LIFETIME', 3600); // 1 hour
define('PASSWORD_COST', 12); // bcrypt cost

// Error reporting
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

// Timezone
date_default_timezone_set('UTC');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

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

function asset($path) {
    return getAppUrl('includes/' . ltrim($path, '/'));
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