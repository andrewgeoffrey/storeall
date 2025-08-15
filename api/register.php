<?php
// API Registration Endpoint for StoreAll.io
// This file handles registration requests via API

// Load configuration and core classes
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/Logger.php';
require_once __DIR__ . '/../includes/ErrorHandler.php';
require_once __DIR__ . '/../includes/Email.php';
require_once __DIR__ . '/../includes/helpers.php';

// Set content type to JSON for API responses
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
    // Get form data
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $confirmEmail = trim($_POST['confirmEmail'] ?? '');
    $companyName = trim($_POST['companyName'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $terms = $_POST['terms'] ?? '';
    $newsletter = $_POST['newsletter'] ?? '';

    // Validation array to collect all errors
    $errors = [];

    // Validate required fields
    if (empty($firstName)) {
        $errors['firstName'] = 'First name is required';
    }

    if (empty($lastName)) {
        $errors['lastName'] = 'Last name is required';
    }

    if (empty($email)) {
        $errors['email'] = 'Email address is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }

    if (empty($confirmEmail)) {
        $errors['confirmEmail'] = 'Please confirm your email address';
    } elseif ($email !== $confirmEmail) {
        $errors['confirmEmail'] = 'Email addresses do not match';
    }

    if (empty($companyName)) {
        $errors['companyName'] = 'Company name is required';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 12) {
        $errors['password'] = 'Password must be at least 12 characters long';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors['password'] = 'Password must contain at least one uppercase letter';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors['password'] = 'Password must contain at least one lowercase letter';
    } elseif (!preg_match('/\d/', $password)) {
        $errors['password'] = 'Password must contain at least one number';
    } elseif (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
        $errors['password'] = 'Password must contain at least one special character';
    }

    if (empty($confirmPassword)) {
        $errors['confirmPassword'] = 'Please confirm your password';
    } elseif ($password !== $confirmPassword) {
        $errors['confirmPassword'] = 'Passwords do not match';
    }

    if (empty($terms)) {
        $errors['terms'] = 'You must agree to the Terms of Service and Privacy Policy';
    }

    // Validate optional fields
    if (!empty($phone)) {
        $cleanPhone = preg_replace('/[\s\-\(\)]/', '', $phone);
        if (!preg_match('/^[\+]?[1-9][\d]{0,15}$/', $cleanPhone)) {
            $errors['phone'] = 'Please enter a valid phone number';
        }
    }

    if (!empty($website)) {
        if (!filter_var($website, FILTER_VALIDATE_URL)) {
            $errors['website'] = 'Please enter a valid website URL';
        }
    }

    // If there are validation errors, return them
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'errors' => $errors,
            'message' => 'Please correct the errors below'
        ]);
        exit;
    }

    // Initialize database connection
    $db = Database::getInstance();

    // Check if email already exists
    $existingUser = $db->fetch("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existingUser) {
        echo json_encode([
            'success' => false,
            'errors' => ['email' => 'An account with this email address already exists'],
            'message' => 'Email already registered'
        ]);
        exit;
    }

    // Check if company name/subdomain already exists
    $subdomain = createSubdomain($companyName);
    $existingOrg = $db->fetch("SELECT id FROM organizations WHERE subdomain = ?", [$subdomain]);
    if ($existingOrg) {
        // Try with a number suffix
        $counter = 1;
        do {
            $newSubdomain = $subdomain . $counter;
            $existingOrg = $db->fetch("SELECT id FROM organizations WHERE subdomain = ?", [$newSubdomain]);
            $counter++;
        } while ($existingOrg && $counter < 100);
        $subdomain = $newSubdomain;
    }

    // Start transaction
    $db->beginTransaction();

    try {
        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Create user record
        $userId = $db->insert('users', [
            'email' => $email,
            'password_hash' => $passwordHash,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Create organization record
        $orgId = $db->insert('organizations', [
            'name' => $companyName,
            'subdomain' => $subdomain,
            'domain' => $website ?: null,
            'tier' => 'tier1',
            'status' => 'trial',
            'trial_ends_at' => date('Y-m-d H:i:s', strtotime('+14 days')),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Assign owner role to user
        $db->insert('user_roles', [
            'user_id' => $userId,
            'role' => 'owner',
            'organization_id' => $orgId,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Create primary location for the organization
        $locationId = $db->insert('locations', [
            'organization_id' => $orgId,
            'name' => $companyName . ' - Main Location',
            'address' => 'Address to be updated',
            'phone' => $phone ?: null,
            'email' => $email,
            'is_primary' => true,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Generate email verification token
        $verificationToken = bin2hex(random_bytes(32));
        $verificationExpiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Store verification token (you might want to create a separate table for this)
        // For now, we'll store it in a session or temporary storage
        $_SESSION['verification_tokens'][$userId] = [
            'token' => $verificationToken,
            'expires' => $verificationExpiry
        ];

        // Log the registration
        if (class_exists('Logger')) {
            Logger::getInstance()->info('User registered via API', [
                'user_id' => $userId,
                'email' => $email,
                'organization_id' => $orgId,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        }

        // Commit transaction
        $db->commit();

        // Send welcome email
        if (class_exists('Email')) {
            Email::getInstance()->sendWelcomeEmail($email, $firstName, $verificationToken);
        }

        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful! Please check your email to confirm your account.',
            'data' => [
                'user_id' => $userId,
                'organization_id' => $orgId,
                'subdomain' => $subdomain,
                'trial_ends' => date('Y-m-d H:i:s', strtotime('+14 days'))
            ]
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollback();
        
        // Log the error
        if (class_exists('Logger')) {
            Logger::getInstance()->error('API registration failed', [
                'error' => $e->getMessage(),
                'email' => $email,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        }

        echo json_encode([
            'success' => false,
            'message' => 'Registration failed. Please try again or contact support.',
            'error' => ENVIRONMENT === 'development' ? $e->getMessage() : null
        ]);
    }

} catch (Exception $e) {
    // Log the error
    if (class_exists('Logger')) {
        Logger::getInstance()->error('API registration handler error', [
            'error' => $e->getMessage(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }

    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred. Please try again.',
        'error' => ENVIRONMENT === 'development' ? $e->getMessage() : null
    ]);
}

/**
 * Create a URL-friendly subdomain from company name
 */
function createSubdomain($companyName) {
    // Remove special characters and convert to lowercase
    $subdomain = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($companyName));
    
    // Limit length
    if (strlen($subdomain) > 50) {
        $subdomain = substr($subdomain, 0, 50);
    }
    
    // Ensure it's not empty
    if (empty($subdomain)) {
        $subdomain = 'company' . time();
    }
    
    return $subdomain;
}


?>
