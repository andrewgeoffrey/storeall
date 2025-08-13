<?php
/**
 * Test file to diagnose 404 error
 */

echo "<h1>StoreAll.io - Test Page</h1>";
echo "<p>PHP is working correctly!</p>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP version: " . phpversion() . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' . "</p>";
echo "<p>Document root: " . $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown' . "</p>";
echo "<p>Script path: " . __FILE__ . "</p>";

// Test if we can include the config file
echo "<h2>Configuration Test</h2>";
try {
    if (file_exists('config/config.php')) {
        echo "<p style='color: green;'>✓ config/config.php exists</p>";
        include_once 'config/config.php';
        echo "<p style='color: green;'>✓ config/config.php loaded successfully</p>";
        echo "<p>APP_NAME: " . (defined('APP_NAME') ? APP_NAME : 'Not defined') . "</p>";
        echo "<p>APP_URL: " . (defined('APP_URL') ? APP_URL : 'Not defined') . "</p>";
    } else {
        echo "<p style='color: red;'>✗ config/config.php does not exist</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error loading config: " . $e->getMessage() . "</p>";
}

// Test if we can include the includes files
echo "<h2>Includes Test</h2>";
$includes = ['Database.php', 'Auth.php', 'Session.php', 'Logger.php', 'helpers.php'];
foreach ($includes as $include) {
    $path = 'includes/' . $include;
    if (file_exists($path)) {
        echo "<p style='color: green;'>✓ $path exists</p>";
    } else {
        echo "<p style='color: red;'>✗ $path does not exist</p>";
    }
}

// Test database connection
echo "<h2>Database Test</h2>";
try {
    if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER')) {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS ?? '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo "<p style='color: green;'>✓ Database connection successful</p>";
    } else {
        echo "<p style='color: red;'>✗ Database constants not defined</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}

// Show all defined constants
echo "<h2>Defined Constants</h2>";
echo "<pre>";
$constants = get_defined_constants(true);
if (isset($constants['user'])) {
    foreach ($constants['user'] as $name => $value) {
        if (strpos($name, 'DB_') === 0) {
            echo "$name: " . (strpos($name, 'PASS') !== false ? '***HIDDEN***' : $value) . "\n";
        }
    }
}
echo "</pre>";
?>
