// Add Admin Form Validation and Enhancement

document.addEventListener('DOMContentLoaded', function() {
    console.log('Add admin page loaded');

    // Auto-hide messages after 5 seconds
    setTimeout(() => {
        const messages = document.querySelectorAll('.message');
        messages.forEach(msg => {
            if (msg) {
                msg.style.opacity = '0';
                setTimeout(() => {
                    if (msg.parentNode) {
                        msg.parentNode.removeChild(msg);
                    }
                }, 300);
            }
        });
    }, 5000);

    // Basic form validation
    const form = document.querySelector('.add-admin-form');
    const passwordInput = document.getElementById('password');
    const emailInput = document.getElementById('email');
    const fullNameInput = document.getElementById('fullName');

    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('Form submitted');
            
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const fullName = document.getElementById('fullName').value.trim();
            
            if (!email || !password || !fullName) {
                alert('Please fill in all required fields');
                e.preventDefault();
                return false;
            }
            
            if (password.length < 6) {
                alert('Password must be at least 6 characters long');
                e.preventDefault();
                return false;
            }
            
            console.log('Form validation passed, submitting...');
        });
    }

    // Email validation
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            const email = this.value.trim();
            const emailPattern = /^[A-Za-z0-9+_.-]+@(.+)$/;
            
            if (email && !emailPattern.test(email)) {
                showFieldError(this, 'Please enter a valid email address');
            } else {
                clearFieldError(this);
            }
        });
    }

    // Password strength indicator
    if (passwordInput) {
        const strengthIndicator = document.createElement('div');
        strengthIndicator.className = 'password-strength';
        passwordInput.parentNode.appendChild(strengthIndicator);

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            updatePasswordStrength(strengthIndicator, strength, password.length);
        });
    }

    function calculatePasswordStrength(password) {
        let score = 0;
        if (password.length >= 8) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        return score;
    }

    function updatePasswordStrength(indicator, strength, length) {
        const levels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        const colors = ['#ff4757', '#ff6b7a', '#ffa502', '#2ed573', '#1e90ff'];
        
        if (length === 0) {
            indicator.style.display = 'none';
            return;
        }
        
        indicator.style.display = 'block';
        indicator.textContent = `Password Strength: ${levels[strength] || 'Very Weak'}`;
        indicator.style.color = colors[strength] || colors[0];
        indicator.style.fontSize = '0.8rem';
        indicator.style.marginTop = '5px';
    }

    function showFieldError(field, message) {
        clearFieldError(field);
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        errorDiv.style.color = '#e74c3c';
        errorDiv.style.fontSize = '0.8rem';
        errorDiv.style.marginTop = '5px';
        
        field.parentNode.appendChild(errorDiv);
        field.style.borderColor = '#e74c3c';
    }

    function clearFieldError(field) {
        if (!field) return;
        
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
        field.style.borderColor = '';
    }
});