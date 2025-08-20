<?php
// API endpoint to check email availability
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';



// Set content type to JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get email from request
    $email = strtolower(trim($_POST['email'] ?? ''));
    
    // Validate email format
    if (empty($email)) {
        echo json_encode([
            'success' => false,
            'available' => false,
            'message' => 'Email address is required'
        ]);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'available' => false,
            'message' => 'Please enter a valid email address'
        ]);
        exit;
    }
    
    // Check if email already exists
    $db = Database::getInstance();
    $existingUser = $db->fetch("SELECT id, email_verified_at FROM users WHERE email = ?", [$email]);
    
    if ($existingUser) {
        // Email already exists
        $emailVerified = !empty($existingUser['email_verified_at']);
        $message = 'An account with this email address already exists';
        if ($emailVerified) {
            $message .= '. You can try logging in instead.';
        } else {
            $message .= '. Please check your email for verification or request a new verification email.';
        }
        
        echo json_encode([
            'success' => true,
            'available' => false,
            'message' => $message,
            'email_verified' => $emailVerified
        ]);
    } else {
        // Email is available
        echo json_encode([
            'success' => true,
            'available' => true,
            'message' => 'Email address is available'
        ]);
    }
    
} catch (Exception $e) {
    // Error occurred
    echo json_encode([
        'success' => false,
        'available' => false,
        'message' => 'Unable to check email availability. Please try again.'
    ]);
}
?>
