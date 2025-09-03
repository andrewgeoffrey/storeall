<?php
// Set content type to JSON
header('Content-Type: application/json');

// Enable CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Load required files
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Logger.php';

// Get the action from the request
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_system_health':
            $response = getSystemHealth();
            break;
            
        case 'get_performance_metrics':
            $response = getPerformanceMetrics();
            break;
            
        case 'get_error_logs':
            $response = getErrorLogs();
            break;
            
        case 'log_client_error':
            $response = logClientError($_POST);
            break;
            
        case 'log_performance_metric':
            $response = logPerformanceMetric($_POST);
            break;
            
        case 'get_database_status':
            $response = getDatabaseStatus();
            break;
            
        case 'get_recent_errors':
            $response = getRecentErrors();
            break;
            
        default:
            $response = [
                'success' => false,
                'message' => 'Invalid action specified'
            ];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    // Log the error
    $logger = new Logger();
    $logger->log('ERROR', 'Monitoring API error: ' . $e->getMessage(), [
        'action' => $action,
        'file' => __FILE__,
        'line' => __LINE__
    ]);
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $e->getMessage()
    ]);
}

/**
 * Get system health status
 */
function getSystemHealth() {
    try {
        $db = Database::getInstance();
        
        // Get latest system health snapshot
        $health = $db->fetch("
            SELECT 
                database_status, 
                api_status, 
                overall_status,
                created_at,
                database_uptime
            FROM system_health_snapshots 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        
        if (!$health) {
            // Create a basic health check if no snapshot exists
            $health = [
                'database_status' => 'unknown',
                'api_status' => 'unknown',
                'overall_status' => 'unknown',
                'created_at' => date('Y-m-d H:i:s'),
                'database_uptime' => 'Unknown'
            ];
        }
        
        return [
            'success' => true,
            'data' => [
                'database' => $health['database_status'],
                'api' => $health['api_status'],
                'overall' => $health['overall_status'],
                'last_check' => $health['created_at'],
                'uptime' => $health['database_uptime']
            ]
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Failed to get system health',
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Get performance metrics
 */
function getPerformanceMetrics() {
    try {
        $db = Database::getInstance();
        
        // Get recent page load times
        $pageLoadTimes = $db->query("
            SELECT 
                created_at,
                duration_ms as load_time
            FROM performance_metrics 
            WHERE metric_type = 'page_load'
            ORDER BY created_at DESC 
            LIMIT 20
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        // Get memory usage from latest system health snapshot
        $memoryUsage = $db->fetch("
            SELECT 
                memory_usage_current as current,
                memory_usage_peak as peak,
                memory_limit
            FROM system_health_snapshots 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        
        return [
            'success' => true,
            'data' => [
                'page_load_times' => $pageLoadTimes,
                'memory_usage' => $memoryUsage ?: [
                    'current' => 'Unknown',
                    'peak' => 'Unknown',
                    'limit' => 'Unknown'
                ]
            ]
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Failed to get performance metrics',
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Get error logs
 */
function getErrorLogs() {
    try {
        $db = Database::getInstance();
        
        // Get recent errors with proper column names
        $errors = $db->query("
            SELECT 
                id,
                error_type,
                error_message,
                error_code,
                file_name,
                line_number,
                created_at,
                user_id,
                ip_address
            FROM error_logs 
            ORDER BY created_at DESC 
            LIMIT 50
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $errors
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Failed to get error logs',
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Log client-side error
 */
function logClientError($data) {
    try {
        $db = Database::getInstance();
        
        $stmt = $db->prepare("
            INSERT INTO error_logs (
                error_type, 
                error_message, 
                error_code, 
                file_name, 
                line_number, 
                stack_trace, 
                user_id, 
                ip_address, 
                user_agent, 
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            'client',
            $data['message'] ?? 'Unknown error',
            $data['code'] ?? '',
            $data['filename'] ?? '',
            $data['lineno'] ?? 0,
            $data['stack'] ?? '',
            $data['user_id'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        
        return [
            'success' => true,
            'message' => 'Error logged successfully'
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Failed to log error',
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Log performance metric
 */
function logPerformanceMetric($data) {
    try {
        $db = Database::getInstance();
        
        $stmt = $db->prepare("
            INSERT INTO performance_metrics (
                metric_type, 
                operation_name, 
                duration_ms, 
                memory_usage_mb, 
                user_id, 
                ip_address, 
                request_uri, 
                additional_data, 
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $data['type'] ?? 'unknown',
            $data['operation'] ?? 'unknown',
            $data['duration'] ?? 0,
            $data['memory'] ?? 0,
            $data['user_id'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['REQUEST_URI'] ?? '',
            json_encode($data['additional'] ?? [])
        ]);
        
        return [
            'success' => true,
            'message' => 'Performance metric logged successfully'
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Failed to log performance metric',
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Get database status
 */
function getDatabaseStatus() {
    try {
        $db = Database::getInstance();
        
        // Get database version
        $version = $db->fetch("SELECT VERSION() as version");
        
        // Get database status
        $status = $db->fetch("SHOW STATUS LIKE 'Uptime'");
        $uptime = $status ? formatUptime($status['Value']) : 'Unknown';
        
        // Get connection info
        $connections = $db->fetch("SHOW STATUS LIKE 'Threads_connected'");
        $activeConnections = $connections ? $connections['Value'] : 'Unknown';
        
        // Get slow query count
        $slowQueries = $db->fetch("SHOW STATUS LIKE 'Slow_queries'");
        $slowQueryCount = $slowQueries ? $slowQueries['Value'] : 'Unknown';
        
        return [
            'success' => true,
            'data' => [
                'version' => $version ? $version['version'] : 'Unknown',
                'uptime' => $uptime,
                'active_connections' => $activeConnections,
                'slow_queries' => $slowQueryCount
            ]
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Failed to get database status',
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Get recent errors for dashboard display
 */
function getRecentErrors() {
    try {
        $db = Database::getInstance();
        
        // Get recent errors grouped by type and message
        $errors = $db->query("
            SELECT 
                error_type,
                error_message,
                COUNT(*) as count,
                MAX(created_at) as created_at
            FROM error_logs 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            GROUP BY error_type, error_message
            ORDER BY count DESC, created_at DESC
            LIMIT 20
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $errors
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Failed to get recent errors',
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Format database uptime
 */
function formatUptime($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    
    if ($hours > 0) {
        return "{$hours}h {$minutes}m";
    } else {
        return "{$minutes}m";
    }
}

/**
 * Format bytes to human readable format
 */
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}
?>
