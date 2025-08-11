<?php
/**
 * ErrorHandler Class
 * Handles error logging and tracking for both client-side and server-side errors
 */

class ErrorHandler {
    private static $instance = null;
    private $db;
    
    public static function init() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        // Set error handlers
        set_error_handler([self::$instance, 'handleError']);
        set_exception_handler([self::$instance, 'handleException']);
        register_shutdown_function([self::$instance, 'handleFatalError']);
        
        return self::$instance;
    }
    
    private function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Handle PHP errors
     */
    public function handleError($errno, $errstr, $errfile, $errline) {
        $errorData = [
            'type' => 'PHP_ERROR',
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'error_level' => $errno,
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_id' => $_SESSION['user_id'] ?? null,
            'session_id' => session_id(),
            'timestamp' => date('Y-m-d H:i:s'),
            'stack_trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10)
        ];
        
        $this->logError($errorData);
        
        // Don't display errors in production
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Handle exceptions
     */
    public function handleException($exception) {
        $errorData = [
            'type' => 'EXCEPTION',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_id' => $_SESSION['user_id'] ?? null,
            'session_id' => session_id(),
            'timestamp' => date('Y-m-d H:i:s'),
            'stack_trace' => $exception->getTraceAsString()
        ];
        
        $this->logError($errorData);
    }
    
    /**
     * Handle fatal errors
     */
    public function handleFatalError() {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $errorData = [
                'type' => 'FATAL_ERROR',
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'url' => $_SERVER['REQUEST_URI'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_id' => $_SESSION['user_id'] ?? null,
                'session_id' => session_id(),
                'timestamp' => date('Y-m-d H:i:s'),
                'stack_trace' => 'Fatal error - no stack trace available'
            ];
            
            $this->logError($errorData);
        }
    }
    
    /**
     * Log error to database
     */
    private function logError($errorData) {
        try {
            $sql = "INSERT INTO error_logs (
                error_type, message, file_path, line_number, url, 
                user_agent, ip_address, user_id, session_id, 
                timestamp, stack_trace, metadata
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $errorData['type'],
                $errorData['message'],
                $errorData['file'],
                $errorData['line'],
                $errorData['url'],
                $errorData['user_agent'],
                $errorData['ip_address'],
                $errorData['user_id'],
                $errorData['session_id'],
                $errorData['timestamp'],
                $errorData['stack_trace'],
                json_encode($errorData)
            ];
            
            $this->db->query($sql, $params);
            
        } catch (Exception $e) {
            // Fallback to file logging if database fails
            error_log("ErrorHandler failed to log to database: " . $e->getMessage());
            error_log("Original error: " . json_encode($errorData));
        }
    }
    
    /**
     * Log client-side JavaScript errors
     */
    public static function logClientError($errorData) {
        $instance = self::getInstance();
        
        $data = [
            'type' => 'CLIENT_ERROR',
            'message' => $errorData['message'] ?? '',
            'file' => $errorData['file'] ?? '',
            'line' => $errorData['line'] ?? '',
            'column' => $errorData['column'] ?? '',
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_id' => $_SESSION['user_id'] ?? null,
            'session_id' => session_id(),
            'timestamp' => date('Y-m-d H:i:s'),
            'stack_trace' => $errorData['stack'] ?? '',
            'metadata' => json_encode($errorData)
        ];
        
        $instance->logError($data);
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
?>
