# StoreAll.io - Project Structure

This document outlines the folder structure and organization following the logistics requirements.

## Root Level Structure

```
storeall/
├── .htaccess                    # Main .htaccess for clean URLs and security
├── index.php                    # Main entry point for the application
├── TEST_setup_database.php      # Database setup script (root level for testing)
├── composer.json                # PHP dependencies
├── composer.phar                # Composer executable
├── env.example                  # Environment variables template
├── README.md                    # Main project documentation
├── storeall_v1.txt             # Requirements specification
│
├── includes/                    # All shared files at root level
│   ├── .htaccess               # Prevent direct access to PHP files
│   ├── index.php               # Access prevention
│   ├── Database.php            # Database connection class
│   ├── Auth.php                # Authentication class
│   ├── Session.php             # Session management
│   ├── Logger.php              # Logging functionality
│   ├── ErrorHandler.php        # Error handling and logging
│   ├── PerformanceMonitor.php  # Performance tracking
│   ├── Email.php               # Email functionality
│   ├── Stripe.php              # Stripe integration
│   ├── Validation.php          # Input validation
│   ├── Security.php            # Security utilities
│   │
│   ├── js/                     # JavaScript files
│   │   ├── error-handler.js    # Client-side error handling
│   │   ├── performance-monitor.js # Client-side performance monitoring
│   │   └── main.js             # Main JavaScript functionality
│   │
│   └── css/                    # CSS files
│       ├── main.css            # Main stylesheet with Bootstrap
│       └── components.css      # Component-specific styles
│
├── docs/                       # All documentation files
│   ├── .htaccess               # Allow access to .md files
│   ├── index.php               # Access prevention
│   ├── PROJECT_STRUCTURE.md    # This file
│   ├── API_DOCUMENTATION.md    # API documentation
│   ├── DATABASE_SCHEMA.md      # Database schema documentation
│   └── DEPLOYMENT_GUIDE.md     # Deployment instructions
│
├── database/                   # Database files
│   ├── .htaccess               # Allow access to .sql files
│   ├── index.php               # Access prevention
│   ├── schema.sql              # Complete database schema
│   ├── migrations/             # Database migrations
│   └── seeds/                  # Sample data
│
├── config/                     # Configuration files
│   ├── config.php              # Main configuration
│   ├── database.php            # Database configuration
│   ├── stripe.php              # Stripe configuration
│   └── email.php               # Email configuration
│
├── public/                     # Public web files
│   ├── landing.php             # Landing page
│   ├── 404.php                 # 404 error page
│   ├── error.php               # Error page
│   └── assets/                 # Public assets
│
├── admin/                      # Admin panel
│   ├── index.php               # Admin entry point
│   ├── dashboard.php           # Admin dashboard
│   ├── users.php               # User management
│   └── organizations.php       # Organization management
│
├── api/                        # API endpoints
│   ├── index.php               # API router
│   ├── auth.php                # Authentication endpoints
│   ├── inventory.php           # Inventory API
│   ├── billing.php             # Billing API
│   └── log-error.php           # Error logging endpoint
│
├── billing/                    # Billing system
│   ├── index.php               # Billing entry point
│   ├── subscriptions.php       # Subscription management
│   └── webhooks/               # Webhook handlers
│
├── customers/                  # Customer portal
│   ├── index.php               # Customer entry point
│   ├── dashboard.php           # Customer dashboard
│   └── rentals.php             # Rental management
│
├── owners/                     # Owner-specific folders
│   └── {owner-slug}/           # Dynamic owner folders
│       ├── index.php           # Owner entry point
│       ├── cust/               # Customer-facing pages
│       └── manage/             # Management interface
│
├── docker/                     # Docker configuration
│   ├── php/                    # PHP container setup
│   ├── nginx/                  # Nginx configuration
│   └── mysql/                  # Database setup
│
├── logs/                       # Application logs
├── uploads/                    # File uploads
└── vendor/                     # Composer dependencies
```

## Key Features Implemented

### 1. Clean URL Structure
- `.htaccess` at root level handles clean URLs
- `index.php` files in each folder prevent showing .php extensions
- Test/setup files clearly labeled and at root level

### 2. Security
- `.htaccess` files prevent direct access to sensitive folders
- All includes, config, and database files protected
- Security headers implemented

### 3. Error Handling & Performance Monitoring
- `ErrorHandler.php` captures server-side errors
- `PerformanceMonitor.php` tracks query and page load performance
- Client-side JavaScript error handling and performance monitoring
- All errors and performance data logged to database

### 4. Mobile-Responsive Design
- Bootstrap 5.3+ integration
- Mobile-first responsive design
- Accessibility features implemented
- Google mobile-friendly compliance

### 5. Modal Technology
- Bootstrap modal integration ready
- JavaScript framework for modal interactions
- Consistent modal styling

### 6. File Organization
- All shared files in `includes/` at root level
- All documentation in `docs/` folder
- All database files in `database/` folder
- Test files clearly labeled at root level

## Next Steps

1. **Database Setup**: Run `TEST_setup_database.php` to create tables
2. **Configuration**: Copy `env.example` to `.env` and configure settings
3. **Docker Setup**: Use existing `docker-compose.yml` for development
4. **Authentication**: Implement the Auth class with role-based access
5. **Tier Implementation**: Build the three-tier system (Tier 1, 2, 3)

## Development Workflow

1. Start Docker environment: `docker-compose up -d`
2. Access application: `http://localhost:8080`
3. Run database setup: `http://localhost:8080/TEST_setup_database.php`
4. Delete test files after setup
5. Begin development with the established structure

This structure follows all the logistics requirements and provides a solid foundation for the StoreAll.io platform.




