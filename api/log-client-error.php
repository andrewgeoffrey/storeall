<?php
// API endpoint for logging client-side errors
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Logger.php';

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
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    $errorType = $input['error_type'] ?? 'javascript';
    $errorMessage = $input['error_message'] ?? 'Unknown error';
    $additionalData = $input['additional_data'] ?? [];
    
    // Log the client error
    $logger = Logger::getInstance();
    $logger->logClientError($errorType, $errorMessage, $additionalData);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Error logged successfully'
    ]);
    
} catch (Exception $e) {
    // Log the error in the application logs
    if (class_exists('Logger')) {
        Logger::getInstance()->error('Failed to log client error', [
            'error' => $e->getMessage(),
            'input' => $input ?? null
        ]);
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to log error'
    ]);
}
?>


