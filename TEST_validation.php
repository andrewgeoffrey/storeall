<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Validation Test</h2>
        <form id="testForm">
            <div class="mb-3">
                <label for="firstName" class="form-label required">First Name</label>
                <input type="text" class="form-control" id="firstName" name="firstName">
                <div class="invalid-feedback" id="firstNameError"></div>
            </div>
            <div class="mb-3">
                <label for="lastName" class="form-label required">Last Name</label>
                <input type="text" class="form-control" id="lastName" name="lastName">
                <div class="invalid-feedback" id="lastNameError"></div>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label required">Email</label>
                <input type="email" class="form-control" id="email" name="email">
                <div class="invalid-feedback" id="emailError"></div>
            </div>
            <button type="submit" class="btn btn-primary">Test Validation</button>
        </form>
    </div>

    <script>
        document.getElementById('testForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Clear all previous errors
            clearAllErrors();
            
            let hasErrors = false;
            let errorFields = [];
            
            // Get form data
            const firstName = document.getElementById('firstName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            const email = document.getElementById('email').value.trim();
            
            // Validate First Name
            if (!firstName) {
                showFieldError('firstName', 'First name is required');
                hasErrors = true;
                errorFields.push('firstName');
            }
            
            // Validate Last Name
            if (!lastName) {
                showFieldError('lastName', 'Last name is required');
                hasErrors = true;
                errorFields.push('lastName');
            }
            
            // Validate Email
            if (!email) {
                showFieldError('email', 'Email is required');
                hasErrors = true;
                errorFields.push('email');
            } else {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    showFieldError('email', 'Please enter a valid email address');
                    hasErrors = true;
                    errorFields.push('email');
                }
            }
            
            // If there are errors, show summary
            if (hasErrors) {
                showErrorSummary(errorFields);
                return;
            }
            
            alert('Form is valid!');
        });
        
        function showFieldError(fieldName, message) {
            const field = document.getElementById(fieldName);
            const errorDiv = document.getElementById(fieldName + 'Error');
            
            if (field && errorDiv) {
                field.classList.add('is-invalid');
                errorDiv.textContent = message;
                errorDiv.style.display = 'block';
            }
        }
        
        function clearAllErrors() {
            const fields = ['firstName', 'lastName', 'email'];
            
            fields.forEach(fieldName => {
                const field = document.getElementById(fieldName);
                const errorDiv = document.getElementById(fieldName + 'Error');
                
                if (field) {
                    field.classList.remove('is-invalid', 'is-valid');
                }
                if (errorDiv) {
                    errorDiv.style.display = 'none';
                }
            });
            
            // Remove error summary if it exists
            const existingSummary = document.getElementById('errorSummary');
            if (existingSummary) {
                existingSummary.remove();
            }
        }
        
        function showErrorSummary(errorFields) {
            // Remove existing summary if it exists
            const existingSummary = document.getElementById('errorSummary');
            if (existingSummary) {
                existingSummary.remove();
            }
            
            // Create error summary
            const summaryDiv = document.createElement('div');
            summaryDiv.id = 'errorSummary';
            summaryDiv.className = 'alert alert-danger mb-3';
            summaryDiv.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Please correct the following ${errorFields.length} error${errorFields.length > 1 ? 's' : ''}:</strong>
                </div>
                <ul class="mb-0 mt-2">
                    ${errorFields.map(field => {
                        const fieldLabel = getFieldLabel(field);
                        return `<li>${fieldLabel}</li>`;
                    }).join('')}
                </ul>
            `;
            
            // Insert at the top of the form
            const form = document.getElementById('testForm');
            form.insertBefore(summaryDiv, form.firstChild);
            
            // Scroll to the first error field
            if (errorFields.length > 0) {
                const firstErrorField = document.getElementById(errorFields[0]);
                if (firstErrorField) {
                    firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstErrorField.focus();
                }
            }
        }
        
        function getFieldLabel(fieldName) {
            const labels = {
                'firstName': 'First Name',
                'lastName': 'Last Name',
                'email': 'Email Address'
            };
            return labels[fieldName] || fieldName;
        }
    </script>
</body>
</html>

