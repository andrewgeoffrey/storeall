<?php
/**
 * ErrorHandler.php - Error Handling Class
 * Handles application errors and exceptions
 */

class ErrorHandler {
    private static $instance = null;
    private $logFile;
    
    private function __construct() {
        $this->logFile = 'logs/errors.log';
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialize error handling
     */
    public static function init() {
        // Set error handlers
        set_error_handler([self::getInstance(), 'handleError']);
        set_exception_handler([self::getInstance(), 'handleException']);
        register_shutdown_function([self::getInstance(), 'handleFatalError']);
    }
    
    /**
     * Handle PHP errors
     */
    public function handleError($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return false; // Don't handle suppressed errors
        }
        
        $errorData = [
            'type' => 'PHP_ERROR',
            'level' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_id' => $_SESSION['user_id'] ?? null,
            'session_id' => session_id(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->logError($errorData);
        
        return true;
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
     * Log error to file (simplified to prevent memory issues)
     */
    private function logError($errorData) {
        try {
            $logEntry = sprintf(
                "[%s] %s: %s in %s on line %d\n",
                $errorData['timestamp'],
                $errorData['type'],
                $errorData['message'],
                $errorData['file'],
                $errorData['line']
            );
            
            file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
            
        } catch (Exception $e) {
            // Fallback to PHP's built-in error logging
            error_log("ErrorHandler failed to log to file: " . $e->getMessage());
            error_log("Original error: " . $errorData['message']);
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
            'stack_trace' => $errorData['stack'] ?? ''
        ];
        
        $instance->logError($data);
    }
}
?>
