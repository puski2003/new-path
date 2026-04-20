function showError(input, message) {
    input.classList.add('ca-input--error');
    if (input.parentNode.querySelector('.ca-field-error')) return;
    const err = document.createElement('small');
    err.className   = 'ca-field-error';
    err.textContent = message;
    err.style.cssText = 'color:#f43a3a;font-size:12px;margin-top:4px;display:block;';
    input.parentNode.appendChild(err);
}

function clearError(input) {
    input.classList.remove('ca-input--error');
    const err = input.parentNode.querySelector('.ca-field-error');
    if (err) err.remove();
}

document.addEventListener('DOMContentLoaded', function () {
    ['fullName','email','phoneNumber','specialty','bio','education','documentsFile'].forEach(function (id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input',  function () { clearError(el); });
        el.addEventListener('change', function () { clearError(el); });
    });
});

function showFileName(input) {
    var display = document.getElementById('fileNameDisplay');
    display.textContent = input.files.length > 0 ? '✓ ' + input.files[0].name : '';
}

function validateForm() {
    let valid = true;

    const fullName    = document.getElementById('fullName');
    const email       = document.getElementById('email');
    const phoneNumber = document.getElementById('phoneNumber');
    const specialty   = document.getElementById('specialty');
    const bio         = document.getElementById('bio');
    const education   = document.getElementById('education');
    const fileInput   = document.getElementById('documentsFile');

    if (!fullName.value.trim()) {
        showError(fullName, 'Full name is required.');
        valid = false;
    }

    if (!email.value.trim()) {
        showError(email, 'Email address is required.');
        valid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        showError(email, 'Please enter a valid email address.');
        valid = false;
    }

    if (!phoneNumber.value.trim()) {
        showError(phoneNumber, 'Phone number is required.');
        valid = false;
    } else if (!/^\+?[\d\s\-]{7,15}$/.test(phoneNumber.value)) {
        showError(phoneNumber, 'Please enter a valid phone number.');
        valid = false;
    }

    if (!specialty.value) {
        showError(specialty, 'Please select a specialty.');
        valid = false;
    }

    if (!bio.value.trim()) {
        showError(bio, 'Professional bio is required.');
        valid = false;
    }

    if (!education.value.trim()) {
        showError(education, 'Education details are required.');
        valid = false;
    }

    if (fileInput && fileInput.files.length > 0) {
        const allowed = ['application/pdf', 'application/msword', 'image/jpeg', 'image/png'];
        if (!allowed.includes(fileInput.files[0].type)) {
            showError(fileInput, 'Only PDF, DOC, DOCX, JPG or PNG files are allowed.');
            valid = false;
        } else if (fileInput.files[0].size > 10 * 1024 * 1024) {
            showError(fileInput, 'File must be under 10 MB.');
            valid = false;
        }
    }
    return valid;
}

document.querySelector('form').addEventListener('submit', function (e) {
    if (!validateForm()) {
        e.preventDefault();
    }
});