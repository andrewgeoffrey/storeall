<?php
// Test for PHP errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP is working!";
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Test</title>
</head>
<body>
    <h1>PHP Test Page</h1>
    <p>If you see this, PHP is working correctly.</p>
    
    <script>
        console.log('JavaScript test');
        alert('JavaScript is working!');
    </script>
</body>
</html>
