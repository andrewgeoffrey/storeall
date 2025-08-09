# StoreAll.io - Storage Unit Management SaaS Platform

A modern, scalable storage unit management platform built with PHP, MySQL, and Docker.

## 🚀 Features

- **Multi-tenant SaaS architecture** with role-based access control
- **Three pricing tiers**: Inventory Management, Web Hosting, Full Billing
- **Real-time inventory tracking** with waitlist management
- **Customer portal** for booking and account management
- **Admin dashboard** with comprehensive analytics
- **Stripe Connect integration** for payments
- **Mobile-responsive design** for all devices

## 🏗️ Architecture

- **Backend**: PHP 8.2+ with modern OOP practices
- **Database**: MySQL 8.0 with proper indexing
- **Cache**: Redis for session and data caching
- **Web Server**: Nginx with PHP-FPM
- **Containerization**: Docker for consistent development environment

## 📋 Prerequisites

- Docker Desktop for Windows
- Git
- PhpStorm (recommended) or any PHP IDE
- Modern web browser

## 🛠️ Installation & Setup

### 1. Clone the Repository
```bash
git clone <your-repo-url>
cd storeall
```

### 2. Install Docker Desktop
- Download from: https://www.docker.com/products/docker-desktop/
- Install and restart your computer
- Ensure Docker is running

### 3. Start the Development Environment
```bash
# Build and start all services
docker-compose up -d --build

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

### 4. Access Your Application
- **Main App**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
- **Database**: localhost:3306 (username: storeall_user, password: storeall_password)

## 🗄️ Database

The application includes a complete database schema with:
- User management and authentication
- Organization and multi-tenancy
- Location and unit management
- Customer and booking systems
- Audit logging and error tracking

### Default Admin User
- **Email**: admin@storeall.io
- **Password**: admin123

## 🏗️ Project Structure

```
storeall/
├── docker/                    # Docker configuration files
│   ├── php/                  # PHP container setup
│   ├── nginx/                # Nginx web server
│   └── mysql/                # Database initialization
├── src/                      # Application source code
│   ├── app/                  # Core application classes
│   ├── includes/             # Helper functions and utilities
│   └── api/                  # API endpoints
├── public/                   # Public web files
├── config/                   # Configuration files
├── database/                 # Database migrations and seeds
├── docker-compose.yml        # Main Docker configuration
└── README.md                 # This file
```

## 🚀 Development Workflow

### 1. Start Development
```bash
docker-compose up -d
```

### 2. Make Changes
- Edit files in the `src/` directory
- Changes are automatically reflected due to volume mounting

### 3. Test Your Changes
- Visit http://localhost:8080 to see your application
- Use phpMyAdmin at http://localhost:8081 for database management

### 4. Stop Development
```bash
docker-compose down
```

## 🔧 Configuration

### Environment Variables
Copy `env.example` to `.env` and update the values:
```bash
cp env.example .env
```

### Database Configuration
- **Host**: mysql (Docker service name)
- **Port**: 3306
- **Database**: storeall_dev
- **Username**: storeall_user
- **Password**: storeall_password

## 📱 Multi-Tenant Architecture

The platform supports three subscription tiers:

### Tier 1: Inventory Management ($29/month)
- Basic inventory tracking
- Unit status management
- Customer database

### Tier 2: Web Hosting ($49/month)
- Everything from Tier 1
- Custom website builder
- SEO optimization tools

### Tier 3: Full Billing ($99/month)
- Everything from Tier 2
- Payment processing
- Advanced analytics
- Customer portal

## 🛡️ Security Features

- Role-based access control (Admin, Super User, Owner, Customer)
- Secure password hashing
- SQL injection prevention
- XSS protection
- CSRF token validation
- Comprehensive audit logging

## 🧪 Testing

```bash
# Run tests (when implemented)
docker-compose exec app php vendor/bin/phpunit

# Check code quality
docker-compose exec app php vendor/bin/phpcs
```

## 📊 Monitoring & Logging

- **Application Logs**: Check Docker container logs
- **Database Logs**: Available in MySQL container
- **Error Tracking**: Comprehensive error logging system
- **Audit Trail**: All user actions are logged

## 🚀 Deployment

### Production Considerations
- Update environment variables
- Configure SSL certificates
- Set up proper backup strategies
- Implement monitoring and alerting
- Configure production database settings

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

[Your License Here]

## 🆘 Support

For support and questions:
- Create an issue in the repository
- Contact: [your-email@domain.com]

## 🔄 Updates

To update your development environment:
```bash
git pull origin main
docker-compose down
docker-compose up -d --build
```

---

**Happy Coding! 🎉**

Built with ❤️ for the storage industry.
