<?php
// Simple admin landing page - no redirects
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Area - StoreAll.io</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Admin Area</h3>
                    </div>
                    <div class="card-body">
                        <h5>Welcome to the Admin Area</h5>
                        <p>Choose where you'd like to go:</p>
                        
                        <div class="d-grid gap-2">
                            <a href="/admin/dashboard/" class="btn btn-primary btn-lg">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Admin Dashboard
                            </a>
                            <a href="/login/" class="btn btn-outline-secondary">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Back to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
