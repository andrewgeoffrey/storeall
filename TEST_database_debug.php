<?php
// Debug database connection and current database
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';

echo "Debugging database connection...\n";

try {
    $db = Database::getInstance();
    
    // Check current database
    $currentDb = $db->fetchColumn("SELECT DATABASE()");
    echo "Current database: " . ($currentDb ?: 'NULL') . "\n";
    
    // Check if we can connect
    $result = $db->fetchColumn("SELECT 1");
    echo "Database connection test: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
    
    // List all databases
    echo "\nAvailable databases:\n";
    $databases = $db->fetchAll("SHOW DATABASES");
    foreach ($databases as $dbInfo) {
        $dbName = $dbInfo['Database'];
        echo "- {$dbName}\n";
    }
    
    // List all tables in current database
    echo "\nTables in current database:\n";
    $tables = $db->fetchAll("SHOW TABLES");
    if (empty($tables)) {
        echo "No tables found in current database.\n";
    } else {
        foreach ($tables as $tableInfo) {
            $tableName = array_values($tableInfo)[0];
            echo "- {$tableName}\n";
        }
    }
    
    // Check if storeall_dev database exists
    echo "\nChecking storeall_dev database:\n";
    $storeallDevExists = $db->fetchColumn("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'storeall_dev'");
    echo "storeall_dev database exists: " . ($storeallDevExists ? 'YES' : 'NO') . "\n";
    
    if ($storeallDevExists) {
        echo "Switching to storeall_dev database...\n";
        $db->query("USE storeall_dev");
        
        echo "Tables in storeall_dev database:\n";
        $tables = $db->fetchAll("SHOW TABLES");
        if (empty($tables)) {
            echo "No tables found in storeall_dev database.\n";
        } else {
            foreach ($tables as $tableInfo) {
                $tableName = array_values($tableInfo)[0];
                echo "- {$tableName}\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
