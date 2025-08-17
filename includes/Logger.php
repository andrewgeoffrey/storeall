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
            $this->db->insert('application_logs', [
                'level' => $logData['level'],
                'message' => $logData['message'],
                'context' => $logData['context'],
                'timestamp' => $logData['timestamp'],
                'ip_address' => $logData['ip_address'],
                'user_agent' => $logData['user_agent'],
                'user_id' => $logData['user_id'],
                'request_uri' => $logData['request_uri'],
                'request_method' => $logData['request_method'],
                'created_at' => $logData['created_at']
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
    
    /**
     * Log client-side error
     */
    public function logClientError($errorType, $errorMessage, $additionalData = []) {
        try {
            $this->db->insert('client_errors', [
                'error_type' => $errorType,
                'error_message' => $errorMessage,
                'error_stack' => $additionalData['stack'] ?? null,
                'error_file' => $additionalData['file'] ?? null,
                'error_line' => $additionalData['line'] ?? null,
                'error_column' => $additionalData['column'] ?? null,
                'user_id' => $this->getCurrentUserId(),
                'session_id' => session_id(),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'page_url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'referrer_url' => $_SERVER['HTTP_REFERER'] ?? null,
                'browser_info' => json_encode($this->getBrowserInfo()),
                'additional_context' => json_encode($additionalData),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Failed to log client error: " . $e->getMessage());
        }
    }
    
    /**
     * Log database error
     */
    public function logDatabaseError($errorType, $errorMessage, $additionalData = []) {
        try {
            $this->db->insert('database_errors', [
                'error_type' => $errorType,
                'error_message' => $errorMessage,
                'error_code' => $additionalData['code'] ?? null,
                'sql_query' => $additionalData['sql'] ?? null,
                'query_params' => json_encode($additionalData['params'] ?? []),
                'table_name' => $additionalData['table'] ?? null,
                'operation_type' => $additionalData['operation'] ?? null,
                'execution_time_ms' => $additionalData['execution_time'] ?? null,
                'user_id' => $this->getCurrentUserId(),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'stack_trace' => $additionalData['stack_trace'] ?? null,
                'additional_context' => json_encode($additionalData),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Failed to log database error: " . $e->getMessage());
        }
    }
    
    /**
     * Log performance metric
     */
    public function logPerformanceMetric($metricType, $operationName, $durationMs, $additionalData = []) {
        try {
            $this->db->insert('performance_metrics', [
                'metric_type' => $metricType,
                'operation_name' => $operationName,
                'duration_ms' => $durationMs,
                'memory_usage_mb' => $additionalData['memory'] ?? null,
                'cpu_usage_percent' => $additionalData['cpu'] ?? null,
                'user_id' => $this->getCurrentUserId(),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'additional_data' => json_encode($additionalData),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Failed to log performance metric: " . $e->getMessage());
        }
    }
    
    /**
     * Log API request
     */
    public function logApiRequest($endpoint, $method, $statusCode, $responseTimeMs, $additionalData = []) {
        try {
            $this->db->insert('api_requests', [
                'endpoint' => $endpoint,
                'method' => $method,
                'status_code' => $statusCode,
                'response_time_ms' => $responseTimeMs,
                'request_size_bytes' => $additionalData['request_size'] ?? null,
                'response_size_bytes' => $additionalData['response_size'] ?? null,
                'user_id' => $this->getCurrentUserId(),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'request_headers' => json_encode($additionalData['headers'] ?? []),
                'request_body' => json_encode($additionalData['request_body'] ?? []),
                'response_body_preview' => $additionalData['response_preview'] ?? null,
                'error_message' => $additionalData['error'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Failed to log API request: " . $e->getMessage());
        }
    }
    
    /**
     * Log email event
     */
    public function logEmailEvent($emailType, $recipientEmail, $subject, $status, $additionalData = []) {
        try {
            $this->db->insert('email_logs', [
                'email_type' => $emailType,
                'recipient_email' => $recipientEmail,
                'subject' => $subject,
                'status' => $status,
                'error_message' => $additionalData['error'] ?? null,
                'sent_at' => $additionalData['sent_at'] ?? null,
                'delivered_at' => $additionalData['delivered_at'] ?? null,
                'opened_at' => $additionalData['opened_at'] ?? null,
                'clicked_at' => $additionalData['clicked_at'] ?? null,
                'user_id' => $this->getCurrentUserId(),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'additional_data' => json_encode($additionalData),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Failed to log email event: " . $e->getMessage());
        }
    }
    
    /**
     * Log verification event
     */
    public function logVerificationEvent($verificationType, $userId, $action, $status, $additionalData = []) {
        try {
            $this->db->insert('verification_logs', [
                'verification_type' => $verificationType,
                'user_id' => $userId,
                'token_id' => $additionalData['token_id'] ?? null,
                'action' => $action,
                'status' => $status,
                'error_message' => $additionalData['error'] ?? null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'additional_context' => json_encode($additionalData),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Failed to log verification event: " . $e->getMessage());
        }
    }
    
    /**
     * Get browser information
     */
    private function getBrowserInfo() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        return [
            'user_agent' => $userAgent,
            'browser' => $this->detectBrowser($userAgent),
            'version' => $this->detectVersion($userAgent),
            'platform' => $this->detectPlatform($userAgent),
            'mobile' => $this->isMobile($userAgent)
        ];
    }
    
    /**
     * Detect browser from user agent
     */
    private function detectBrowser($userAgent) {
        if (preg_match('/Chrome/i', $userAgent)) return 'Chrome';
        if (preg_match('/Firefox/i', $userAgent)) return 'Firefox';
        if (preg_match('/Safari/i', $userAgent)) return 'Safari';
        if (preg_match('/Edge/i', $userAgent)) return 'Edge';
        if (preg_match('/MSIE|Trident/i', $userAgent)) return 'Internet Explorer';
        return 'Unknown';
    }
    
    /**
     * Detect browser version
     */
    private function detectVersion($userAgent) {
        if (preg_match('/(Chrome|Firefox|Safari|Edge)\/(\d+)/i', $userAgent, $matches)) {
            return $matches[2];
        }
        return 'Unknown';
    }
    
    /**
     * Detect platform
     */
    private function detectPlatform($userAgent) {
        if (preg_match('/Windows/i', $userAgent)) return 'Windows';
        if (preg_match('/Mac/i', $userAgent)) return 'Mac';
        if (preg_match('/Linux/i', $userAgent)) return 'Linux';
        if (preg_match('/Android/i', $userAgent)) return 'Android';
        if (preg_match('/iOS/i', $userAgent)) return 'iOS';
        return 'Unknown';
    }
    
    /**
     * Check if mobile device
     */
    private function isMobile($userAgent) {
        return preg_match('/(Android|iPhone|iPad|Mobile)/i', $userAgent);
    }
}