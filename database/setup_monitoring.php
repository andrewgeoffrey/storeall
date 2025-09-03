<?php
/**
 * StoreAll.io - Monitoring System Database Setup
 * This script creates all necessary database tables for the monitoring system
 */

// Load configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';

echo "🚀 Setting up StoreAll.io Monitoring System Database...\n\n";

try {
    $db = Database::getInstance();
    echo "✅ Database connection established\n";
    
    // Read and execute the SQL file
    $sqlFile = __DIR__ . '/create_monitoring_tables.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split SQL into individual statements more intelligently
    $statements = [];
    $currentStatement = '';
    $lines = explode("\n", $sql);
    
    foreach ($lines as $line) {
        $trimmedLine = trim($line);
        
        // Skip empty lines and comments
        if (empty($trimmedLine) || strpos($trimmedLine, '--') === 0) {
            continue;
        }
        
        $currentStatement .= $line . "\n";
        
        // Check if this line ends a statement
        if (strpos($trimmedLine, ';') !== false) {
            $statements[] = trim($currentStatement);
            $currentStatement = '';
        }
    }
    
    // Add any remaining statement
    if (!empty(trim($currentStatement))) {
        $statements[] = trim($currentStatement);
    }
    
    // Filter out empty statements
    $statements = array_filter($statements, function($stmt) {
        return !empty(trim($stmt));
    });
    
    echo "📋 Found " . count($statements) . " SQL statements to execute\n\n";
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $index => $statement) {
        try {
            if (!empty(trim($statement))) {
                echo "Executing statement " . ($index + 1) . "...\n";
                $db->query($statement);
                echo "✅ Statement " . ($index + 1) . " executed successfully\n";
                $successCount++;
            }
        } catch (Exception $e) {
            echo "❌ Statement " . ($index + 1) . " failed: " . $e->getMessage() . "\n";
            echo "Statement content: " . substr($statement, 0, 100) . "...\n";
            $errorCount++;
        }
    }
    
    echo "\n📊 Setup Summary:\n";
    echo "   ✅ Successful: $successCount\n";
    echo "   ❌ Failed: $errorCount\n";
    
    if ($errorCount === 0) {
        echo "\n🎉 Monitoring system database setup completed successfully!\n";
        echo "\n📋 Tables created:\n";
        echo "   • error_logs - Stores client-side and backend error logs\n";
        echo "   • performance_metrics - Stores performance data and metrics\n";
        echo "   • system_health_snapshots - Stores system health status\n";
        echo "   • user_activity_metrics - Stores user activity data\n";
        echo "   • api_performance_metrics - Stores API performance data\n";
        
        echo "\n🔧 Next steps:\n";
        echo "   1. The monitoring API is ready at: /admin/dashboard/monitoring_api.php\n";
        echo "   2. Client-side monitoring is active via: /admin/dashboard/monitoring.js\n";
        echo "   3. Admin dashboard shows live monitoring at: /admin/dashboard/\n";
        echo "   4. All pages now automatically collect monitoring data\n";
        
    } else {
        echo "\n⚠️  Some statements failed. Please check the errors above.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
    echo "\n🔧 Troubleshooting:\n";
    echo "   1. Check database connection settings in config/config.php\n";
    echo "   2. Ensure database user has CREATE TABLE permissions\n";
    echo "   3. Verify database exists and is accessible\n";
}

echo "\n✨ Setup script completed.\n";
?>
