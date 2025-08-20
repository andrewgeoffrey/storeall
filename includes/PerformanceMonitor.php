<?php
/**
 * PerformanceMonitor Class
 * Tracks performance metrics for queries and page loads
 */

class PerformanceMonitor {
    private static $instance = null;
    private $db;
    private $startTime;
    private $queries = [];
    private $pageLoadStart;
    
    public static function start() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        $instance = self::$instance;
        $instance->pageLoadStart = microtime(true);
        $instance->startTime = microtime(true);
        
        return $instance;
    }
    
    private function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Start monitoring a database query
     */
    public static function startQuery($sql, $params = []) {
        $instance = self::getInstance();
        
        $queryId = uniqid('query_');
        $instance->queries[$queryId] = [
            'sql' => $sql,
            'params' => $params,
            'start_time' => microtime(true),
            'file' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['file'] ?? '',
            'line' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['line'] ?? ''
        ];
        
        return $queryId;
    }
    
    /**
     * End monitoring a database query
     */
    public static function endQuery($queryId) {
        $instance = self::getInstance();
        
        if (isset($instance->queries[$queryId])) {
            $instance->queries[$queryId]['end_time'] = microtime(true);
            $instance->queries[$queryId]['duration'] = 
                $instance->queries[$queryId]['end_time'] - $instance->queries[$queryId]['start_time'];
            
            // Log slow queries (over 1 second)
            if ($instance->queries[$queryId]['duration'] > 1.0) {
                $instance->logSlowQuery($instance->queries[$queryId]);
            }
        }
    }
    
    /**
     * End performance monitoring and log results
     */
    public static function end() {
        $instance = self::getInstance();
        
        $pageLoadTime = microtime(true) - $instance->pageLoadStart;
        $totalQueryTime = 0;
        
        foreach ($instance->queries as $query) {
            if (isset($query['duration'])) {
                $totalQueryTime += $query['duration'];
            }
        }
        
        // Log performance metrics
        $instance->logPerformanceMetrics([
            'page_load_time' => $pageLoadTime,
            'total_query_time' => $totalQueryTime,
            'query_count' => count($instance->queries),
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_id' => $_SESSION['user_id'] ?? null,
            'session_id' => session_id(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Log slow queries to database
     */
    private function logSlowQuery($queryData) {
        try {
            $sql = "INSERT INTO performance_logs (
                type, duration, sql_query, parameters, file_path, 
                line_number, url, user_agent, ip_address, user_id, 
                session_id, timestamp, metadata
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                'SLOW_QUERY',
                $queryData['duration'],
                $queryData['sql'],
                json_encode($queryData['params']),
                $queryData['file'],
                $queryData['line'],
                $_SERVER['REQUEST_URI'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SESSION['user_id'] ?? null,
                session_id(),
                date('Y-m-d H:i:s'),
                json_encode($queryData)
            ];
            
            $this->db->query($sql, $params);
            
        } catch (Exception $e) {
            error_log("PerformanceMonitor failed to log slow query: " . $e->getMessage());
        }
    }
    
    /**
     * Log performance metrics to database
     */
    private function logPerformanceMetrics($metrics) {
        try {
            $sql = "INSERT INTO performance_logs (
                type, duration, page_load_time, query_count, total_query_time,
                url, user_agent, ip_address, user_id, session_id, 
                timestamp, metadata
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                'PAGE_LOAD',
                $metrics['page_load_time'],
                $metrics['page_load_time'],
                $metrics['query_count'],
                $metrics['total_query_time'],
                $metrics['url'],
                $metrics['user_agent'],
                $metrics['ip_address'],
                $metrics['user_id'],
                $metrics['session_id'],
                $metrics['timestamp'],
                json_encode($metrics)
            ];
            
            $this->db->query($sql, $params);
            
        } catch (Exception $e) {
            error_log("PerformanceMonitor failed to log metrics: " . $e->getMessage());
        }
    }
    
    /**
     * Get performance statistics
     */
    public static function getStats() {
        $instance = self::getInstance();
        
        return [
            'page_load_time' => microtime(true) - $instance->pageLoadStart,
            'query_count' => count($instance->queries),
            'queries' => $instance->queries
        ];
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




