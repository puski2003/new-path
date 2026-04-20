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

    const emailValue = email.value.trim();

    if (!emailValue) {
        showError(email, 'Email address is required.');
        valid = false;
    } else if (!emailValue.includes('@') || !emailValue.includes('.')) {
        showError(email, 'Please enter a valid email address.');
        valid = false;
    }

    const phoneRegex = /^(?:\+94|0)?7[0-9]{8}$/;

    if (!phoneNumber.value.trim()) {
        showError(phoneNumber, 'Phone number is required.');
        valid = false;
    } else if (!phoneRegex.test(phoneNumber.value.trim())) {
        showError(phoneNumber, 'Enter a valid Sri Lankan mobile number (e.g. 0771234567 or +94771234567).');
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

        const file = fileInput.files[0];

        const allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        const allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/png'
        ];

        const ext = file.name.split('.').pop().toLowerCase();

        if (!allowedExtensions.includes(ext)) {
            showError(fileInput, 'Invalid file extension.');
            valid = false;
        }
        else if (!allowedTypes.includes(file.type)) {
            showError(fileInput, 'Invalid file type.');
            valid = false;
        }
        else if (file.size > 10 * 1024 * 1024) {
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