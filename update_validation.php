<?php
// Read the current index.php file
$content = file_get_contents('index.php');

// Replace the old validation logic with the enhanced version
$oldValidation = 'let hasErrors = false;
            
            // Validate First Name
            if (!firstName) {
                showFieldError(\'firstName\', \'First name is required\');
                hasErrors = true;
            }
            
            // Validate Last Name
            if (!lastName) {
                showFieldError(\'lastName\', \'Last name is required\');
                hasErrors = true;
            }
            
            // Validate Company Name
            if (!companyName) {
                showFieldError(\'companyName\', \'Company name is required\');
                hasErrors = true;
            }
            
            // Validate Email
            if (!email) {
                showFieldError(\'email\', \'Email address is required\');
                hasErrors = true;
            } else {
                const emailRegex = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/;
                if (!emailRegex.test(email)) {
                    showFieldError(\'email\', \'Please enter a valid email address\');
                    hasErrors = true;
                }
            }
            
            // Validate Confirm Email
            if (!confirmEmail) {
                showFieldError(\'confirmEmail\', \'Please confirm your email address\');
                hasErrors = true;
            } else if (email !== confirmEmail) {
                showFieldError(\'confirmEmail\', \'Email addresses do not match\');
                hasErrors = true;
            }
            
            // Validate Phone (if provided)
            if (phone) {
                const phoneRegex = /^[\\+]?[1-9][\\d]{0,15}$/;
                if (!phoneRegex.test(phone.replace(/[\\s\\-\\(\\)]/g, \'\'))) {
                    showFieldError(\'phone\', \'Please enter a valid phone number\');
                    hasErrors = true;
                }
            }
            
            // Validate Website (if provided)
            if (website) {
                try {
                    new URL(website);
                } catch {
                    showFieldError(\'website\', \'Please enter a valid website URL\');
                    hasErrors = true;
                }
            }
            
            // Validate Password
            if (!password) {
                showFieldError(\'password\', \'Password is required\');
                hasErrors = true;
            } else {
                const { strength } = checkPasswordStrength(password);
                if (strength < 100) {
                    showFieldError(\'password\', \'Password must be at least 12 characters with uppercase, lowercase, number, and symbol\');
                    hasErrors = true;
                }
            }
            
            // Validate Confirm Password
            if (!confirmPassword) {
                showFieldError(\'confirmPassword\', \'Please confirm your password\');
                hasErrors = true;
            } else if (password !== confirmPassword) {
                showFieldError(\'confirmPassword\', \'Passwords do not match\');
                hasErrors = true;
            }
            
            // Validate Terms
            if (!terms) {
                showFieldError(\'terms\', \'You must agree to the Terms of Service and Privacy Policy\');
                hasErrors = true;
            }
            
            // If there are errors, stop submission
            if (hasErrors) {
                return;
            }';

$newValidation = 'let hasErrors = false;
            let errorFields = [];
            
            // Validate First Name
            if (!firstName) {
                showFieldError(\'firstName\', \'First name is required\');
                hasErrors = true;
                errorFields.push(\'firstName\');
            }
            
            // Validate Last Name
            if (!lastName) {
                showFieldError(\'lastName\', \'Last name is required\');
                hasErrors = true;
                errorFields.push(\'lastName\');
            }
            
            // Validate Company Name
            if (!companyName) {
                showFieldError(\'companyName\', \'Company name is required\');
                hasErrors = true;
                errorFields.push(\'companyName\');
            }
            
            // Validate Email
            if (!email) {
                showFieldError(\'email\', \'Email address is required\');
                hasErrors = true;
                errorFields.push(\'email\');
            } else {
                const emailRegex = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/;
                if (!emailRegex.test(email)) {
                    showFieldError(\'email\', \'Please enter a valid email address\');
                    hasErrors = true;
                    errorFields.push(\'email\');
                }
            }
            
            // Validate Confirm Email
            if (!confirmEmail) {
                showFieldError(\'confirmEmail\', \'Please confirm your email address\');
                hasErrors = true;
                errorFields.push(\'confirmEmail\');
            } else if (email !== confirmEmail) {
                showFieldError(\'confirmEmail\', \'Email addresses do not match\');
                hasErrors = true;
                errorFields.push(\'confirmEmail\');
            }
            
            // Validate Phone (if provided)
            if (phone) {
                const phoneRegex = /^[\\+]?[1-9][\\d]{0,15}$/;
                if (!phoneRegex.test(phone.replace(/[\\s\\-\\(\\)]/g, \'\'))) {
                    showFieldError(\'phone\', \'Please enter a valid phone number\');
                    hasErrors = true;
                    errorFields.push(\'phone\');
                }
            }
            
            // Validate Website (if provided)
            if (website) {
                try {
                    let url = website;
                    if (!url.startsWith(\'http://\') && !url.startsWith(\'https://\')) {
                        url = \'https://\' + url;
                    }
                    new URL(url);
                } catch {
                    showFieldError(\'website\', \'Please enter a valid website URL\');
                    hasErrors = true;
                    errorFields.push(\'website\');
                }
            }
            
            // Validate Password
            if (!password) {
                showFieldError(\'password\', \'Password is required\');
                hasErrors = true;
                errorFields.push(\'password\');
            } else {
                const { strength, feedback } = checkPasswordStrength(password);
                if (strength < 100) {
                    showFieldError(\'password\', \'Password must be at least 12 characters with uppercase, lowercase, number, and symbol\');
                    hasErrors = true;
                    errorFields.push(\'password\');
                }
            }
            
            // Validate Confirm Password
            if (!confirmPassword) {
                showFieldError(\'confirmPassword\', \'Please confirm your password\');
                hasErrors = true;
                errorFields.push(\'confirmPassword\');
            } else if (password !== confirmPassword) {
                showFieldError(\'confirmPassword\', \'Passwords do not match\');
                hasErrors = true;
                errorFields.push(\'confirmPassword\');
            }
            
            // Validate Terms
            if (!terms) {
                showFieldError(\'terms\', \'You must agree to the Terms of Service and Privacy Policy\');
                hasErrors = true;
                errorFields.push(\'terms\');
            }
            
            // If there are errors, show summary and stop submission
            if (hasErrors) {
                showErrorSummary(errorFields);
                return;
            }';

// Replace the validation logic
$content = str_replace($oldValidation, $newValidation, $content);

// Add the missing functions
$oldClearErrors = '        // Function to clear all errors
        function clearAllErrors() {
            const fields = [\'firstName\', \'lastName\', \'email\', \'confirmEmail\', \'companyName\', \'phone\', \'website\', \'password\', \'confirmPassword\', \'terms\'];
            
            fields.forEach(fieldName => {
                const field = document.getElementById(fieldName);
                const errorDiv = document.getElementById(fieldName + \'Error\');
                
                if (field) {
                    field.classList.remove(\'is-invalid\');
                }
                if (errorDiv) {
                    errorDiv.style.display = \'none\';
                }
            });
        }';

$newClearErrors = '        // Function to clear all errors
        function clearAllErrors() {
            const fields = [\'firstName\', \'lastName\', \'email\', \'confirmEmail\', \'companyName\', \'phone\', \'website\', \'password\', \'confirmPassword\', \'terms\'];
            
            fields.forEach(fieldName => {
                const field = document.getElementById(fieldName);
                const errorDiv = document.getElementById(fieldName + \'Error\');
                
                if (field) {
                    field.classList.remove(\'is-invalid\', \'is-valid\');
                }
                if (errorDiv) {
                    errorDiv.style.display = \'none\';
                }
            });
            
            // Remove error summary if it exists
            const existingSummary = document.getElementById(\'errorSummary\');
            if (existingSummary) {
                existingSummary.remove();
            }
        }
        
        // Function to show error summary
        function showErrorSummary(errorFields) {
            // Remove existing summary if it exists
            const existingSummary = document.getElementById(\'errorSummary\');
            if (existingSummary) {
                existingSummary.remove();
            }
            
            // Create error summary
            const summaryDiv = document.createElement(\'div\');
            summaryDiv.id = \'errorSummary\';
            summaryDiv.className = \'alert alert-danger mb-3\';
            summaryDiv.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Please correct the following ${errorFields.length} error${errorFields.length > 1 ? \'s\' : \'\'}:</strong>
                </div>
                <ul class="mb-0 mt-2">
                    ${errorFields.map(field => {
                        const fieldLabel = getFieldLabel(field);
                        return `<li>${fieldLabel}</li>`;
                    }).join(\'\')}
                </ul>
            `;
            
            // Insert at the top of the form
            const form = document.getElementById(\'registrationForm\');
            form.insertBefore(summaryDiv, form.firstChild);
            
            // Scroll to the first error field
            if (errorFields.length > 0) {
                const firstErrorField = document.getElementById(errorFields[0]);
                if (firstErrorField) {
                    firstErrorField.scrollIntoView({ behavior: \'smooth\', block: \'center\' });
                    firstErrorField.focus();
                }
            }
        }
        
        // Function to get field labels
        function getFieldLabel(fieldName) {
            const labels = {
                \'firstName\': \'First Name\',
                \'lastName\': \'Last Name\',
                \'email\': \'Email Address\',
                \'confirmEmail\': \'Confirm Email Address\',
                \'companyName\': \'Company Name\',
                \'phone\': \'Phone Number\',
                \'website\': \'Website\',
                \'password\': \'Password\',
                \'confirmPassword\': \'Confirm Password\',
                \'terms\': \'Terms of Service agreement\'
            };
            return labels[fieldName] || fieldName;
        }';

// Replace the clear errors function
$content = str_replace($oldClearErrors, $newClearErrors, $content);

// Write the updated content back to the file
file_put_contents('index.php', $content);

echo "Validation logic updated successfully!\n";
?>
