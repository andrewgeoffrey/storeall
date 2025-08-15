<?php
/**
 * helpers.php - Helper Functions
 * Common utility functions used throughout the application
 */

/**
 * Generate asset URL for CSS, JS, images, etc.
 */
if (!function_exists('asset')) {
    function asset($path) {
        return getAppUrl('includes/' . ltrim($path, '/'));
    }
}

/**
 * Generate URL for internal pages
 */
if (!function_exists('url')) {
    function url($path = '') {
        return getAppUrl($path);
    }
}

/**
 * Escape HTML output
 */
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Generate CSRF token
 */
if (!function_exists('csrf_token')) {
    function csrf_token() {
        return $_SESSION['csrf_token'] ?? '';
    }
}

/**
 * Generate CSRF token field for forms
 */
if (!function_exists('csrf_field')) {
    function csrf_field() {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

/**
 * Check if current user has role
 */
if (!function_exists('has_role')) {
    function has_role($role) {
        if (class_exists('Auth')) {
            $auth = Auth::getInstance();
            return $auth->hasRole($role);
        }
        return false;
    }
}

/**
 * Check if current user has any of the specified roles
 */
if (!function_exists('has_any_role')) {
    function has_any_role($roles) {
        if (class_exists('Auth')) {
            $auth = Auth::getInstance();
            return $auth->hasAnyRole($roles);
        }
        return false;
    }
}

/**
 * Get current user
 */
if (!function_exists('current_user')) {
    function current_user() {
        if (class_exists('Auth')) {
            $auth = Auth::getInstance();
            return $auth->getCurrentUser();
        }
        return null;
    }
}

/**
 * Check if user is logged in
 */
if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        if (class_exists('Auth')) {
            $auth = Auth::getInstance();
            return $auth->isLoggedIn();
        }
        return false;
    }
}

/**
 * Format currency
 */
if (!function_exists('format_currency')) {
    function format_currency($amount, $currency = 'USD') {
        return '$' . number_format($amount, 2);
    }
}

/**
 * Format date
 */
if (!function_exists('format_date')) {
    function format_date($date, $format = 'Y-m-d H:i:s') {
        if (is_string($date)) {
            $date = new DateTime($date);
        }
        return $date->format($format);
    }
}

/**
 * Get flash message
 */
if (!function_exists('flash')) {
    function flash($key, $default = null) {
        if (class_exists('Session')) {
            $session = Session::getInstance();
            return $session->getFlash($key, $default);
        }
        return $default;
    }
}

/**
 * Check if flash message exists
 */
if (!function_exists('has_flash')) {
    function has_flash($key) {
        if (class_exists('Session')) {
            $session = Session::getInstance();
            return $session->hasFlash($key);
        }
        return false;
    }
}

/**
 * Redirect to URL
 */
if (!function_exists('redirect')) {
    function redirect($url) {
        header('Location: ' . getAppUrl($url));
        exit;
    }
}

/**
 * Redirect back to previous page
 */
if (!function_exists('redirect_back')) {
    function redirect_back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header('Location: ' . $referer);
        exit;
    }
}

/**
 * Get request method
 */
if (!function_exists('request_method')) {
    function request_method() {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }
}

/**
 * Check if request is POST
 */
if (!function_exists('is_post')) {
    function is_post() {
        return request_method() === 'POST';
    }
}

/**
 * Check if request is GET
 */
if (!function_exists('is_get')) {
    function is_get() {
        return request_method() === 'GET';
    }
}

/**
 * Get request input
 */
if (!function_exists('input')) {
    function input($key, $default = null) {
        return $_REQUEST[$key] ?? $default;
    }
}

/**
 * Get POST input
 */
if (!function_exists('post')) {
    function post($key, $default = null) {
        return $_POST[$key] ?? $default;
    }
}

/**
 * Get GET input
 */
if (!function_exists('get')) {
    function get($key, $default = null) {
        return $_GET[$key] ?? $default;
    }
}

/**
 * Validate email
 */
if (!function_exists('is_valid_email')) {
    function is_valid_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

/**
 * Generate random string
 */
if (!function_exists('random_string')) {
    function random_string($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
}

/**
 * Hash password
 */
if (!function_exists('hash_password')) {
    function hash_password($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}

/**
 * Verify password
 */
if (!function_exists('verify_password')) {
    function verify_password($password, $hash) {
        return password_verify($password, $hash);
    }
}

/**
 * Get file extension
 */
if (!function_exists('get_file_extension')) {
    function get_file_extension($filename) {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
}

/**
 * Check if file is image
 */
if (!function_exists('is_image')) {
    function is_image($filename) {
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        return in_array(get_file_extension($filename), $extensions);
    }
}

/**
 * Sanitize filename
 */
if (!function_exists('sanitize_filename')) {
    function sanitize_filename($filename) {
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        // Remove multiple dots
        $filename = preg_replace('/\.+/', '.', $filename);
        return $filename;
    }
}

/**
 * Get file size in human readable format
 */
if (!function_exists('format_file_size')) {
    function format_file_size($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

/**
 * Truncate text
 */
if (!function_exists('truncate')) {
    function truncate($text, $length = 100, $suffix = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . $suffix;
    }
}

/**
 * Generate slug from text
 */
if (!function_exists('slugify')) {
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
}

/**
 * Get current URL
 */
if (!function_exists('current_url')) {
    function current_url() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return $protocol . '://' . $host . $uri;
    }
}

/**
 * Get base URL
 */
if (!function_exists('base_url')) {
    function base_url() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        return $protocol . '://' . $host;
    }
}

/**
 * Log message
 */
if (!function_exists('log_message')) {
    function log_message($level, $message, $context = []) {
        if (class_exists('Logger')) {
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
    }
}
?>



