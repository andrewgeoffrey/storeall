<?php
/**
 * Auth.php - Authentication and Authorization Class
 * Handles user login, logout, registration, and permission checking
 */

class Auth {
    private static $instance = null;
    private $db;
    private $currentUser = null;
    
    private function __construct() {
        if (class_exists('Database')) {
            $this->db = Database::getInstance();
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Login user with email and password
     */
    public function login($email, $password) {
        try {
            if (!$this->db) {
                return false;
            }
            
            $user = $this->db->fetch("
                SELECT id, username, email, password, role, status, created_at 
                FROM users 
                WHERE email = ? AND status = 'active'
            ", [$email]);
            
            if ($user && password_verify($password, $user['password'])) {
                // Start session and store user data
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_email'] = $user['email'];
                
                $this->currentUser = $user;
                
                // Log successful login
                if (class_exists('Logger')) {
                    Logger::getInstance()->info('User logged in successfully', [
                        'user_id' => $user['id'],
                        'email' => $user['email'],
                        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                    ]);
                }
                
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            if (class_exists('Logger')) {
                Logger::getInstance()->error('Login error', [
                    'email' => $email,
                    'error' => $e->getMessage()
                ]);
            }
            return false;
        }
    }
    
    /**
     * Logout current user
     */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = $_SESSION['user_id'] ?? null;
        
        // Clear session
        session_destroy();
        $this->currentUser = null;
        
        // Log logout
        if ($userId && class_exists('Logger')) {
            Logger::getInstance()->info('User logged out', [
                'user_id' => $userId,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        }
        
        return true;
    }
    
    /**
     * Get current user
     */
    public function getCurrentUser() {
        if ($this->currentUser) {
            return $this->currentUser;
        }
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId || !$this->db) {
            return null;
        }
        
        try {
            $this->currentUser = $this->db->fetch("
                SELECT id, username, email, role, status, created_at 
                FROM users 
                WHERE id = ? AND status = 'active'
            ", [$userId]);
            
            return $this->currentUser;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return $this->getCurrentUser() !== null;
    }
    
    /**
     * Check if user has specific role
     */
    public function hasRole($role) {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === $role;
    }
    
    /**
     * Check if user has any of the specified roles
     */
    public function hasAnyRole($roles) {
        $user = $this->getCurrentUser();
        return $user && in_array($user['role'], (array)$roles);
    }
    
    /**
     * Register new user
     */
    public function register($userData) {
        try {
            if (!$this->db) {
                throw new Exception("Database not available");
            }
            
            // Validate required fields
            $required = ['username', 'email', 'password', 'role'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }
            
            // Check if email already exists
            $existingUser = $this->db->fetch("SELECT id FROM users WHERE email = ?", [$userData['email']]);
            if ($existingUser) {
                throw new Exception("Email already registered");
            }
            
            // Hash password
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            // Insert new user
            $userId = $this->db->insert('users', [
                'username' => $userData['username'],
                'email' => $userData['email'],
                'password' => $hashedPassword,
                'role' => $userData['role'],
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            if (class_exists('Logger')) {
                Logger::getInstance()->info('User registered', [
                    'user_id' => $userId,
                    'email' => $userData['email'],
                    'role' => $userData['role']
                ]);
            }
            
            return $userId;
        } catch (Exception $e) {
            if (class_exists('Logger')) {
                Logger::getInstance()->error('Registration error', [
                    'email' => $userData['email'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
            }
            throw $e;
        }
    }
}
?>


