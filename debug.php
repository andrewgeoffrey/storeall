<?php
echo "PHP is working!";
echo "<br>";
echo "Current time: " . date('Y-m-d H:i:s');
echo "<br>";
echo "PHP version: " . phpversion();
echo "<br>";

// Test if we can load the config
echo "<h2>Testing Config Load</h2>";
try {
    require_once 'config/config.php';
    echo "✅ Config loaded successfully<br>";
    echo "Environment: " . ENVIRONMENT . "<br>";
    echo "App Name: " . APP_NAME . "<br>";
} catch (Exception $e) {
    echo "❌ Config error: " . $e->getMessage() . "<br>";
}

// Test if we can load includes
echo "<h2>Testing Includes</h2>";
$includes = [
    'includes/Database.php',
    'includes/Auth.php',
    'includes/Session.php',
    'includes/Logger.php',
    'includes/ErrorHandler.php',
    'includes/PerformanceMonitor.php',
    'includes/helpers.php'
];

foreach ($includes as $include) {
    try {
        require_once $include;
        echo "✅ $include loaded<br>";
    } catch (Exception $e) {
        echo "❌ $include error: " . $e->getMessage() . "<br>";
    }
}

echo "<h2>Testing Database</h2>";
try {
    $db = Database::getInstance();
    echo "✅ Database connected<br>";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

echo "<h2>Session Status</h2>";
echo "Session status: " . session_status() . "<br>";
echo "Session ID: " . session_id() . "<br>";

echo "<h2>All Done!</h2>";
echo "<a href='index.php'>Try main page again</a>";
?>
