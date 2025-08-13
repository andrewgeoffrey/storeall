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
        $this->db = Database::getInstance();
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
            $stmt = $this->db->prepare("
                SELECT id, username, email, password, role, status, created_at 
                FROM users 
                WHERE email = ? AND status = 'active'
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
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
                Logger::getInstance()->info('User logged in successfully', [
                    'user_id' => $user['id'],
                    'email' => $user['email'],
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);
                
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            Logger::getInstance()->error('Login error', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
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
        if ($userId) {
            Logger::getInstance()->info('User logged out', [
                'user_id' => $userId,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        }
        
        return true;
    }
    
    /**
     * Register new user
     */
    public function register($userData) {
        try {
            // Validate required fields
            $required = ['username', 'email', 'password', 'role'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }
            
            // Check if email already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$userData['email']]);
            if ($stmt->fetch()) {
                throw new Exception("Email already registered");
            }
            
            // Hash password
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password, role, status, created_at) 
                VALUES (?, ?, ?, ?, 'pending', NOW())
            ");
            
            $result = $stmt->execute([
                $userData['username'],
                $userData['email'],
                $hashedPassword,
                $userData['role']
            ]);
            
            if ($result) {
                $userId = $this->db->lastInsertId();
                
                Logger::getInstance()->info('User registered', [
                    'user_id' => $userId,
                    'email' => $userData['email'],
                    'role' => $userData['role']
                ]);
                
                return $userId;
            }
            
            return false;
        } catch (Exception $e) {
            Logger::getInstance()->error('Registration error', [
                'email' => $userData['email'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Get current logged in user
     */
    public function getCurrentUser() {
        if ($this->currentUser !== null) {
            return $this->currentUser;
        }
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['user_id'])) {
            try {
                $stmt = $this->db->prepare("
                    SELECT id, username, email, role, status, created_at 
                    FROM users 
                    WHERE id = ? AND status = 'active'
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $this->currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
                
                return $this->currentUser;
            } catch (Exception $e) {
                Logger::getInstance()->error('Error getting current user', [
                    'user_id' => $_SESSION['user_id'],
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        }
        
        return null;
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
     * Verify email address
     */
    public function verifyEmail($token) {
        try {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET status = 'active', email_verified_at = NOW() 
                WHERE email_verification_token = ? AND status = 'pending'
            ");
            
            $result = $stmt->execute([$token]);
            
            if ($result && $stmt->rowCount() > 0) {
                Logger::getInstance()->info('Email verified', ['token' => $token]);
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            Logger::getInstance()->error('Email verification error', [
                'token' => $token,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Reset password
     */
    public function resetPassword($email) {
        try {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $stmt = $this->db->prepare("
                UPDATE users 
                SET password_reset_token = ?, password_reset_expires = ? 
                WHERE email = ? AND status = 'active'
            ");
            
            $result = $stmt->execute([$token, $expires, $email]);
            
            if ($result && $stmt->rowCount() > 0) {
                // Send reset email (implement email sending logic)
                Logger::getInstance()->info('Password reset requested', ['email' => $email]);
                return $token;
            }
            
            return false;
        } catch (Exception $e) {
            Logger::getInstance()->error('Password reset error', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Change password
     */
    public function changePassword($userId, $oldPassword, $newPassword) {
        try {
            // Verify old password
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($oldPassword, $user['password'])) {
                return false;
            }
            
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("
                UPDATE users 
                SET password = ?, password_reset_token = NULL, password_reset_expires = NULL 
                WHERE id = ?
            ");
            
            $result = $stmt->execute([$hashedPassword, $userId]);
            
            if ($result) {
                Logger::getInstance()->info('Password changed', ['user_id' => $userId]);
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            Logger::getInstance()->error('Password change error', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
