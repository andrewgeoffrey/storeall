// Login Page JavaScript
// Handles login form submission, MFA verification, password reset, and environment tracking

// Global variables
let currentEmail = '';
let deviceFingerprint = '';
let environmentData = {};

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    collectEnvironmentData();
    setupEventListeners();
    displayDeviceInfo();
    
    // Check for URL parameters (for password reset links)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('reset') && urlParams.has('token')) {
        showPasswordReset();
    }
});

// Collect environment data for fingerprinting
function collectEnvironmentData() {
    environmentData = {
        screen_resolution: `${screen.width}x${screen.height}`,
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        language: navigator.language,
        platform: navigator.platform,
        cookie_enabled: navigator.cookieEnabled,
        user_agent: navigator.userAgent,
        color_depth: screen.colorDepth,
        pixel_ratio: window.devicePixelRatio,
        viewport: `${window.innerWidth}x${window.innerHeight}`,
        do_not_track: navigator.doNotTrack,
        hardware_concurrency: navigator.hardwareConcurrency,
        max_touch_points: navigator.maxTouchPoints,
        webgl_vendor: getWebGLVendor(),
        webgl_renderer: getWebGLRenderer(),
        canvas_fingerprint: getCanvasFingerprint()
    };
    
    // Generate device fingerprint
    const fingerprintData = JSON.stringify(environmentData);
    deviceFingerprint = btoa(fingerprintData).substring(0, 32);
}

// Get WebGL vendor information
function getWebGLVendor() {
    try {
        const canvas = document.createElement('canvas');
        const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
        if (gl) {
            return gl.getParameter(gl.VENDOR) || 'unknown';
        }
    } catch (e) {
        return 'unknown';
    }
    return 'unknown';
}

// Get WebGL renderer information
function getWebGLRenderer() {
    try {
        const canvas = document.createElement('canvas');
        const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
        if (gl) {
            return gl.getParameter(gl.RENDERER) || 'unknown';
        }
    } catch (e) {
        return 'unknown';
    }
    return 'unknown';
}

// Get canvas fingerprint
function getCanvasFingerprint() {
    try {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        ctx.textBaseline = 'top';
        ctx.font = '14px Arial';
        ctx.fillText('StoreAll.io Device Fingerprint', 2, 2);
        return canvas.toDataURL();
    } catch (e) {
        return 'unknown';
    }
}

// Display device information
function displayDeviceInfo() {
    const platformElement = document.getElementById('devicePlatform');
    const locationElement = document.getElementById('deviceLocation');
    const timezoneElement = document.getElementById('deviceTimezone');
    
    if (platformElement) {
        platformElement.textContent = environmentData.platform;
    }
    if (locationElement) {
        locationElement.textContent = environmentData.timezone;
    }
    if (timezoneElement) {
        timezoneElement.textContent = new Date().toLocaleTimeString();
    }
}

// Setup event listeners
function setupEventListeners() {
    // Remember device checkbox
    const rememberDeviceCheckbox = document.getElementById('rememberDevice');
    if (rememberDeviceCheckbox) {
        rememberDeviceCheckbox.addEventListener('change', function() {
            const deviceNameSection = document.getElementById('deviceNameSection');
            if (deviceNameSection) {
                deviceNameSection.style.display = this.checked ? 'block' : 'none';
            }
        });
    }
    
    // Login form submission
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    // MFA verification
    const verifyMfaBtn = document.getElementById('verifyMfaBtn');
    if (verifyMfaBtn) {
        verifyMfaBtn.addEventListener('click', handleMfaVerification);
    }
    
    const resendMfaBtn = document.getElementById('resendMfaBtn');
    if (resendMfaBtn) {
        resendMfaBtn.addEventListener('click', handleResendMfa);
    }
    
    // Password reset
    const sendResetBtn = document.getElementById('sendResetBtn');
    if (sendResetBtn) {
        sendResetBtn.addEventListener('click', handlePasswordReset);
    }
    
    const backToLoginBtn = document.getElementById('backToLoginBtn');
    if (backToLoginBtn) {
        backToLoginBtn.addEventListener('click', showLoginForm);
    }
    
    // MFA code input formatting
    const mfaCodeInput = document.getElementById('mfaCode');
    if (mfaCodeInput) {
        mfaCodeInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
        });
    }
}

// Handle login form submission
async function handleLogin(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const rememberDevice = document.getElementById('rememberDevice').checked;
    const deviceName = document.getElementById('deviceName').value.trim();
    
    if (!email || !password) {
        showAlert('Please fill in all required fields.', 'danger');
        return;
    }
    
    currentEmail = email;
    showLoading(true);
    
    try {
        const formData = new FormData();
        formData.append('email', email);
        formData.append('password', password);
        formData.append('remember_device', rememberDevice ? '1' : '0');
        formData.append('device_name', deviceName || 'Unknown Device');
        
        // Add environment data
        Object.keys(environmentData).forEach(key => {
            formData.append(key, environmentData[key]);
        });
        
        const response = await fetch('/api/login.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Login successful
            showAlert('Login successful! Redirecting...', 'success');
            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.classList.add('success-animation');
            }
            
            // Store session data
            localStorage.setItem('user_session', JSON.stringify(data.user));
            localStorage.setItem('session_id', data.session_id);
            
            // Redirect based on user role
            setTimeout(() => {
                if (data.user.role === 'admin' || data.user.role === 'super_user') {
                    window.location.href = '/admin/dashboard/';
                } else if (data.user.role === 'owner') {
                    if (data.user.organization_slug) {
                        window.location.href = '/' + data.user.organization_slug + '/manage/';
                    } else {
                        window.location.href = '/admin/dashboard/';
                    }
                } else if (data.user.role === 'customer') {
                    if (data.user.organization_slug) {
                        window.location.href = '/' + data.user.organization_slug + '/cust/';
                    } else {
                        window.location.href = '/';
                    }
                } else {
                    window.location.href = '/';
                }
            }, 2000);
            
        } else if (data.mfa_required) {
            // MFA required
            showMfaSection();
            showAlert('Please enter the verification code sent to your email.', 'info');
            
        } else if (data.email_verification_required) {
            // Email verification required
            showAlert('Please verify your email address before logging in. Check your email for a verification link.', 'warning');
            
        } else if (data.locked) {
            // Account locked
            showAlert(data.message, 'danger');
            
        } else {
            // Other errors
            showAlert(data.message || 'Login failed. Please try again.', 'danger');
            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.classList.add('error-shake');
                setTimeout(() => {
                    loginForm.classList.remove('error-shake');
                }, 500);
            }
        }
        
    } catch (error) {
        console.error('Login error:', error);
        showAlert('An error occurred during login. Please try again.', 'danger');
    } finally {
        showLoading(false);
    }
}

// Handle MFA verification
async function handleMfaVerification() {
    const mfaCode = document.getElementById('mfaCode').value.trim();
    
    if (!mfaCode || mfaCode.length !== 6) {
        showAlert('Please enter a valid 6-digit verification code.', 'danger');
        return;
    }
    
    showLoading(true);
    
    try {
        const formData = new FormData();
        formData.append('email', currentEmail);
        formData.append('mfa_code', mfaCode);
        
        // Add environment data
        Object.keys(environmentData).forEach(key => {
            formData.append(key, environmentData[key]);
        });
        
        const response = await fetch('/api/login.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('Verification successful! Redirecting...', 'success');
            
            // Store session data
            localStorage.setItem('user_session', JSON.stringify(data.user));
            localStorage.setItem('session_id', data.session_id);
            
            // Redirect based on user role
            setTimeout(() => {
                if (data.user.role === 'admin' || data.user.role === 'super_user') {
                    window.location.href = '/admin/dashboard/';
                } else if (data.user.role === 'owner') {
                    if (data.user.organization_slug) {
                        window.location.href = '/' + data.user.organization_slug + '/manage/';
                    } else {
                        window.location.href = '/admin/dashboard/';
                    }
                } else if (data.user.role === 'customer') {
                    if (data.user.organization_slug) {
                        window.location.href = '/' + data.user.organization_slug + '/cust/';
                    } else {
                        window.location.href = '/';
                    }
                } else {
                    window.location.href = '/';
                }
            }, 2000);
            
        } else {
            showAlert(data.message || 'Invalid verification code.', 'danger');
            const mfaCodeInput = document.getElementById('mfaCode');
            if (mfaCodeInput) {
                mfaCodeInput.value = '';
                mfaCodeInput.focus();
            }
        }
        
    } catch (error) {
        console.error('MFA verification error:', error);
        showAlert('An error occurred during verification. Please try again.', 'danger');
    } finally {
        showLoading(false);
    }
}

// Handle resend MFA code
async function handleResendMfa() {
    showLoading(true);
    
    try {
        const formData = new FormData();
        formData.append('email', currentEmail);
        formData.append('resend_mfa', '1');
        
        const response = await fetch('/api/login.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('Verification code resent to your email.', 'success');
        } else {
            showAlert(data.message || 'Failed to resend verification code.', 'danger');
        }
        
    } catch (error) {
        console.error('Resend MFA error:', error);
        showAlert('An error occurred while resending the code.', 'danger');
    } finally {
        showLoading(false);
    }
}

// Handle password reset
async function handlePasswordReset() {
    const resetEmail = document.getElementById('resetEmail').value.trim();
    
    if (!resetEmail) {
        showAlert('Please enter your email address.', 'danger');
        return;
    }
    
    showLoading(true);
    
    try {
        const formData = new FormData();
        formData.append('email', resetEmail);
        formData.append('action', 'reset_password');
        
        const response = await fetch('/api/password-reset.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('Password reset link sent to your email address.', 'success');
            showLoginForm();
        } else {
            showAlert(data.message || 'Failed to send password reset link.', 'danger');
        }
        
    } catch (error) {
        console.error('Password reset error:', error);
        showAlert('An error occurred while processing your request.', 'danger');
    } finally {
        showLoading(false);
    }
}

// Show MFA section
function showMfaSection() {
    const loginForm = document.getElementById('loginForm');
    const mfaSection = document.getElementById('mfaSection');
    const passwordResetSection = document.getElementById('passwordResetSection');
    
    if (loginForm) loginForm.style.display = 'none';
    if (mfaSection) {
        mfaSection.style.display = 'block';
        const mfaCodeInput = document.getElementById('mfaCode');
        if (mfaCodeInput) mfaCodeInput.focus();
    }
    if (passwordResetSection) passwordResetSection.style.display = 'none';
}

// Show password reset section
function showPasswordReset() {
    const loginForm = document.getElementById('loginForm');
    const mfaSection = document.getElementById('mfaSection');
    const passwordResetSection = document.getElementById('passwordResetSection');
    
    if (loginForm) loginForm.style.display = 'none';
    if (mfaSection) mfaSection.style.display = 'none';
    if (passwordResetSection) {
        passwordResetSection.style.display = 'block';
        const resetEmailInput = document.getElementById('resetEmail');
        if (resetEmailInput) resetEmailInput.focus();
    }
}

// Show login form
function showLoginForm() {
    const loginForm = document.getElementById('loginForm');
    const mfaSection = document.getElementById('mfaSection');
    const passwordResetSection = document.getElementById('passwordResetSection');
    
    if (loginForm) loginForm.style.display = 'block';
    if (mfaSection) mfaSection.style.display = 'none';
    if (passwordResetSection) passwordResetSection.style.display = 'none';
}

// Show loading spinner
function showLoading(show) {
    const spinner = document.getElementById('loadingSpinner');
    const loginBtn = document.getElementById('loginBtn');
    const verifyBtn = document.getElementById('verifyMfaBtn');
    const resetBtn = document.getElementById('sendResetBtn');
    
    if (show) {
        if (spinner) spinner.style.display = 'block';
        if (loginBtn) loginBtn.disabled = true;
        if (verifyBtn) verifyBtn.disabled = true;
        if (resetBtn) resetBtn.disabled = true;
    } else {
        if (spinner) spinner.style.display = 'none';
        if (loginBtn) loginBtn.disabled = false;
        if (verifyBtn) verifyBtn.disabled = false;
        if (resetBtn) resetBtn.disabled = false;
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

// Export functions for global access
window.showPasswordReset = showPasswordReset;
window.showLoginForm = showLoginForm;
