<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StoreAll.io - Clean Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .progress-bar.very-weak { background-color: #dc3545; }
        .progress-bar.weak { background-color: #fd7e14; }
        .progress-bar.medium { background-color: #ffc107; }
        .progress-bar.strong { background-color: #28a745; }
        .progress-bar.very-strong { background-color: #20c997; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>StoreAll.io - Clean Test</h1>
        
        <!-- Registration Modal -->
        <div class="modal fade" id="registerModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Your StoreAll.io Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="registrationForm">
                            <div class="mb-3">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="progress mt-2" style="height: 8px;">
                                    <div id="passwordStrengthBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <div class="form-text" id="passwordStrengthText">Password strength: Very Weak</div>
                                <div class="form-text" id="passwordRequirements">
                                    <small>Requirements: 12+ characters, uppercase, lowercase, number, symbol</small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Test Registration</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerModal">
            Open Registration Modal
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        console.log('Clean JavaScript is loading...');
        
        // Password strength functions
        function checkPasswordStrength(password) {
            let score = 0;
            let requirements = [];
            
            if (password.length >= 12) {
                score += 2;
            } else {
                requirements.push('12+ characters');
            }
            
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
        
        // Add event listeners when modal is shown
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded');
            
            const registerModal = document.getElementById('registerModal');
            if (registerModal) {
                registerModal.addEventListener('shown.bs.modal', function() {
                    console.log('Modal shown, adding password listener');
                    const passwordField = document.getElementById('password');
                    if (passwordField) {
                        passwordField.addEventListener('input', updatePasswordStrength);
                        console.log('Password listener added');
                    }
                });
                console.log('Modal event listener added');
            }
        });
    </script>
</body>
</html>
