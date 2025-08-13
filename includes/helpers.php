<?php
/**
 * helpers.php - Helper Functions
 * Common utility functions used throughout the application
 */

/**
 * Generate asset URL for CSS, JS, images, etc.
 */
function asset($path) {
    $baseUrl = defined('BASE_URL') ? BASE_URL : '';
    return $baseUrl . '/' . ltrim($path, '/');
}

/**
 * Generate URL for internal pages
 */
function url($path = '') {
    $baseUrl = defined('BASE_URL') ? BASE_URL : '';
    return $baseUrl . '/' . ltrim($path, '/');
}

/**
 * Escape HTML output
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function csrf_token() {
    $session = Session::getInstance();
    return $session->setCsrfToken();
}

/**
 * Generate CSRF token field for forms
 */
function csrf_field() {
    return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
}

/**
 * Check if current user has role
 */
function has_role($role) {
    $auth = Auth::getInstance();
    return $auth->hasRole($role);
}

/**
 * Check if current user has any of the specified roles
 */
function has_any_role($roles) {
    $auth = Auth::getInstance();
    return $auth->hasAnyRole($roles);
}

/**
 * Get current user
 */
function current_user() {
    $auth = Auth::getInstance();
    return $auth->getCurrentUser();
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    $auth = Auth::getInstance();
    return $auth->isLoggedIn();
}

/**
 * Format currency
 */
function format_currency($amount, $currency = 'USD') {
    return '$' . number_format($amount, 2);
}

/**
 * Format date
 */
function format_date($date, $format = 'Y-m-d H:i:s') {
    if (is_string($date)) {
        $date = new DateTime($date);
    }
    return $date->format($format);
}

/**
 * Get flash message
 */
function flash($key, $default = null) {
    $session = Session::getInstance();
    return $session->getFlash($key, $default);
}

/**
 * Check if flash message exists
 */
function has_flash($key) {
    $session = Session::getInstance();
    return $session->hasFlash($key);
}

/**
 * Redirect to URL
 */
function redirect($url) {
    header('Location: ' . url($url));
    exit;
}

/**
 * Redirect back to previous page
 */
function redirect_back() {
    $referer = $_SERVER['HTTP_REFERER'] ?? '/';
    header('Location: ' . $referer);
    exit;
}

/**
 * Get request method
 */
function request_method() {
    return $_SERVER['REQUEST_METHOD'] ?? 'GET';
}

/**
 * Check if request is POST
 */
function is_post() {
    return request_method() === 'POST';
}

/**
 * Check if request is GET
 */
function is_get() {
    return request_method() === 'GET';
}

/**
 * Get request input
 */
function input($key, $default = null) {
    return $_REQUEST[$key] ?? $default;
}

/**
 * Get POST input
 */
function post($key, $default = null) {
    return $_POST[$key] ?? $default;
}

/**
 * Get GET input
 */
function get($key, $default = null) {
    return $_GET[$key] ?? $default;
}

/**
 * Validate email
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate random string
 */
function random_string($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Hash password
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Get file extension
 */
function get_file_extension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if file is image
 */
function is_image($filename) {
    $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    return in_array(get_file_extension($filename), $extensions);
}

/**
 * Sanitize filename
 */
function sanitize_filename($filename) {
    // Remove special characters
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    // Remove multiple dots
    $filename = preg_replace('/\.+/', '.', $filename);
    return $filename;
}

/**
 * Get file size in human readable format
 */
function format_file_size($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Truncate text
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}

/**
 * Generate slug from text
 */
function slugify($text) {
    // Convert to lowercase
    $text = strtolower($text);
    // Replace non-alphanumeric characters with hyphens
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    // Remove multiple hyphens
    $text = preg_replace('/-+/', '-', $text);
    // Remove leading and trailing hyphens
    return trim($text, '-');
}

/**
 * Get current URL
 */
function current_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    return $protocol . '://' . $host . $uri;
}

/**
 * Get base URL
 */
function base_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    return $protocol . '://' . $host;
}

/**
 * Log message
 */
function log_message($level, $message, $context = []) {
    $logger = Logger::getInstance();
    
    switch (strtolower($level)) {
        case 'debug':
            $logger->debug($message, $context);
            break;
        case 'info':
            $logger->info($message, $context);
            break;
        case 'warning':
            $logger->warning($message, $context);
            break;
        case 'error':
            $logger->error($message, $context);
            break;
        case 'critical':
            $logger->critical($message, $context);
            break;
    }
}
