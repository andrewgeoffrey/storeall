// Password Reset Page JavaScript
// Handles password strength validation and form submission

document.addEventListener('DOMContentLoaded', function() {
    setupPasswordValidation();
    setupFormSubmission();
});

// Setup password validation
function setupPasswordValidation() {
    const newPasswordInput = document.getElementById('newPassword');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            validatePassword(this.value);
            checkPasswordMatch();
        });
    }
    
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            checkPasswordMatch();
        });
    }
}

// Setup form submission
function setupFormSubmission() {
    const form = document.getElementById('resetPasswordForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            handleFormSubmission();
        });
    }
}

// Validate password strength
function validatePassword(password) {
    const requirements = {
        length: password.length >= 12,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[^A-Za-z0-9]/.test(password)
    };
    
    // Update requirement indicators
    updateRequirementIndicator('req-length', requirements.length);
    updateRequirementIndicator('req-uppercase', requirements.uppercase);
    updateRequirementIndicator('req-lowercase', requirements.lowercase);
    updateRequirementIndicator('req-number', requirements.number);
    updateRequirementIndicator('req-special', requirements.special);
    
    // Calculate strength score
    const score = Object.values(requirements).filter(Boolean).length;
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    
    if (strengthBar && strengthText) {
        let strength = 'weak';
        let color = '#dc3545';
        let width = 20;
        
        if (score >= 5) {
            strength = 'strong';
            color = '#28a745';
            width = 100;
        } else if (score >= 4) {
            strength = 'good';
            color = '#17a2b8';
            width = 80;
        } else if (score >= 3) {
            strength = 'medium';
            color = '#ffc107';
            width = 60;
        } else if (score >= 2) {
            strength = 'weak';
            color = '#fd7e14';
            width = 40;
        }
        
        strengthBar.style.width = width + '%';
        strengthBar.style.backgroundColor = color;
        strengthText.textContent = `Password strength: ${strength}`;
        strengthText.className = 'text-muted';
        
        if (strength === 'strong') {
            strengthText.className = 'text-success';
        } else if (strength === 'good') {
            strengthText.className = 'text-info';
        } else if (strength === 'medium') {
            strengthText.className = 'text-warning';
        } else if (strength === 'weak') {
            strengthText.className = 'text-danger';
        }
    }
    
    return score >= 5;
}

// Update requirement indicator
function updateRequirementIndicator(elementId, isMet) {
    const element = document.getElementById(elementId);
    if (element) {
        const icon = element.querySelector('i');
        if (icon) {
            if (isMet) {
                icon.className = 'fas fa-check text-success';
            } else {
                icon.className = 'fas fa-circle text-muted';
            }
        }
    }
}

// Check if passwords match
function checkPasswordMatch() {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const confirmInput = document.getElementById('confirmPassword');
    
    if (confirmPassword && newPassword !== confirmPassword) {
        confirmInput.classList.add('is-invalid');
        confirmInput.classList.remove('is-valid');
    } else if (confirmPassword && newPassword === confirmPassword) {
        confirmInput.classList.add('is-valid');
        confirmInput.classList.remove('is-invalid');
    } else {
        confirmInput.classList.remove('is-valid', 'is-invalid');
    }
}

// Handle form submission
async function handleFormSubmission() {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    // Validate inputs
    if (!newPassword || !confirmPassword) {
        showAlert('Please fill in all fields.', 'danger');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        showAlert('Passwords do not match.', 'danger');
        return;
    }
    
    if (!validatePassword(newPassword)) {
        showAlert('Password does not meet strength requirements.', 'danger');
        return;
    }
    
    // Show loading state
    showLoading(true);
    
    try {
        const form = document.getElementById('resetPasswordForm');
        const formData = new FormData(form);
        
        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            // Reload page to show success message
            window.location.reload();
        } else {
            showAlert('An error occurred while updating your password.', 'danger');
        }
        
    } catch (error) {
        console.error('Password reset error:', error);
        showAlert('An error occurred while updating your password.', 'danger');
    } finally {
        showLoading(false);
    }
}

// Show loading spinner
function showLoading(show) {
    const spinner = document.getElementById('loadingSpinner');
    const submitBtn = document.getElementById('submitBtn');
    
    if (show) {
        if (spinner) spinner.style.display = 'block';
        if (submitBtn) submitBtn.disabled = true;
    } else {
        if (spinner) spinner.style.display = 'none';
        if (submitBtn) submitBtn.disabled = false;
    }
}

// Show alert message
function showAlert(message, type) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create new alert
    const alertContainer = document.createElement('div');
    alertContainer.className = `alert alert-${type} alert-dismissible fade show`;
    alertContainer.setAttribute('role', 'alert');
    
    const iconClass = type === 'success' ? 'check-circle' : 
                     type === 'danger' ? 'exclamation-triangle' : 
                     type === 'warning' ? 'exclamation-circle' : 'info-circle';
    
    alertContainer.innerHTML = `
        <i class="fas fa-${iconClass}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert alert at the top of the login card
    const loginCard = document.querySelector('.login-card');
    if (loginCard) {
        loginCard.insertBefore(alertContainer, loginCard.firstChild);
    }
    
    // Auto-dismiss success messages after 5 seconds
    if (type === 'success') {
        setTimeout(() => {
            if (alertContainer.parentNode) {
                alertContainer.remove();
            }
        }, 5000);
    }
}

// Add additional CSS for password strength indicator
const style = document.createElement('style');
style.textContent = `
    .password-strength {
        margin: 15px 0;
    }
    
    .password-strength .progress {
        background-color: #e9ecef;
        border-radius: 3px;
    }
    
    .password-strength .progress-bar {
        transition: all 0.3s ease;
    }
    
    .password-requirements {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #667eea;
    }
    
    .password-requirements h6 {
        color: #495057;
        margin-bottom: 10px;
        font-weight: 600;
    }
    
    .password-requirements ul {
        margin: 0;
        padding: 0;
    }
    
    .password-requirements li {
        margin: 5px 0;
        color: #6c757d;
        font-size: 14px;
    }
    
    .password-requirements li i {
        width: 16px;
        margin-right: 8px;
    }
    
    .form-control.is-valid {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }
    
    .form-control.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }
`;
document.head.appendChild(style);
