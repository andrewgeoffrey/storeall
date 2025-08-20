<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StoreAll.io - Complete Storage Management Solution</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #3b82f6;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
            --text-color: #374151;
            --border-color: #e5e7eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: #ffffff;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-color) !important;
        }

        .nav-link {
            font-weight: 500;
            color: var(--text-color) !important;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 120px 0 80px;
            text-align: center;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .feature-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            color: white;
            font-size: 1.5rem;
        }

        .stats-section {
            background: var(--light-color);
            padding: 80px 0;
        }

        .stat-item {
            text-align: center;
            padding: 2rem;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.1rem;
            color: var(--text-color);
            font-weight: 500;
        }

        .pricing-card {
            background: white;
            border-radius: 1rem;
            padding: 2.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 2px solid var(--border-color);
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
        }

        .pricing-card.featured {
            border-color: var(--primary-color);
            transform: scale(1.05);
        }

        .pricing-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .pricing-card.featured:hover {
            transform: scale(1.05) translateY(-5px);
        }

        .price {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .price-period {
            color: var(--text-color);
            opacity: 0.7;
        }

        .testimonial-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
            margin-bottom: 2rem;
        }

        .testimonial-text {
            font-style: italic;
            margin-bottom: 1.5rem;
            color: var(--text-color);
        }

        .testimonial-author {
            font-weight: 600;
            color: var(--primary-color);
        }

        .footer {
            background: var(--dark-color);
            color: white;
            padding: 60px 0 30px;
        }

        .footer h5 {
            color: white;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .footer a {
            color: #9ca3af;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: white;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 1rem;
            color: var(--dark-color);
        }

        .section-subtitle {
            font-size: 1.1rem;
            text-align: center;
            margin-bottom: 3rem;
            color: var(--text-color);
            opacity: 0.8;
        }

        /* Modal Styles */
        .modal-content {
            border-radius: 1rem;
            border: none;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            border-bottom: 1px solid var(--border-color);
            padding: 2rem 2rem 1rem;
        }

        .modal-title {
            font-weight: 700;
            color: var(--dark-color);
            font-size: 1.5rem;
        }

        .modal-body {
            padding: 2rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 2px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.1);
        }
        
        .form-control.is-loading {
            background-image: url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="%23666" class="bi bi-arrow-clockwise" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/><path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .error-message.text-success {
            color: #28a745 !important;
        }
        
        .error-message.text-warning {
            color: #ffc107 !important;
        }

        .form-select {
            border: 2px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            font-size: 1rem;
        }

        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.1);
        }

        .btn-register {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
        }

        .modal-footer {
            border-top: 1px solid var(--border-color);
            padding: 1rem 2rem 2rem;
        }

        .login-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .login-link:hover {
            text-decoration: underline;
        }

        .tier-badge {
            background: linear-gradient(135deg, var(--success-color), #059669);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        .password-strength .progress {
            background-color: #e9ecef;
            border-radius: 0.25rem;
        }

        .password-strength .progress-bar {
            transition: width 0.3s ease, background-color 0.3s ease;
        }

        /* Validation Styles */
        .form-control.is-invalid {
            border-color: var(--danger-color);
            box-shadow: 0 0 0 0.2rem rgba(239, 68, 68, 0.25);
        }

        .form-check-input.is-invalid {
            border-color: var(--danger-color);
        }

        .invalid-feedback {
            display: block;
            color: var(--danger-color);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .form-label.required::after {
            content: " *";
            color: var(--danger-color);
            font-weight: bold;
        }

        /* Make required field labels red when validation fails */
        .form-control.is-invalid + .form-label.required,
        .form-check-input.is-invalid ~ .form-check-label.required {
            color: var(--danger-color);
            font-weight: bold;
        }

        /* Highlight required field labels in red when form has errors */
        .form-control.is-invalid ~ .form-label.required,
        .form-control.is-invalid + .form-label.required {
            color: var(--danger-color) !important;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .form-control.is-invalid {
            animation: shake 0.5s ease-in-out;
        }

        .form-text {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        .form-control.is-valid {
            border-color: var(--success-color);
            background-color: rgba(16, 185, 129, 0.1);
        }

        /* Validation Styles */
        .form-control.is-invalid {
            border-color: var(--danger-color);
            box-shadow: 0 0 0 0.2rem rgba(239, 68, 68, 0.25);
        }

        .form-check-input.is-invalid {
            border-color: var(--danger-color);
        }

        .invalid-feedback {
            display: block;
            color: var(--danger-color);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .form-label.required::after {
            content: " *";
            color: var(--danger-color);
            font-weight: bold;
        }

        /* Make required field labels red when validation fails */
        .form-control.is-invalid + .form-label.required,
        .form-check-input.is-invalid ~ .form-check-label.required {
            color: var(--danger-color);
            font-weight: bold;
        }

        /* Highlight required field labels in red when form has errors */
        .form-control.is-invalid ~ .form-label.required,
        .form-control.is-invalid + .form-label.required {
            color: var(--danger-color) !important;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .form-control.is-invalid {
            animation: shake 0.5s ease-in-out;
        }

        .form-text {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        .form-control.is-valid {
            border-color: var(--success-color);
            background-color: rgba(16, 185, 129, 0.1);
        }

        /* Enhanced Validation Styles */
        .form-control.is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
            background-color: #fff5f5;
        }
        .form-control.is-invalid:focus {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }
        .form-check-input.is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }
        .invalid-feedback {
            display: block !important;
            color: #dc3545;
            font-size: 0.875rem;
            font-weight: 500;
            margin-top: 0.25rem;
        }
        .form-label.required::after {
            content: " *";
            color: #dc3545;
            font-weight: bold;
        }
        /* Animate validation errors */
        .form-control.is-invalid {
            animation: shake 0.5s ease-in-out;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        /* Helpful text styling */
        .form-text {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        /* Success state for valid fields */
        .form-control.is-valid {
            border-color: #198754 !important;
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25) !important;
            background-color: #f8fff9;
        }
        .form-control.is-valid:focus {
            border-color: #198754 !important;
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25) !important;
        }

        /* Enhanced Validation Styles */
        .form-control.is-invalid {
            border-color: #dc3545 !important;
            border-width: 2px !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
            background-color: #fff5f5;
        }

        .form-control.is-invalid:focus {
            border-color: #dc3545 !important;
            border-width: 2px !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }

        .form-check-input.is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }

        .invalid-feedback {
            display: block !important;
            color: #dc3545;
            font-size: 0.875rem;
            font-weight: 500;
            margin-top: 0.25rem;
        }

        .form-label.required::after {
            content: " *";
            color: #dc3545;
            font-weight: bold;
        }

        /* Animate validation errors */
        .form-control.is-invalid {
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Helpful text styling */
        .form-text {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        /* Password strength indicator colors */
        .progress-bar.very-weak {
            background-color: #dc3545;
        }
        
        .progress-bar.weak {
            background-color: #fd7e14;
        }
        
        .progress-bar.medium {
            background-color: #ffc107;
        }
        
        .progress-bar.strong {
            background-color: #28a745;
        }
        
        .progress-bar.very-strong {
            background-color: #20c997;
        }
        
        /* Enhanced error summary styling */
        #errorSummary {
            border-left: 4px solid #dc3545;
            background-color: #fff5f5;
        }

        /* Success state for valid fields */
        .form-control.is-valid {
            border-color: #198754 !important;
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25) !important;
            background-color: #f8fff9;
        }

        .form-control.is-valid:focus {
            border-color: #198754 !important;
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25) !important;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .section-title {
                font-size: 2rem;
            }

            .modal-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-boxes me-2"></i>StoreAll.io
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#pricing">Pricing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#testimonials">Testimonials</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-primary" href="#" data-bs-toggle="modal" data-bs-target="#registerModal">Get Started</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="hero-title">Complete Storage Management Solution</h1>
                    <p class="hero-subtitle">
                        Streamline your storage operations with our comprehensive SaaS platform. 
                        Manage inventory, customers, and billing all in one place.
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="#" class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#registerModal">
                            <i class="fas fa-rocket me-2"></i>Start Free Trial
                        </a>
                        <a href="#features" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-play me-2"></i>Watch Demo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Happy Customers</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-number">50K+</div>
                        <div class="stat-label">Items Managed</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-number">99.9%</div>
                        <div class="stat-label">Uptime</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Support</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <h2 class="section-title">Why Choose StoreAll.io?</h2>
            <p class="section-subtitle">Everything you need to run your storage business efficiently</p>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <h4>Inventory Management</h4>
                        <p>Track every item with detailed records, photos, and status updates. Never lose track of customer belongings again.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Customer Portal</h4>
                        <p>Give customers access to their storage information, payments, and reservations through a secure online portal.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h4>Automated Billing</h4>
                        <p>Set up recurring payments, send invoices automatically, and track payment history with ease.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h4>Reservation System</h4>
                        <p>Manage storage reservations, check availability, and handle scheduling conflicts automatically.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>Analytics & Reports</h4>
                        <p>Get insights into your business performance with detailed reports and analytics dashboard.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4>Mobile Responsive</h4>
                        <p>Access your storage management system from any device with our fully responsive design.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-5 bg-light">
        <div class="container">
            <h2 class="section-title">Simple, Transparent Pricing</h2>
            <p class="section-subtitle">Choose the plan that fits your business needs</p>
            
            <div class="row g-4 justify-content-center">
                <div class="col-lg-4 col-md-6">
                    <div class="pricing-card">
                        <h4 class="text-center mb-3">Tier 1 - Inventory</h4>
                        <div class="text-center mb-4">
                            <span class="price">$29</span>
                            <span class="price-period">/month</span>
                        </div>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Basic inventory management</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Customer records</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Item tracking</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Basic reporting</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Email support</li>
                        </ul>
                        <div class="text-center mt-4">
                            <a href="#" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#registerModal">Get Started</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="pricing-card featured">
                        <div class="position-absolute top-0 start-50 translate-middle">
                            <span class="badge bg-primary px-3 py-2">Most Popular</span>
                        </div>
                        <h4 class="text-center mb-3">Tier 2 - Web Hosting</h4>
                        <div class="text-center mb-4">
                            <span class="price">$79</span>
                            <span class="price-period">/month</span>
                        </div>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Everything in Tier 1</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Customer portal</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Online reservations</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Web hosting included</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Priority support</li>
                        </ul>
                        <div class="text-center mt-4">
                            <a href="#" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#registerModal">Get Started</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="pricing-card">
                        <h4 class="text-center mb-3">Tier 3 - Full Billing</h4>
                        <div class="text-center mb-4">
                            <span class="price">$149</span>
                            <span class="price-period">/month</span>
                        </div>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Everything in Tier 2</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Automated billing</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Payment processing</li>
                            <li class="mb-2"><i class="mb-2"><i class="fas fa-check text-success me-2"></i>Advanced analytics</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>24/7 phone support</li>
                        </ul>
                        <div class="text-center mt-4">
                            <a href="#" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#registerModal">Get Started</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="py-5">
        <div class="container">
            <h2 class="section-title">What Our Customers Say</h2>
            <p class="section-subtitle">Join hundreds of satisfied storage business owners</p>
            
            <div class="row">
                <div class="col-lg-4">
                    <div class="testimonial-card">
                        <div class="testimonial-text">
                            "StoreAll.io has completely transformed how we manage our storage facility. The customer portal alone has reduced our admin time by 70%."
                        </div>
                        <div class="testimonial-author">- Sarah Johnson, StorageMax</div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="testimonial-card">
                        <div class="testimonial-text">
                            "The automated billing feature is a game-changer. No more chasing payments or manual invoicing. Highly recommended!"
                        </div>
                        <div class="testimonial-author">- Mike Chen, SecureStore</div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="testimonial-card">
                        <div class="testimonial-text">
                            "Customer support is exceptional. The team helped us migrate from our old system seamlessly. Couldn't be happier!"
                        </div>
                        <div class="testimonial-author">- Lisa Rodriguez, BoxIt Storage</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="get-started" class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="mb-4">Ready to Transform Your Storage Business?</h2>
            <p class="mb-4">Start your free trial today and see the difference StoreAll.io can make.</p>
            <a href="#" class="btn btn-light btn-lg me-3" data-bs-toggle="modal" data-bs-target="#registerModal">
                <i class="fas fa-rocket me-2"></i>Start Free Trial
            </a>
            <a href="#contact" class="btn btn-outline-light btn-lg">
                <i class="fas fa-phone me-2"></i>Contact Sales
            </a>
        </div>
    </section>

    <!-- Registration Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerModalLabel">
                        <i class="fas fa-user-plus me-2"></i>Create Your StoreAll.io Account
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="errorSummary" class="alert alert-danger mb-3" style="display: none;"></div>
                    <form id="registrationForm" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label required">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" required>
                                <div class="invalid-feedback" id="firstNameError"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label required">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" required>
                                <div class="invalid-feedback" id="lastNameError"></div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label required">Email Address</label>
                            <input type="text" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback" id="emailError"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirmEmail" class="form-label required">Confirm Email Address</label>
                            <input type="text" class="form-control" id="confirmEmail" name="confirmEmail" required autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');">
                            <div class="invalid-feedback" id="confirmEmailError"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="companyName" class="form-label required">Company Name</label>
                            <input type="text" class="form-control" id="companyName" name="companyName" required>
                            <div class="invalid-feedback" id="companyNameError"></div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="555-123-4567" maxlength="12">
                                <div class="form-text">Format: 555-123-4567</div>
                                <div class="invalid-feedback" id="phoneError"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="website" class="form-label">Website (Optional)</label>
                                <input type="text" class="form-control" id="website" name="website" placeholder="https://example.com">
                                <div class="form-text">Include https:// or we'll add it automatically</div>
                                <div class="invalid-feedback" id="websiteError"></div>
                            </div>
                        </div>
                        

                        
                        <div class="mb-3">
                            <label for="password" class="form-label required">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password">
                            <div class="progress mt-2" style="height: 8px;">
                                <div id="passwordStrengthBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <div class="form-text" id="passwordStrengthText">Password strength: Very Weak</div>
                            <div class="form-text" id="passwordRequirements">
                                <small>Requirements: 12+ characters, uppercase, lowercase, number, symbol</small>
                            </div>
                            <div class="invalid-feedback" id="passwordError"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label required">Confirm Password</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required autocomplete="new-password">
                            <div class="invalid-feedback" id="confirmPasswordError"></div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label required" for="terms">
                                I agree to the <a href="#" class="login-link">Terms of Service</a> and <a href="#" class="login-link">Privacy Policy</a>
                            </label>
                            <div class="invalid-feedback" id="termsError"></div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="newsletter" name="newsletter">
                            <label class="form-check-label" for="newsletter">
                                Send me product updates and storage industry insights
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="registrationForm" class="btn btn-register">
                        <i class="fas fa-rocket me-2"></i>Create Free Account
                    </button>
                    <div class="w-100 mt-3 text-center">
                        <small class="text-muted">
                            Already have an account? <a href="#" class="login-link" data-bs-toggle="modal" data-bs-target="#loginModal">Sign in here</a>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal (Placeholder) -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In to StoreAll.io
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-center text-muted">Login functionality coming soon!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5><i class="fas fa-boxes me-2"></i>StoreAll.io</h5>
                    <p>Complete storage management solution for modern businesses. Streamline operations, improve customer experience, and grow your revenue.</p>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Product</h5>
                    <ul class="list-unstyled">
                        <li><a href="#features">Features</a></li>
                        <li><a href="#pricing">Pricing</a></li>
                        <li><a href="#">API</a></li>
                        <li><a href="#">Integrations</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Company</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">About</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Press</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Support</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Contact</a></li>
                        <li><a href="#">Status</a></li>
                        <li><a href="#">Documentation</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Legal</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">Privacy</a></li>
                        <li><a href="#">Terms</a></li>
                        <li><a href="#">Security</a></li>
                        <li><a href="#">GDPR</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2024 StoreAll.io. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="social-links">
                        <a href="#" class="me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Client-side error logging
        window.addEventListener('error', function(e) {
            logClientError('javascript', e.message, {
                file: e.filename,
                line: e.lineno,
                column: e.colno,
                stack: e.error ? e.error.stack : null,
                url: window.location.href,
                referrer: document.referrer
            });
        });
        
        // Log unhandled promise rejections
        window.addEventListener('unhandledrejection', function(e) {
            logClientError('javascript', 'Unhandled Promise Rejection: ' + e.reason, {
                stack: e.reason && e.reason.stack ? e.reason.stack : null,
                url: window.location.href,
                referrer: document.referrer
            });
        });
        
        // Function to log client errors to server
        function logClientError(errorType, errorMessage, additionalData) {
            fetch('/api/log-client-error.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    error_type: errorType,
                    error_message: errorMessage,
                    additional_data: additionalData
                })
            }).catch(function(err) {
                console.error('Failed to log client error:', err);
            });
        }
        console.log('JavaScript is loading...');
        
        // Password strength functions
        function checkPasswordStrength(password) {
            let score = 0;
            let requirements = [];
            
            // Length check
            if (password.length >= 12) {
                score += 2;
            } else {
                requirements.push('12+ characters');
            }
            
            // Character type checks
            if (/[A-Z]/.test(password)) {
                score += 1;
            } else {
                requirements.push('uppercase letter');
            }
            
            if (/[a-z]/.test(password)) {
                score += 1;
            } else {
                requirements.push('lowercase letter');
            }
            
            if (/\d/.test(password)) {
                score += 1;
            } else {
                requirements.push('number');
            }
            
            if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
                score += 1;
            } else {
                requirements.push('symbol');
            }
            
            return { score, requirements };
        }
        
        function updatePasswordStrength() {
            console.log('updatePasswordStrength called');
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('passwordStrengthBar');
            const strengthText = document.getElementById('passwordStrengthText');
            const requirements = document.getElementById('passwordRequirements');
            
            console.log('Password value:', password);
            console.log('Strength bar found:', !!strengthBar);
            console.log('Strength text found:', !!strengthText);
            console.log('Requirements found:', !!requirements);
            
            if (!password) {
                strengthBar.style.width = '0%';
                strengthBar.className = 'progress-bar';
                strengthText.textContent = 'Password strength: Very Weak';
                requirements.innerHTML = '<small>Requirements: 12+ characters, uppercase, lowercase, number, symbol</small>';
                return;
            }
            
            const { score, requirements: missingRequirements } = checkPasswordStrength(password);
            
            let strength, width, colorClass;
            
            if (score === 0) {
                strength = 'Very Weak';
                width = '10%';
                colorClass = 'very-weak';
            } else if (score <= 2) {
                strength = 'Weak';
                width = '25%';
                colorClass = 'weak';
            } else if (score <= 3) {
                strength = 'Medium';
                width = '50%';
                colorClass = 'medium';
            } else if (score <= 4) {
                strength = 'Strong';
                width = '75%';
                colorClass = 'strong';
            } else {
                strength = 'Very Strong';
                width = '100%';
                colorClass = 'very-strong';
            }
            
            strengthBar.style.width = width;
            strengthBar.className = `progress-bar ${colorClass}`;
            strengthText.textContent = `Password strength: ${strength}`;
            
            if (missingRequirements.length > 0) {
                requirements.innerHTML = `<small>Missing: ${missingRequirements.join(', ')}</small>`;
            } else {
                requirements.innerHTML = '<small class="text-success">✓ All requirements met!</small>';
            }
        }
        
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const confirmField = document.getElementById('confirmPassword');
            const confirmError = document.getElementById('confirmPasswordError');
            
            if (confirmPassword && password !== confirmPassword) {
                confirmField.classList.add('is-invalid');
                confirmError.textContent = 'Passwords do not match';
                confirmError.style.display = 'block';
            } else if (confirmPassword) {
                confirmField.classList.remove('is-invalid');
                confirmField.classList.add('is-valid');
                confirmError.style.display = 'none';
            }
        }
        

        
        // Function to show field error
        function showError(fieldName, message) {
            console.log('showError called for:', fieldName, 'with message:', message);
            const field = document.getElementById(fieldName);
            const errorDiv = document.getElementById(fieldName + 'Error');
            
            console.log('Field found:', !!field);
            console.log('Error div found:', !!errorDiv);
            
            if (field && errorDiv) {
                field.classList.add('is-invalid');
                errorDiv.textContent = message;
                errorDiv.style.display = 'block';
                
                // Find the label (it might not be the previous sibling)
                const label = field.closest('.mb-3').querySelector('label[for="' + fieldName + '"]');
                if (label && label.classList.contains('required')) {
                    label.classList.add('error');
                    console.log('Label made red for:', fieldName);
                }
                
                console.log('Error applied successfully for:', fieldName);
            } else {
                console.log('ERROR: Field or errorDiv not found for:', fieldName);
            }
        }
        
        // Function to clear all errors
        function clearErrors() {
            const fields = ['firstName', 'lastName', 'email', 'confirmEmail', 'companyName', 'phone', 'website', 'password', 'confirmPassword', 'terms'];
            
            fields.forEach(fieldName => {
                const field = document.getElementById(fieldName);
                const errorDiv = document.getElementById(fieldName + 'Error');
                const label = field.previousElementSibling;
                
                field.classList.remove('is-invalid');
                errorDiv.style.display = 'none';
                
                if (label) {
                    label.classList.remove('error');
                }
            });
            
            document.getElementById('errorSummary').style.display = 'none';
        }
        
        // Function to show error summary
        function showErrorSummary(errors) {
            const summary = document.getElementById('errorSummary');
            summary.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Please correct the following ${errors.length} error${errors.length > 1 ? 's' : ''}:</strong>
                </div>
                <ul class="mb-0 mt-2">
                    ${errors.map(error => `<li>${error}</li>`).join('')}
                </ul>
            `;
            summary.style.display = 'block';
            
            // Scroll to the first error field
            if (errorFields.length > 0) {
                const firstErrorField = document.getElementById(errorFields[0]);
                if (firstErrorField) {
                    firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstErrorField.focus();
                }
            }
        }
        
        // Phone number formatting function
        function formatPhoneNumber(input) {
            // Remove all non-digits
            let value = input.value.replace(/\D/g, '');
            
            // Limit to 10 digits
            value = value.substring(0, 10);
            
            // Format as XXX-XXX-XXXX
            if (value.length >= 6) {
                value = value.substring(0, 3) + '-' + value.substring(3, 6) + '-' + value.substring(6);
            } else if (value.length >= 3) {
                value = value.substring(0, 3) + '-' + value.substring(3);
            }
            
            input.value = value;
        }
        
        // Email availability checker
        function checkEmailAvailability(email, field) {
            const formData = new FormData();
            formData.append('email', email);
            
            fetch('api/check-email.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Remove loading state
                field.classList.remove('is-loading');
                
                if (data.success) {
                    if (data.available) {
                        // Email is available
                        field.classList.add('is-valid');
                        field.classList.remove('is-invalid');
                        
                        // Show success message
                        const errorDiv = document.getElementById('emailError');
                        if (errorDiv) {
                            errorDiv.textContent = '✅ ' + data.message;
                            errorDiv.className = 'error-message text-success';
                            errorDiv.style.display = 'block';
                        }
                    } else {
                        // Email is not available
                        field.classList.add('is-invalid');
                        field.classList.remove('is-valid');
                        
                        // Show error message
                        const errorDiv = document.getElementById('emailError');
                        if (errorDiv) {
                            errorDiv.textContent = '❌ ' + data.message;
                            errorDiv.className = 'error-message text-danger';
                            errorDiv.style.display = 'block';
                        }
                        
                        // If email is verified, suggest login
                        if (data.email_verified) {
                            const loginSuggestion = document.createElement('div');
                            loginSuggestion.className = 'text-info mt-1';
                            loginSuggestion.innerHTML = '<small><i class="fas fa-info-circle"></i> <a href="#" onclick="showLoginModal()">Click here to log in</a> instead</small>';
                            
                            const errorDiv = document.getElementById('emailError');
                            if (errorDiv && !errorDiv.nextElementSibling?.classList.contains('text-info')) {
                                errorDiv.parentNode.insertBefore(loginSuggestion, errorDiv.nextSibling);
                            }
                        }
                    }
                } else {
                    // Error checking email
                    field.classList.add('is-invalid');
                    field.classList.remove('is-valid');
                    
                    const errorDiv = document.getElementById('emailError');
                    if (errorDiv) {
                        errorDiv.textContent = '⚠️ ' + data.message;
                        errorDiv.className = 'error-message text-warning';
                        errorDiv.style.display = 'block';
                    }
                }
            })
            .catch(error => {
                console.error('Email check error:', error);
                field.classList.remove('is-loading');
                
                // Show generic error
                const errorDiv = document.getElementById('emailError');
                if (errorDiv) {
                    errorDiv.textContent = '⚠️ Unable to check email availability';
                    errorDiv.className = 'error-message text-warning';
                    errorDiv.style.display = 'block';
                }
            });
        }
        
        // Add event listeners when modal is shown
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded');
            
            // Form submission handler
            const registrationForm = document.getElementById('registrationForm');
            if (registrationForm) {
                registrationForm.addEventListener('submit', function(e) {
                    console.log('Form submission started');
                    e.preventDefault();
                    
                    console.log('Starting validation...');
                    
                    // Clear previous errors
                    clearErrors();
                    
                    // Get form data
                    const formData = new FormData(this);
                    const firstName = formData.get('firstName').trim();
                    const lastName = formData.get('lastName').trim();
                    const email = formData.get('email').trim();
                    const confirmEmail = formData.get('confirmEmail').trim();
                    const companyName = formData.get('companyName').trim();
                    const phone = formData.get('phone').trim();
                    const website = formData.get('website').trim();
                    const password = formData.get('password');
                    const confirmPassword = formData.get('confirmPassword');
                    const terms = formData.get('terms');
                    
                    let errors = [];
                    let errorFields = [];
                    
                    // Validate First Name
                    console.log('Validating firstName:', firstName);
                    if (!firstName) {
                        console.log('firstName validation failed');
                        showError('firstName', 'First name is required');
                        errors.push('First Name');
                        errorFields.push('firstName');
                    }
                    
                    // Validate Last Name
                    console.log('Validating lastName:', lastName);
                    if (!lastName) {
                        console.log('lastName validation failed');
                        showError('lastName', 'Last name is required');
                        errors.push('Last Name');
                        errorFields.push('lastName');
                    }
                    
                    // Validate Company Name
                    console.log('Validating companyName:', companyName);
                    if (!companyName) {
                        console.log('companyName validation failed');
                        showError('companyName', 'Company name is required');
                        errors.push('Company Name');
                        errorFields.push('companyName');
                    }
                    
                    // Validate Email
                    if (!email) {
                        showError('email', 'Email address is required');
                        errors.push('Email Address');
                        errorFields.push('email');
                    } else {
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(email.toLowerCase())) {
                            showError('email', 'Please enter a valid email address');
                            errors.push('Email Address');
                            errorFields.push('email');
                        }
                    }
                    
                    // Validate Confirm Email
                    if (!confirmEmail) {
                        showError('confirmEmail', 'Please confirm your email address');
                        errors.push('Confirm Email Address');
                        errorFields.push('confirmEmail');
                    } else if (email.trim().toLowerCase() !== confirmEmail.trim().toLowerCase()) {
                        showError('confirmEmail', 'Email addresses do not match');
                        errors.push('Confirm Email Address');
                        errorFields.push('confirmEmail');
                    }
                    
                    // Validate Phone (if provided)
                    if (phone) {
                        const phoneRegex = /^\d{3}-\d{3}-\d{4}$/;
                        if (!phoneRegex.test(phone)) {
                            showError('phone', 'Please enter phone number in format: 555-123-4567');
                            errors.push('Phone Number');
                            errorFields.push('phone');
                        }
                    }
                    
                    // Validate Website (if provided)
                    if (website) {
                        try {
                            let url = website;
                            if (!url.startsWith('http://') && !url.startsWith('https://')) {
                                url = 'https://' + url;
                            }
                            new URL(url);
                        } catch {
                            showError('website', 'Please enter a valid website URL');
                            errors.push('Website');
                            errorFields.push('website');
                        }
                    }
                    
                    // Validate Password
                    if (!password) {
                        showError('password', 'Password is required');
                        errors.push('Password');
                        errorFields.push('password');
                    } else {
                        const { score, requirements } = checkPasswordStrength(password);
                        if (score < 5) {
                            showError('password', 'Password must be at least 12 characters with uppercase, lowercase, number, and symbol');
                            errors.push('Password');
                            errorFields.push('password');
                        }
                    }
                    
                    // Validate Confirm Password
                    if (!confirmPassword) {
                        showError('confirmPassword', 'Please confirm your password');
                        errors.push('Confirm Password');
                        errorFields.push('confirmPassword');
                    } else if (password !== confirmPassword) {
                        showError('confirmPassword', 'Passwords do not match');
                        errors.push('Confirm Password');
                        errorFields.push('confirmPassword');
                    }
                    
                    // Validate Terms
                    if (!terms) {
                        showError('terms', 'You must agree to the Terms of Service and Privacy Policy');
                        errors.push('Terms of Service agreement');
                        errorFields.push('terms');
                    }
                    
                    // Show error summary if there are errors
                    console.log('Total errors found:', errors.length);
                    console.log('Error fields:', errorFields);
                    console.log('Error messages:', errors);
                    
                    if (errors.length > 0) {
                        showErrorSummary(errors);
                        return;
                    }
                    
                    // Submit form to server
                    console.log('Form validation passed, submitting to server...');
                    
                    // Show loading state
                    const submitBtn = document.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Account...';
                    submitBtn.disabled = true;
                    
                    fetch('api/register.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        console.log('Response headers:', response.headers);
                        return response.text().then(text => {
                            console.log('Raw response text:', text);
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                console.error('JSON parse error:', e);
                                throw new Error('Invalid JSON response: ' + text);
                            }
                        });
                    })
                    .then(data => {
                        console.log('Server response:', data);
                        if (data.success) {
                            // Show success message
                            alert(data.message);
                            
                            // Close the modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
                            modal.hide();
                            
                            // Reset the form
                            this.reset();
                            
                            // Reset password strength indicator
                            document.getElementById('passwordStrengthBar').style.width = '0%';
                            document.getElementById('passwordStrengthText').textContent = 'Password strength: Very Weak';
                            
                            console.log('Registration successful:', data.data);
                        } else {
                            // Show server-side validation errors
                            if (data.errors) {
                                Object.keys(data.errors).forEach(fieldName => {
                                    showError(fieldName, data.errors[fieldName]);
                                });
                            }
                            
                            // Show general error message
                            if (data.message) {
                                alert(data.message);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Registration error:', error);
                        alert('Registration failed. Please try again or contact support.');
                    })
                    .finally(() => {
                        // Reset button state
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
                });
                console.log('Form submission listener added');
            }
            
            const registerModal = document.getElementById('registerModal');
            if (registerModal) {
                registerModal.addEventListener('shown.bs.modal', function() {
                    console.log('Modal shown, adding listeners');
                    
                    // Password strength listener
                    const passwordField = document.getElementById('password');
                    if (passwordField) {
                        passwordField.addEventListener('input', updatePasswordStrength);
                        console.log('Password listener added');
                    }
                    
                    // Phone formatting listener
                    const phoneField = document.getElementById('phone');
                    if (phoneField) {
                        phoneField.addEventListener('input', function() {
                            formatPhoneNumber(this);
                        });
                        console.log('Phone listener added');
                    }
                    
                    // Real-time validation clearing
                    const formFields = ['firstName', 'lastName', 'email', 'confirmEmail', 'companyName', 'phone', 'website', 'password', 'confirmPassword'];
                    formFields.forEach(fieldName => {
                        const field = document.getElementById(fieldName);
                        if (field) {
                            field.addEventListener('input', function() {
                                // Clear error for this field when user starts typing
                                const errorDiv = document.getElementById(fieldName + 'Error');
                                if (errorDiv) {
                                    errorDiv.style.display = 'none';
                                }
                                this.classList.remove('is-invalid');
                                
                                // Clear error summary if all fields are valid
                                const allFieldsValid = formFields.every(field => {
                                    const f = document.getElementById(field);
                                    return !f.classList.contains('is-invalid');
                                });
                                if (allFieldsValid) {
                                    document.getElementById('errorSummary').style.display = 'none';
                                }
                            });
                        }
                    });
                    
                    // Real-time email availability checking
                    const emailField = document.getElementById('email');
                    if (emailField) {
                        let emailCheckTimeout;
                        
                        emailField.addEventListener('input', function() {
                            const email = this.value.trim();
                            
                            // Clear previous timeout
                            if (emailCheckTimeout) {
                                clearTimeout(emailCheckTimeout);
                            }
                            
                            // Clear previous email status
                            const errorDiv = document.getElementById('emailError');
                            if (errorDiv) {
                                errorDiv.style.display = 'none';
                            }
                            this.classList.remove('is-invalid', 'is-valid');
                            
                            // Only check if email is valid format and not empty
                            if (email && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                                // Add loading indicator
                                this.classList.add('is-loading');
                                
                                // Debounce the check (wait 500ms after user stops typing)
                                emailCheckTimeout = setTimeout(() => {
                                    checkEmailAvailability(email, this);
                                }, 500);
                            }
                        });
                    }
                    
                    // Terms checkbox validation clearing
                    const termsField = document.getElementById('terms');
                    if (termsField) {
                        termsField.addEventListener('change', function() {
                            if (this.checked) {
                                const errorDiv = document.getElementById('termsError');
                                if (errorDiv) {
                                    errorDiv.style.display = 'none';
                                }
                                this.classList.remove('is-invalid');
                            }
                        });
                    }
                });
                console.log('Modal event listener added');
            }
        });
    </script>
</body>
</html>
