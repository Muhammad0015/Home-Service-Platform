/**
 * ================================================
 * CLIENT-SIDE FORM VALIDATION
 * Home Service Booking Platform
 * ================================================
 */

// ==================== VALIDATION PATTERNS ====================

const ValidationPatterns = {
    email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    phone: /^[0-9\-\+\s\(\)]{7,20}$/,
    password: /^.{6,}$/
};

const ErrorMessages = {
    required: (fieldName) => `${fieldName} is required`,
    email: 'Please enter a valid email address',
    phone: 'Please enter a valid phone number',
    passwordLength: 'Password must be at least 6 characters',
    passwordMatch: 'Passwords do not match',
    select: (fieldName) => `Please select a ${fieldName}`
};

// ==================== VALIDATION FUNCTIONS ====================

function isEmpty(value) {
    return value === null || value === undefined || value.trim() === '';
}

function validateRequired(value, fieldName) {
    if (isEmpty(value)) {
        return ErrorMessages.required(fieldName);
    }
    return null;
}

function validateEmail(email) {
    if (isEmpty(email)) {
        return ErrorMessages.required('Email');
    }
    if (!ValidationPatterns.email.test(email)) {
        return ErrorMessages.email;
    }
    return null;
}

function validatePhone(phone, required = true) {
    if (isEmpty(phone)) {
        if (required) {
            return ErrorMessages.required('Phone number');
        }
        return null;
    }
    if (!ValidationPatterns.phone.test(phone)) {
        return ErrorMessages.phone;
    }
    return null;
}

function validatePassword(password) {
    if (isEmpty(password)) {
        return ErrorMessages.required('Password');
    }
    if (password.length < 6) {
        return ErrorMessages.passwordLength;
    }
    return null;
}

function validatePasswordMatch(password, confirmPassword) {
    if (isEmpty(confirmPassword)) {
        return ErrorMessages.required('Confirm Password');
    }
    if (password !== confirmPassword) {
        return ErrorMessages.passwordMatch;
    }
    return null;
}

function validateName(name, fieldName = 'Name') {
    if (isEmpty(name)) {
        return ErrorMessages.required(fieldName);
    }
    if (name.length < 2) {
        return `${fieldName} must be at least 2 characters`;
    }
    return null;
}

function validateSelect(value, fieldName) {
    if (isEmpty(value) || value === '0' || value === '') {
        return ErrorMessages.select(fieldName);
    }
    return null;
}

function validateNumber(value, fieldName, min = null, max = null) {
    if (isEmpty(value)) {
        return ErrorMessages.required(fieldName);
    }
    
    const num = parseFloat(value);
    
    if (isNaN(num)) {
        return `Please enter a valid ${fieldName}`;
    }
    
    if (min !== null && num < min) {
        return `${fieldName} must be at least ${min}`;
    }
    
    if (max !== null && num > max) {
        return `${fieldName} must not exceed ${max}`;
    }
    
    return null;
}

// ==================== UI HELPER FUNCTIONS ====================

function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const errorElement = document.getElementById(fieldId + 'Error');
    
    if (field) {
        field.classList.add('error');
        field.classList.remove('success');
    }
    
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.classList.add('show');
    }
}

function clearFieldError(fieldId) {
    const field = document.getElementById(fieldId);
    const errorElement = document.getElementById(fieldId + 'Error');
    
    if (field) {
        field.classList.remove('error');
    }
    
    if (errorElement) {
        errorElement.textContent = '';
        errorElement.classList.remove('show');
    }
}

function showFieldSuccess(fieldId) {
    const field = document.getElementById(fieldId);
    
    if (field) {
        field.classList.remove('error');
        field.classList.add('success');
    }
    
    clearFieldError(fieldId);
}

function showAlert(type, message, containerId = 'alertContainer') {
    const container = document.getElementById(containerId);
    
    if (container) {
        container.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
        
        setTimeout(() => {
            container.innerHTML = '';
        }, 5000);
    }
}

// ==================== FORM VALIDATION ====================

function validateRegistrationForm() {
    let isValid = true;
    const userType = document.getElementById('userType').value;
    
    // Validate Full Name
    const fullNameError = validateName(document.getElementById('fullName').value, 'Full Name');
    if (fullNameError) {
        showFieldError('fullName', fullNameError);
        isValid = false;
    } else {
        showFieldSuccess('fullName');
    }
    
    // Validate Email
    const emailError = validateEmail(document.getElementById('email').value);
    if (emailError) {
        showFieldError('email', emailError);
        isValid = false;
    } else {
        showFieldSuccess('email');
    }
    
    // Validate Phone
    const phoneError = validatePhone(document.getElementById('phone').value);
    if (phoneError) {
        showFieldError('phone', phoneError);
        isValid = false;
    } else {
        showFieldSuccess('phone');
    }
    
    // Validate Password
    const password = document.getElementById('password').value;
    const passwordError = validatePassword(password);
    if (passwordError) {
        showFieldError('password', passwordError);
        isValid = false;
    } else {
        showFieldSuccess('password');
    }
    
    // Validate Confirm Password
    const confirmPassword = document.getElementById('confirmPassword').value;
    const confirmPasswordError = validatePasswordMatch(password, confirmPassword);
    if (confirmPasswordError) {
        showFieldError('confirmPassword', confirmPasswordError);
        isValid = false;
    } else {
        showFieldSuccess('confirmPassword');
    }
    
    // Provider-specific validations
    if (userType === 'provider') {
        const categoryError = validateSelect(document.getElementById('serviceCategory').value, 'service category');
        if (categoryError) {
            showFieldError('serviceCategory', categoryError);
            isValid = false;
        } else {
            showFieldSuccess('serviceCategory');
        }
        
        const experienceError = validateNumber(document.getElementById('experience').value, 'Experience', 0, 50);
        if (experienceError) {
            showFieldError('experience', experienceError);
            isValid = false;
        } else {
            showFieldSuccess('experience');
        }
        
        const hourlyRateError = validateNumber(document.getElementById('hourlyRate').value, 'Hourly Rate', 1);
        if (hourlyRateError) {
            showFieldError('hourlyRate', hourlyRateError);
            isValid = false;
        } else {
            showFieldSuccess('hourlyRate');
        }
    }
    
    return isValid;
}

function validateLoginForm() {
    let isValid = true;
    
    const emailError = validateEmail(document.getElementById('email').value);
    if (emailError) {
        showFieldError('email', emailError);
        isValid = false;
    } else {
        showFieldSuccess('email');
    }
    
    const passwordError = validateRequired(document.getElementById('password').value, 'Password');
    if (passwordError) {
        showFieldError('password', passwordError);
        isValid = false;
    } else {
        showFieldSuccess('password');
    }
    
    return isValid;
}

// ==================== FORM SUBMISSION HANDLERS ====================

/**
 * Handle Registration Form Submission
 */
function handleRegistrationSubmit(event) {
    event.preventDefault();
    
    if (!validateRegistrationForm()) {
        showAlert('error', 'Please fix the errors in the form');
        return;
    }
    
    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = document.getElementById('submitBtn');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
    
    fetch('php/register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Save session to localStorage
            SessionManager.saveSession({
                type: data.data.type,
                id: data.data.id,
                name: data.data.name
            });
            
            showAlert('success', data.message);
            
            // Redirect based on user type
            setTimeout(() => {
                if (data.data.type === 'provider') {
                    window.location.href = 'provider-dashboard.html';
                } else {
                    window.location.href = 'dashboard.html';
                }
            }, 1000);
        } else {
            showAlert('error', data.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-user-plus"></i> Create Account';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-user-plus"></i> Create Account';
    });
}

/**
 * Handle Login Form Submission
 */
function handleLoginSubmit(event) {
    event.preventDefault();
    
    if (!validateLoginForm()) {
        showAlert('error', 'Please fix the errors in the form');
        return;
    }
    
    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = document.getElementById('submitBtn');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
    
    fetch('php/login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Save session to localStorage
            SessionManager.saveSession({
                type: data.data.type,
                id: data.data.id,
                name: data.data.name
            });
            
            showAlert('success', data.message);
            
            // Redirect based on user type
            setTimeout(() => {
                if (data.data.type === 'provider') {
                    window.location.href = 'provider-dashboard.html';
                } else {
                    window.location.href = 'dashboard.html';
                }
            }, 1000);
        } else {
            showAlert('error', data.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Login';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Login';
    });
}

// ==================== INITIALIZE ====================

document.addEventListener('DOMContentLoaded', function() {
    // Setup registration form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegistrationSubmit);
    }
    
    // Setup login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLoginSubmit);
    }
});

// Make functions globally available
window.showFieldError = showFieldError;
window.clearFieldError = clearFieldError;
window.showFieldSuccess = showFieldSuccess;
window.validateRequired = validateRequired;
window.validateEmail = validateEmail;