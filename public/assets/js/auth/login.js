document.addEventListener("DOMContentLoaded", function () {
  // Detect if this is counselor, admin, or user login
  const isCounselorLogin =document.getElementById("counselorLoginForm") !== null;
  const isAdminLogin = document.getElementById("adminLoginForm") !== null;
  const isUserLogin = document.getElementById("loginForm") !== null;

  // Get form elements
  let loginForm,
    emailInput,
    passwordInput,
    passwordToggle,
    keepSignedInCheckbox,
    loginBtn;

  if (isCounselorLogin) {
    loginForm = document.getElementById("counselorLoginForm");
    loginBtn = loginForm.querySelector(".form-submit-btn");
  } else if (isAdminLogin) {
    loginForm = document.getElementById("adminLoginForm");
    loginBtn = loginForm.querySelector(".form-submit-btn");
  } else if (isUserLogin) {
    loginForm = document.getElementById("loginForm");
    loginBtn = document.querySelector(".form-submit-btn"); // Standardized class
  }

  if (!loginForm) return;

  emailInput = loginForm.querySelector("#email");
  passwordInput = loginForm.querySelector("#password");
  passwordToggle = loginForm.querySelector("#passwordToggle");
  keepSignedInCheckbox = loginForm.querySelector("#keepSignedIn");

  // Password visibility toggle
  if (passwordToggle && passwordInput) {
    passwordToggle.addEventListener("click", function () {
      const type =
        passwordInput.getAttribute("type") === "password" ? "text" : "password";
      passwordInput.setAttribute("type", type);

      // Update icon
      const icon = passwordToggle.querySelector("svg");
      if (type === "text") {
        icon.innerHTML = `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>`;
      } else {
        icon.innerHTML = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
      }
    });
  }

  // Form validation functions
  function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function showError(input, message) {
    clearError(input);
    input.style.borderColor = "var(--color-error, #f44336)";
    const errorDiv = document.createElement("div");
    errorDiv.className = "field-error";
    errorDiv.textContent = message;
    input.parentNode.appendChild(errorDiv);
  }

  function clearError(input) {
    const existingError = input.parentNode.querySelector(".field-error");
    if (existingError) existingError.remove();
    input.style.borderColor = "";
  }

  // Real-time validation
  if (emailInput) {
    emailInput.addEventListener("blur", function () {
      if (this.value && !validateEmail(this.value)) {
        showError(this, "Please enter a valid email address");
      }
    });
    emailInput.addEventListener("input", function () {
      clearError(this);
    });
  }

  if (passwordInput) {
    passwordInput.addEventListener("blur", function () {
      if (this.value && this.value.length < 6) {
        showError(this, "Password must be at least 6 characters");
      }
    });
    passwordInput.addEventListener("input", function () {
      clearError(this);
    });
  }

  // Form submission
  loginForm.addEventListener("submit", function (e) {
    const email = emailInput.value.trim();
    const password = passwordInput.value.trim();
    let isValid = true;

    if (!validateEmail(email)) {
      showError(emailInput, "Valid email is required");
      isValid = false;
    }
    if (password.length < 6) {
      showError(passwordInput, "Password is too short");
      isValid = false;
    }

    if (!isValid) {
      e.preventDefault();
    } else {
      // Show loading state
      const originalText = loginBtn.textContent;
      loginBtn.textContent = isUserLogin
        ? "Logging in..."
        : "Accessing Portal...";
      loginBtn.disabled = true;
    }
  });

  // Auto-focus
  if (emailInput) emailInput.focus();
});
