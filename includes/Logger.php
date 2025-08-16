<?php
/**
 * Logger.php - Logging Class
 * Handles application logging with different levels and database storage
 */

class Logger {
    private static $instance = null;
    private $db;
    private $logLevel;
    private $logFile;
    
    const LEVEL_DEBUG = 0;
    const LEVEL_INFO = 1;
    const LEVEL_WARNING = 2;
    const LEVEL_ERROR = 3;
    const LEVEL_CRITICAL = 4;
    
    private function __construct() {
        $this->db = Database::getInstance();
        $this->logLevel = defined('LOG_LEVEL') ? LOG_LEVEL : self::LEVEL_INFO;
        $this->logFile = defined('LOG_FILE') ? LOG_FILE : 'logs/app.log';
        
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
     * Log debug message
     */
    public function debug($message, $context = []) {
        $this->log(self::LEVEL_DEBUG, $message, $context);
    }
    
    /**
     * Log info message
     */
    public function info($message, $context = []) {
        $this->log(self::LEVEL_INFO, $message, $context);
    }
    
    /**
     * Log warning message
     */
    public function warning($message, $context = []) {
        $this->log(self::LEVEL_WARNING, $message, $context);
    }
    
    /**
     * Log error message
     */
    public function error($message, $context = []) {
        $this->log(self::LEVEL_ERROR, $message, $context);
    }
    
    /**
     * Log critical message
     */
    public function critical($message, $context = []) {
        $this->log(self::LEVEL_CRITICAL, $message, $context);
    }
    
    /**
     * Main logging method
     */
    private function log($level, $message, $context = []) {
        if ($level < $this->logLevel) {
            return;
        }
        
        $levelName = $this->getLevelName($level);
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $userId = $this->getCurrentUserId();
        $requestUri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'unknown';
        
        // Prepare log data
        $logData = [
            'level' => $levelName,
            'message' => $message,
            'context' => json_encode($context),
            'timestamp' => $timestamp,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'user_id' => $userId,
            'request_uri' => $requestUri,
            'request_method' => $requestMethod,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Log to database
        $this->logToDatabase($logData);
        
        // Log to file
        $this->logToFile($levelName, $message, $context, $timestamp, $ip, $userId);
        
        // Send critical errors to admin
        if ($level === self::LEVEL_CRITICAL) {
            $this->sendCriticalAlert($message, $context);
        }
    }
    
    /**
     * Log to database
     */
    private function logToDatabase($logData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO application_logs 
                (level, message, context, timestamp, ip_address, user_agent, user_id, request_uri, request_method, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $logData['level'],
                $logData['message'],
                $logData['context'],
                $logData['timestamp'],
                $logData['ip_address'],
                $logData['user_agent'],
                $logData['user_id'],
                $logData['request_uri'],
                $logData['request_method'],
                $logData['created_at']
            ]);
        } catch (Exception $e) {
            // Fallback to file logging if database fails
            error_log("Database logging failed: " . $e->getMessage());
        }
    }
    
    /**
     * Log to file
     */
    private function logToFile($level, $message, $context, $timestamp, $ip, $userId) {
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $userIdStr = $userId ? " [User: $userId]" : '';
        $logEntry = "[$timestamp] [$level] [$ip]$userIdStr: $message$contextStr" . PHP_EOL;
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Get level name from level number
     */
    private function getLevelName($level) {
        $levels = [
            self::LEVEL_DEBUG => 'DEBUG',
            self::LEVEL_INFO => 'INFO',
            self::LEVEL_WARNING => 'WARNING',
            self::LEVEL_ERROR => 'ERROR',
            self::LEVEL_CRITICAL => 'CRITICAL'
        ];
        
        return $levels[$level] ?? 'UNKNOWN';
    }
    
    /**
     * Get current user ID from session
     */
    private function getCurrentUserId() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Send critical alert to admin
     */
    private function sendCriticalAlert($message, $context) {
        // Implementation for sending critical alerts
        // This could be email, SMS, or other notification methods
        if (defined('ADMIN_EMAIL')) {
            $subject = 'CRITICAL ERROR - StoreAll.io';
            $body = "Critical error occurred:\n\n";
            $body .= "Message: $message\n";
            $body .= "Context: " . json_encode($context, JSON_PRETTY_PRINT) . "\n";
            $body .= "Time: " . date('Y-m-d H:i:s') . "\n";
            $body .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
            $body .= "User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') . "\n";
            
            // Use PHP mail function or implement email service
            mail(ADMIN_EMAIL, $subject, $body);
        }
    }
    
    /**
     * Get logs from database with filters
     */
    public function getLogs($filters = [], $limit = 100, $offset = 0) {
        try {
            $where = [];
            $params = [];
            
            if (!empty($filters['level'])) {
                $where[] = "level = ?";
                $params[] = $filters['level'];
            }
            
            if (!empty($filters['user_id'])) {
                $where[] = "user_id = ?";
                $params[] = $filters['user_id'];
            }
            
            if (!empty($filters['date_from'])) {
                $where[] = "created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $where[] = "created_at <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['search'])) {
                $where[] = "(message LIKE ? OR context LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $sql = "
                SELECT * FROM application_logs 
                $whereClause 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->error('Failed to retrieve logs', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Get log statistics
     */
    public function getLogStats($days = 7) {
        try {
            $sql = "
                SELECT 
                    level,
                    COUNT(*) as count,
                    DATE(created_at) as date
                FROM application_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY level, DATE(created_at)
                ORDER BY date DESC, level
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$days]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->error('Failed to retrieve log statistics', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Clean old logs
     */
    public function cleanOldLogs($days = 30) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM application_logs 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            
            $result = $stmt->execute([$days]);
            
            if ($result) {
                $deletedCount = $stmt->rowCount();
                $this->info("Cleaned old logs", ['deleted_count' => $deletedCount, 'days' => $days]);
                return $deletedCount;
            }
            
            return 0;
        } catch (Exception $e) {
            $this->error('Failed to clean old logs', ['error' => $e->getMessage()]);
            return 0;
        }
    }
    
    /**
     * Log performance metrics
     */
    public function logPerformance($operation, $duration, $context = []) {
        $context['duration'] = $duration;
        $context['operation'] = $operation;
        
        if ($duration > 1000) { // More than 1 second
            $this->warning("Slow operation detected", $context);
        } else {
            $this->debug("Performance metric", $context);
        }
    }
    
    /**
     * Log database query
     */
    public function logQuery($sql, $params = [], $duration = null) {
        $context = [
            'sql' => $sql,
            'params' => $params,
            'duration' => $duration
        ];
        
        if ($duration && $duration > 100) { // More than 100ms
            $this->warning("Slow database query", $context);
        } else {
            $this->debug("Database query", $context);
        }
    }
    
    /**
     * Log user action
     */
    public function logUserAction($action, $details = []) {
        $userId = $this->getCurrentUserId();
        $context = array_merge($details, ['action' => $action]);
        
        $this->info("User action: $action", $context);
    }
    
    /**
     * Log security event
     */
    public function logSecurityEvent($event, $details = []) {
        $context = array_merge($details, ['security_event' => $event]);
        
        $this->warning("Security event: $event", $context);
    }
}