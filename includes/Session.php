<?php
/**
 * Session.php - Session Management Class
 * Handles secure session operations and session data management
 */

class Session {
    private static $instance = null;
    private $started = false;
    
    private function __construct() {
        // Configure session settings
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        // Set session name
        session_name('STOREALL_SESSION');
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Start session if not already started
     */
    public function start() {
        if (!$this->started && session_status() === PHP_SESSION_NONE) {
            session_start();
            $this->started = true;
            
            // Regenerate session ID periodically for security
            if (!isset($_SESSION['last_regeneration'])) {
                $_SESSION['last_regeneration'] = time();
            } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
                $this->regenerate();
            }
        }
        return $this;
    }
    
    /**
     * Set session value
     */
    public function set($key, $value) {
        $this->start();
        $_SESSION[$key] = $value;
        return $this;
    }
    
    /**
     * Get session value
     */
    public function get($key, $default = null) {
        $this->start();
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if session key exists
     */
    public function has($key) {
        $this->start();
        return isset($_SESSION[$key]);
    }
    
    /**
     * Delete session key
     */
    public function delete($key) {
        $this->start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
        return $this;
    }
    
    /**
     * Get all session data
     */
    public function all() {
        $this->start();
        return $_SESSION;
    }
    
    /**
     * Clear all session data
     */
    public function clear() {
        $this->start();
        $_SESSION = [];
        return $this;
    }
    
    /**
     * Destroy session completely
     */
    public function destroy() {
        $this->start();
        session_destroy();
        $this->started = false;
        
        // Clear session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        return $this;
    }
    
    /**
     * Regenerate session ID
     */
    public function regenerate() {
        $this->start();
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
        return $this;
    }
    
    /**
     * Set flash message (temporary session data)
     */
    public function setFlash($key, $message) {
        $this->set("flash_$key", $message);
        return $this;
    }
    
    /**
     * Get flash message and remove it
     */
    public function getFlash($key, $default = null) {
        $message = $this->get("flash_$key", $default);
        $this->delete("flash_$key");
        return $message;
    }
    
    /**
     * Check if flash message exists
     */
    public function hasFlash($key) {
        return $this->has("flash_$key");
    }
    
    /**
     * Set CSRF token
     */
    public function setCsrfToken() {
        if (!$this->has('csrf_token')) {
            $token = bin2hex(random_bytes(32));
            $this->set('csrf_token', $token);
        }
        return $this->get('csrf_token');
    }
    
    /**
     * Verify CSRF token
     */
    public function verifyCsrfToken($token) {
        $sessionToken = $this->get('csrf_token');
        return $sessionToken && hash_equals($sessionToken, $token);
    }
    
    /**
     * Set user data in session
     */
    public function setUser($userData) {
        $this->set('user_id', $userData['id']);
        $this->set('user_role', $userData['role']);
        $this->set('user_email', $userData['email']);
        $this->set('user_username', $userData['username']);
        return $this;
    }
    
    /**
     * Get user ID from session
     */
    public function getUserId() {
        return $this->get('user_id');
    }
    
    /**
     * Get user role from session
     */
    public function getUserRole() {
        return $this->get('user_role');
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return $this->has('user_id');
    }
    
    /**
     * Logout user (clear user data)
     */
    public function logout() {
        $this->delete('user_id');
        $this->delete('user_role');
        $this->delete('user_email');
        $this->delete('user_username');
        return $this;
    }
    
    /**
     * Set session timeout
     */
    public function setTimeout($seconds) {
        $this->set('session_timeout', time() + $seconds);
        return $this;
    }
    
    /**
     * Check if session has expired
     */
    public function isExpired() {
        $timeout = $this->get('session_timeout');
        return $timeout && time() > $timeout;
    }
    
    /**
     * Extend session timeout
     */
    public function extend($seconds = 3600) {
        $this->setTimeout($seconds);
        return $this;
    }
    
    /**
     * Get session ID
     */
    public function getId() {
        $this->start();
        return session_id();
    }
    
    /**
     * Check if session is secure
     */
    public function isSecure() {
        return session_status() === PHP_SESSION_ACTIVE && 
               (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') &&
               ini_get('session.cookie_secure') == 1;
    }
    
    /**
     * Get session status
     */
    public function getStatus() {
        return session_status();
    }
    
    /**
     * Set session configuration
     */
    public function configure($options = []) {
        $defaults = [
            'cookie_lifetime' => 0,
            'cookie_path' => '/',
            'cookie_domain' => '',
            'cookie_secure' => 1,
            'cookie_httponly' => 1,
            'cookie_samesite' => 'Strict',
            'gc_maxlifetime' => 3600,
            'gc_probability' => 1,
            'gc_divisor' => 100
        ];
        
        $config = array_merge($defaults, $options);
        
        foreach ($config as $key => $value) {
            ini_set("session.$key", $value);
        }
        
        return $this;
    }
}