class LogUrgePopup {
  constructor() {
    this.selectedType = "urge";
    this.selectedTriggers = [];
    this.isCrisis = false;

    this.init();
  }

  init() {
    this.setupEventListeners();
    this.showPopup();
  }

  setupEventListeners() {
    // Close popup events
    const closeBtn = document.getElementById("closePopup");
    const overlay = document.getElementById("popupOverlay");
    const cancelBtn = document.querySelector(".btn-cancel");

    closeBtn?.addEventListener("click", () => this.hidePopup());
    cancelBtn?.addEventListener("click", () => this.hidePopup());

    overlay?.addEventListener("click", (e) => {
      if (e.target === overlay) {
        this.hidePopup();
      }
    });

    // Type selection
    const typeButtons = document.querySelectorAll(".type-btn");
    typeButtons.forEach((btn) => {
      btn.addEventListener("click", (e) => {
        this.selectType(e.target.dataset.type);
      });
    });

    // Trigger selection
    const triggerButtons = document.querySelectorAll(".trigger-btn");
    triggerButtons.forEach((btn) => {
      btn.addEventListener("click", (e) => {
        this.toggleTrigger(e.target.dataset.trigger, e.target);
      });
    });

    // Crisis checkbox
    const crisisCheckbox = document.getElementById("crisisCheckbox");
    crisisCheckbox?.addEventListener("change", (e) => {
      this.isCrisis = e.target.checked;
      document.getElementById("isCrisisValue").value = this.isCrisis;
    });

    // Form submission
    const form = document.getElementById("logUrgeForm");
    form?.addEventListener("submit", (e) => this.handleFormSubmit(e));

    // Escape key to close
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        this.hidePopup();
      }
    });
  }

  showPopup() {
    const overlay = document.getElementById("popupOverlay");
    if (overlay) {
      overlay.classList.add("active");
      document.body.style.overflow = "hidden";
    }
  }

  hidePopup() {
    const overlay = document.getElementById("popupOverlay");
    if (overlay) {
      overlay.classList.remove("active");
      document.body.style.overflow = "";

      // Optional: Redirect or close window
      setTimeout(() => {
        window.history.back(); // Go back to previous page
        // OR: window.close(); // Close popup window
        // OR: window.location.href = window.serverData.contextPath + '/user/dashboard';
      }, 300);
    }
  }

  selectType(type) {
    this.selectedType = type;
    document.getElementById("selectedType").value = type;

    // Update UI
    const typeButtons = document.querySelectorAll(".type-btn");
    typeButtons.forEach((btn) => {
      btn.classList.toggle("active", btn.dataset.type === type);
    });
  }

  toggleTrigger(trigger, buttonElement) {
    const index = this.selectedTriggers.indexOf(trigger);

    if (index > -1) {
      // Remove trigger
      this.selectedTriggers.splice(index, 1);
      buttonElement.classList.remove("selected");
    } else {
      // Add trigger
      this.selectedTriggers.push(trigger);
      buttonElement.classList.add("selected");
    }

    // Update hidden field
    document.getElementById("selectedTriggers").value =
      this.selectedTriggers.join(",");
  }

  handleFormSubmit(e) {
    e.preventDefault();

    // Validate required fields
    if (this.selectedTriggers.length === 0) {
      this.showError("Please select at least one trigger.");
      return;
    }

    // Update hidden fields before submission
    document.getElementById("selectedType").value = this.selectedType;
    document.getElementById("selectedTriggers").value =
      this.selectedTriggers.join(",");
    document.getElementById("isCrisisValue").value = this.isCrisis;

    // Show loading state
    const saveBtn = document.querySelector(".btn-save");
    const originalText = saveBtn.textContent;
    saveBtn.disabled = true;
    saveBtn.textContent = "Saving...";

    // Submit form using AJAX to handle response
    this.submitFormAjax(e.target)
      .then((response) => {
        if (response.success) {
          this.showSuccess("Log saved successfully!");
          setTimeout(() => {
            this.hidePopup();
          }, 1500);
        } else {
          this.showError(
            response.message || "Failed to save log. Please try again."
          );
        }
      })
      .catch((error) => {
        console.error("Error saving log:", error);
        this.showError("An error occurred. Please try again.");
      })
      .finally(() => {
        // Restore button state
        saveBtn.disabled = false;
        saveBtn.textContent = originalText;
      });
  }

  async submitFormAjax(form) {
    const formData = new FormData(form);

    try {
      const response = await fetch(form.action, {
        method: "POST",
        body: formData,
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          // Add CSRF header if using Spring Security
          ...(window.serverData.csrfToken && {
            [window.serverData.csrfHeader]: window.serverData.csrfToken,
          }),
        },
      });

      if (response.ok) {
        const result = await response.json();
        return result;
      } else {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
    } catch (error) {
      throw error;
    }
  }

  showError(message) {
    this.showMessage(message, "error");
  }

  showSuccess(message) {
    this.showMessage(message, "success");
  }

  showMessage(message, type) {
    // Remove existing messages
    const existingMessage = document.querySelector(".popup-message");
    if (existingMessage) {
      existingMessage.remove();
    }

    // Create message element
    const messageDiv = document.createElement("div");
    messageDiv.className = `popup-message popup-message-${type}`;
    messageDiv.textContent = message;

    // Add styles
    messageDiv.style.cssText = `
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            z-index: 1001;
            animation: slideDown 0.3s ease;
            ${
              type === "error"
                ? "background: #fee; color: #c53030; border: 1px solid #feb2b2;"
                : "background: #f0fff4; color: #22543d; border: 1px solid #9ae6b4;"
            }
        `;

    // Insert into popup container
    const popupContainer = document.querySelector(".popup-container");
    popupContainer.appendChild(messageDiv);

    // Auto-remove after 4 seconds
    setTimeout(() => {
      if (messageDiv.parentNode) {
        messageDiv.remove();
      }
    }, 4000);
  }
}

// Initialize popup when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  new LogUrgePopup();
});

// Add CSS animation
const style = document.createElement("style");
style.textContent = `
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateX(-50%) translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    }
`;
document.head.appendChild(style);
