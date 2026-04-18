// Add Counselor Form Validation and Enhancement

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addCounselorForm');
    const passwordField = document.getElementById('password');
    const emailField = document.getElementById('email');
    const usernameField = document.getElementById('username');

    // Form validation
    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
        }
    });

    // Real-time validation
    emailField.addEventListener('blur', validateEmail);
    passwordField.addEventListener('blur', validatePassword);
    usernameField.addEventListener('blur', validateUsername);

    function validateForm() {
        let isValid = true;

        isValid = validateEmail() && isValid;
        isValid = validatePassword() && isValid;
        isValid = validateUsername() && isValid;
        isValid = validateRequiredFields() && isValid;

        return isValid;
    }

    function validateEmail() {
        const email = emailField.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        clearError(emailField);
        
        if (!email) {
            showError(emailField, 'Email is required');
            return false;
        }
        
        if (!emailRegex.test(email)) {
            showError(emailField, 'Please enter a valid email address');
            return false;
        }
        
        return true;
    }

    function validatePassword() {
        const password = passwordField.value;
        
        clearError(passwordField);
        
        if (!password) {
            showError(passwordField, 'Password is required');
            return false;
        }
        
        if (password.length < 6) {
            showError(passwordField, 'Password must be at least 6 characters long');
            return false;
        }
        
        return true;
    }

    function validateUsername() {
        const username = usernameField.value.trim();
        const usernameRegex = /^[a-zA-Z0-9_]+$/;
        
        clearError(usernameField);
        
        if (!username) {
            showError(usernameField, 'Username is required');
            return false;
        }
        
        if (!usernameRegex.test(username)) {
            showError(usernameField, 'Username can only contain letters, numbers, and underscores');
            return false;
        }
        
        return true;
    }

    function validateRequiredFields() {
        const requiredFields = ['fullName', 'title', 'specialtyShort', 'specialty'];
        let isValid = true;

        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            const value = field.value.trim();
            
            clearError(field);
            
            if (!value) {
                showError(field, 'This field is required');
                isValid = false;
            }
        });

        return isValid;
    }

    function showError(field, message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.style.color = '#dc3545';
        errorDiv.style.fontSize = '12px';
        errorDiv.style.marginTop = '5px';
        errorDiv.textContent = message;
        
        field.style.borderColor = '#dc3545';
        field.parentNode.appendChild(errorDiv);
    }

    function clearError(field) {
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
        field.style.borderColor = '#ddd';
    }

    // Auto-generate username from full name
    document.getElementById('fullName').addEventListener('input', function() {
        const fullName = this.value.trim();
        const usernameField = document.getElementById('username');
        
        if (fullName && !usernameField.value) {
            // Generate username from full name
            let username = fullName.toLowerCase()
                .replace(/dr\.?\s*/i, 'dr_')
                .replace(/\s+/g, '_')
                .replace(/[^a-z0-9_]/g, '');
            
            usernameField.value = username;
        }
    });

    // Show/hide password
    const passwordToggle = document.createElement('button');
    passwordToggle.type = 'button';
    passwordToggle.innerHTML = '👁️';
    passwordToggle.style.position = 'absolute';
    passwordToggle.style.right = '10px';
    passwordToggle.style.top = '50%';
    passwordToggle.style.transform = 'translateY(-50%)';
    passwordToggle.style.border = 'none';
    passwordToggle.style.background = 'none';
    passwordToggle.style.cursor = 'pointer';
    
    passwordField.parentNode.style.position = 'relative';
    passwordField.parentNode.appendChild(passwordToggle);
    
    passwordToggle.addEventListener('click', function() {
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            this.innerHTML = '🙈';
        } else {
            passwordField.type = 'password';
            this.innerHTML = '👁️';
        }
    });

    // Clear success/error messages after 5 seconds
    const messages = document.querySelectorAll('.success-message, .error-message');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 300);
        }, 5000);
    });
});