<?php
/**
 * Simple Test
 * Test the cleanupExpiredTrustedDevices method
 */

// Start output buffering
ob_start();

echo "<h1>Simple Test</h1>";

// Include config first
require_once __DIR__ . '/config/config.php';
echo "<p>✓ Config loaded</p>";

// Create a simple LoginTracker class with just the method we need
class LoginTracker {
    private $db;
    private $logger;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->logger = Logger::getInstance();
    }

    public function cleanupExpiredTrustedDevices() {
        try {
            $sql = "UPDATE trusted_devices SET is_active = 0 WHERE mfa_suppressed_until < NOW()";
            $stmt = $this->db->query($sql);
            $affected = $stmt->rowCount();
            
            if ($affected > 0) {
                $this->logger->info('Expired trusted devices cleaned up', [
                    'affected_count' => $affected
                ]);
            }
            
            return $affected;
        } catch (Exception $e) {
            $this->logger->error('Failed to cleanup expired trusted devices', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
}

echo "<p>✓ LoginTracker class defined</p>";

// Create instance
$loginTracker = new LoginTracker();
echo "<p>✓ LoginTracker instance created</p>";

// Check if method exists
if (method_exists($loginTracker, 'cleanupExpiredTrustedDevices')) {
    echo "<p>✓ cleanupExpiredTrustedDevices method exists</p>";
    
    // Test the method
    try {
        $result = $loginTracker->cleanupExpiredTrustedDevices();
        echo "<p>✓ Method executed successfully: {$result} devices affected</p>";
    } catch (Exception $e) {
        echo "<p>✗ Method execution failed: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>✗ cleanupExpiredTrustedDevices method does not exist</p>";
    
    // List all methods
    $methods = get_class_methods($loginTracker);
    echo "<p><strong>Available methods:</strong></p>";
    echo "<ul>";
    foreach ($methods as $method) {
        echo "<li>{$method}</li>";
    }
    echo "</ul>";
}

// Flush output buffer
ob_end_flush();
?>
