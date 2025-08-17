# StoreAll.io Registration Verification System

## Overview

The registration verification system ensures that users verify their email addresses before accessing the platform. This document outlines the complete system architecture, database schema, and troubleshooting guide.

## System Architecture

### Files Structure
```
├── api/
│   └── register.php              # Registration API endpoint
├── verify-email.php              # Email verification handler (root level)
├── includes/
│   ├── Database.php              # Database abstraction layer
│   ├── Email.php                 # Email sending functionality
│   └── Logger.php                # Logging system
└── config/
    └── config.php                # Application configuration
```

### Database Tables

#### 1. `verification_tokens` Table
Primary table for tracking verification tokens:

```sql
CREATE TABLE verification_tokens (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    user_id bigint unsigned NOT NULL,
    token varchar(255) NOT NULL UNIQUE,
    type enum('email_verification','password_reset') NOT NULL,
    expires_at timestamp NOT NULL,
    used_at timestamp NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

**Purpose**: Stores verification tokens and tracks their usage status.

#### 2. `users` Table
Tracks user verification status:

```sql
CREATE TABLE users (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    email varchar(255) NOT NULL UNIQUE,
    password_hash varchar(255) NOT NULL,
    first_name varchar(100) NOT NULL,
    last_name varchar(100) NOT NULL,
    email_verified_at timestamp NULL,  -- ← Verification timestamp
    two_factor_enabled tinyint(1) DEFAULT 0,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```

**Purpose**: Records when a user's email was verified.

#### 3. `audit_logs` Table (Optional)
For comprehensive logging:

```sql
CREATE TABLE audit_logs (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    user_id bigint unsigned NULL,
    action varchar(100) NOT NULL,
    details json NULL,
    ip_address varchar(45) NULL,
    user_agent text NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```

#### 4. `application_logs` Table
For general application logging:

```sql
CREATE TABLE application_logs (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    level enum('DEBUG','INFO','WARNING','ERROR','CRITICAL') NOT NULL,
    message text NOT NULL,
    context json NULL,
    timestamp timestamp NOT NULL,
    ip_address varchar(45) NULL,
    user_agent text NULL,
    user_id bigint unsigned NULL,
    request_uri varchar(500) NULL,
    request_method varchar(10) NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```

#### 5. `performance_metrics` Table
For tracking performance data:

```sql
CREATE TABLE performance_metrics (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    metric_type enum('database_query','api_request','page_load','email_send','file_upload','verification_process') NOT NULL,
    operation_name varchar(255) NOT NULL,
    duration_ms decimal(10,3) NOT NULL,
    memory_usage_mb decimal(10,3) NULL,
    cpu_usage_percent decimal(5,2) NULL,
    user_id bigint unsigned NULL,
    ip_address varchar(45) NULL,
    request_uri varchar(500) NULL,
    additional_data json NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```

#### 6. `client_errors` Table
For client-side error tracking:

```sql
CREATE TABLE client_errors (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    error_type enum('javascript','validation','ajax','form_submission','ui_interaction') NOT NULL,
    error_message text NOT NULL,
    error_stack text NULL,
    error_file varchar(255) NULL,
    error_line int NULL,
    error_column int NULL,
    user_id bigint unsigned NULL,
    session_id varchar(255) NULL,
    ip_address varchar(45) NULL,
    user_agent text NULL,
    page_url varchar(500) NULL,
    referrer_url varchar(500) NULL,
    browser_info json NULL,
    additional_context json NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```

#### 7. `database_errors` Table
For database error tracking:

```sql
CREATE TABLE database_errors (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    error_type enum('connection','query','transaction','constraint','timeout','deadlock') NOT NULL,
    error_message text NOT NULL,
    error_code varchar(100) NULL,
    sql_query text NULL,
    query_params json NULL,
    table_name varchar(100) NULL,
    operation_type enum('SELECT','INSERT','UPDATE','DELETE','CREATE','ALTER','DROP') NULL,
    execution_time_ms decimal(10,3) NULL,
    user_id bigint unsigned NULL,
    ip_address varchar(45) NULL,
    request_uri varchar(500) NULL,
    stack_trace text NULL,
    additional_context json NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```

#### 8. `api_requests` Table
For API request tracking:

```sql
CREATE TABLE api_requests (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    endpoint varchar(255) NOT NULL,
    method varchar(10) NOT NULL,
    status_code int NOT NULL,
    response_time_ms decimal(10,3) NOT NULL,
    request_size_bytes int NULL,
    response_size_bytes int NULL,
    user_id bigint unsigned NULL,
    ip_address varchar(45) NULL,
    user_agent text NULL,
    request_headers json NULL,
    request_body json NULL,
    response_body_preview text NULL,
    error_message text NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```

#### 9. `email_logs` Table
For email tracking:

```sql
CREATE TABLE email_logs (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    email_type enum('welcome','verification','password_reset','notification','system') NOT NULL,
    recipient_email varchar(255) NOT NULL,
    subject varchar(255) NOT NULL,
    status enum('sent','failed','pending','bounced') NOT NULL,
    error_message text NULL,
    sent_at timestamp NULL,
    delivered_at timestamp NULL,
    opened_at timestamp NULL,
    clicked_at timestamp NULL,
    user_id bigint unsigned NULL,
    ip_address varchar(45) NULL,
    additional_data json NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```

#### 10. `verification_logs` Table
For verification process tracking:

```sql
CREATE TABLE verification_logs (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    verification_type enum('email_verification','password_reset','two_factor') NOT NULL,
    user_id bigint unsigned NOT NULL,
    token_id bigint unsigned NULL,
    action enum('token_created','token_sent','token_verified','token_expired','token_invalid','token_used') NOT NULL,
    status enum('success','failed','pending') NOT NULL,
    error_message text NULL,
    ip_address varchar(45) NULL,
    user_agent text NULL,
    additional_context json NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```

## Verification Flow

### 1. User Registration
1. User submits registration form via `api/register.php`
2. System validates input data
3. Creates user record in `users` table
4. Creates organization and location records
5. Generates verification token (32-byte hex string)
6. Stores token in `verification_tokens` table
7. Sends welcome email with verification link
8. Returns success response

### 2. Email Verification
1. User clicks verification link in email
2. Link points to `verify-email.php?token=<token>`
3. System validates token:
   - Checks if token exists
   - Verifies token hasn't expired
   - Confirms token hasn't been used
4. Updates `users.email_verified_at` timestamp
5. Marks token as used in `verification_tokens.used_at`
6. Logs verification event
7. Displays success/error page

## Key Components

### Registration API (`api/register.php`)
- Handles POST requests for user registration
- Validates all form fields
- Creates user, organization, and verification token
- Sends welcome email
- Returns JSON response

### Verification Handler (`verify-email.php`)
- Processes verification tokens from URL
- Validates token authenticity and expiration
- Updates user verification status
- Provides user-friendly success/error pages

### Email System (`includes/Email.php`)
- Sends welcome emails with verification links
- Uses MailHog for development testing
- Configurable SMTP settings

### Database Layer (`includes/Database.php`)
- Handles all database operations
- Supports transactions for data consistency
- Fixed parameter handling for mixed named/positional parameters

## Configuration

### Email Settings (`config/config.php`)
```php
// MailHog configuration for development
define('MAIL_HOST', 'mailhog');
define('MAIL_PORT', 1025);
define('MAIL_FROM_ADDRESS', 'noreply@storeall.io');
define('MAIL_FROM_NAME', 'StoreAll.io');
define('APP_URL', 'http://localhost:8080');
```

### Database Settings
```php
define('DB_HOST', 'mysql');
define('DB_NAME', 'storeall_dev');
define('DB_USER', 'storeall_user');
define('DB_PASS', 'storeall_pass');
```

## Issues Fixed

### 1. Database Column Mismatch
**Problem**: Test scripts used `password` instead of `password_hash`
**Solution**: Updated all references to use correct column name

### 2. Database Update Method
**Problem**: Mixed named and positional parameters caused SQL errors
**Solution**: Enhanced `Database.php` to convert all parameters to named format

### 3. Table Existence Check
**Problem**: `tableExists()` method used `rowCount()` which doesn't work with `SHOW TABLES`
**Solution**: Changed to use `fetch()` method

### 4. Subdomain Duplication
**Problem**: Duplicate subdomains caused registration failures
**Solution**: Added logic to append numbers to existing subdomains

## Testing

### Manual Testing
1. Register new user at `http://localhost:8080`
2. Check MailHog at `http://localhost:8025` for welcome email
3. Click verification link in email
4. Verify success page displays
5. Check database for updated verification status

### Database Queries for Verification
```sql
-- Check all verification tokens
SELECT vt.*, u.email, u.email_verified_at 
FROM verification_tokens vt 
JOIN users u ON vt.user_id = u.id 
WHERE vt.type = 'email_verification'
ORDER BY vt.created_at DESC;

-- Check unverified users
SELECT * FROM users WHERE email_verified_at IS NULL;

-- Check verified users
SELECT * FROM users WHERE email_verified_at IS NOT NULL;
```

## Security Considerations

### Token Security
- Tokens are 32-byte random hex strings (256 bits)
- Tokens expire after 24 hours
- Tokens can only be used once
- Tokens are stored hashed in database

### Email Security
- Verification links use HTTPS in production
- Links include secure tokens
- Expiration prevents replay attacks

### Database Security
- All user inputs are validated and sanitized
- SQL injection prevented via prepared statements
- Passwords are hashed using PHP's `password_hash()`

## Comprehensive Logging System

The StoreAll.io application includes a comprehensive logging system that captures all types of errors, performance metrics, and user activities.

### Logging Categories

#### 1. **Client-Side Errors** (`client_errors` table)
- JavaScript errors and exceptions
- Form validation errors
- AJAX request failures
- UI interaction issues
- Browser compatibility problems

#### 2. **Database Errors** (`database_errors` table)
- Connection failures
- Query execution errors
- Constraint violations
- Transaction failures
- Timeout and deadlock issues

#### 3. **Performance Metrics** (`performance_metrics` table)
- Database query execution times
- API response times
- Page load times
- Email sending performance
- File upload durations

#### 4. **API Requests** (`api_requests` table)
- All API endpoint calls
- Request/response sizes
- Status codes
- Response times
- Error messages

#### 5. **Email Logs** (`email_logs` table)
- Email sending status
- Delivery tracking
- Open/click tracking
- Bounce handling
- Error messages

#### 6. **Verification Logs** (`verification_logs` table)
- Token creation events
- Token verification attempts
- Expired token handling
- Failed verification attempts
- Success confirmations

#### 7. **Application Logs** (`application_logs` table)
- General application events
- System startup/shutdown
- User actions
- Security events
- Debug information

### Logging Implementation

#### Client-Side Error Capture
```javascript
// Automatic error capture
window.addEventListener('error', function(e) {
    logClientError('javascript', e.message, {
        file: e.filename,
        line: e.lineno,
        column: e.colno,
        stack: e.error ? e.error.stack : null
    });
});

// Manual error logging
logClientError('validation', 'Invalid email format', {
    field: 'email',
    value: 'invalid-email'
});
```

#### Server-Side Logging
```php
// Performance logging
$logger->logPerformanceMetric('database_query', 'user_registration', 150.5, [
    'memory' => 25.3,
    'cpu' => 5.2
]);

// Database error logging
$logger->logDatabaseError('query', 'Table not found', [
    'sql' => 'SELECT * FROM users',
    'table' => 'users',
    'operation' => 'SELECT'
]);

// API request logging
$logger->logApiRequest('/api/register', 'POST', 200, 125.3, [
    'request_size' => 1024,
    'response_size' => 512
]);
```

### Error Handling

### Common Errors
1. **Token Not Found**: Invalid or expired token
2. **Token Already Used**: User clicked link multiple times
3. **Token Expired**: Link older than 24 hours
4. **Database Errors**: Connection or query failures

### Logging
- All verification attempts are logged
- Errors include user context and IP address
- Audit trail maintained for security

## Production Deployment

### Email Configuration
- Replace MailHog with production SMTP server
- Update `MAIL_HOST`, `MAIL_PORT`, credentials
- Configure proper SPF/DKIM records

### Security Settings
- Enable HTTPS (`APP_URL` with https://)
- Set secure session cookies
- Configure proper CORS headers

### Monitoring
- Monitor verification success rates
- Track email delivery rates
- Alert on unusual verification patterns

## Troubleshooting

### Verification Not Working
1. Check database connection
2. Verify token exists in `verification_tokens` table
3. Confirm token hasn't expired
4. Check if token has been used
5. Review error logs

### Emails Not Sending
1. Verify MailHog is running (`http://localhost:8025`)
2. Check SMTP configuration
3. Review PHP mail logs
4. Test email sending manually

### Database Issues
1. Confirm all tables exist
2. Check foreign key constraints
3. Verify database permissions
4. Review transaction logs

## Future Enhancements

### Planned Features
- Resend verification email functionality
- Multiple email verification support
- Two-factor authentication integration
- Advanced audit logging
- Rate limiting for verification attempts

### Performance Optimizations
- Token cleanup for expired tokens
- Database indexing optimization
- Email queue system
- Caching for frequently accessed data

---

**Last Updated**: August 16, 2025  
**Version**: 1.0  
**Status**: Production Ready ✅
