class NewJournalEntry {
  constructor() {
    this.selectedCategory = "gratitude";
    this.isHighlight = false;

    this.init();
  }

  init() {
    this.setupEventListeners();
    this.showPopup();
    this.loadCustomCategories();
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

    // Category selection
    const categoryButtons = document.querySelectorAll(
      ".category-btn:not(.add-category-btn)"
    );
    categoryButtons.forEach((btn) => {
      btn.addEventListener("click", (e) => {
        this.selectCategory(e.target.dataset.category);
      });
    });

    // Add category button
    const addCategoryBtn = document.getElementById("addCategoryBtn");
    addCategoryBtn?.addEventListener("click", () => {
      this.showAddCategoryDialog();
    });

    // Highlight checkbox
    const highlightCheckbox = document.getElementById("highlightCheckbox");
    highlightCheckbox?.addEventListener("change", (e) => {
      this.isHighlight = e.target.checked;
      document.getElementById("isHighlightValue").value = this.isHighlight;
    });

    // Form submission
    const form = document.getElementById("journalEntryForm");
    form?.addEventListener("submit", (e) => this.handleFormSubmit(e));

    // Escape key to close
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        this.hidePopup();
      }
    });

    // Auto-resize textarea
    const textarea = document.getElementById("journalContent");
    textarea?.addEventListener("input", () => {
      this.autoResizeTextarea(textarea);
    });
  }

  loadCustomCategories() {
    // Load any server-provided custom categories
    if (window.serverData && window.serverData.customCategories) {
      window.serverData.customCategories.forEach((category) => {
        // Categories are already rendered by JSP, just add event listeners
        const btn = document.querySelector(
          `[data-category="${category.value}"]`
        );
        if (btn) {
          btn.addEventListener("click", (e) => {
            this.selectCategory(e.target.dataset.category);
          });
        }
      });
    }
  }

  showPopup() {
    const overlay = document.getElementById("popupOverlay");
    if (overlay) {
      overlay.classList.add("active");
      document.body.style.overflow = "hidden";

      // Focus on title input after animation
      setTimeout(() => {
        const titleInput = document.getElementById("journalTitle");
        titleInput?.focus();
      }, 300);
    }
  }

  hidePopup() {
    const overlay = document.getElementById("popupOverlay");
    if (overlay) {
      overlay.classList.remove("active");
      document.body.style.overflow = "";

      // Optional: Redirect back
      setTimeout(() => {
        window.history.back(); // Go back to previous page
        // OR: window.location.href = window.serverData.contextPath + '/user/dashboard';
      }, 300);
    }
  }

  selectCategory(category) {
    this.selectedCategory = category;
    document.getElementById("selectedCategory").value = category;

    // Update UI
    const categoryButtons = document.querySelectorAll(
      ".category-btn:not(.add-category-btn)"
    );
    categoryButtons.forEach((btn) => {
      btn.classList.toggle("selected", btn.dataset.category === category);
    });
  }

  showAddCategoryDialog() {
    const categoryName = prompt("Enter new category name:");
    if (categoryName && categoryName.trim()) {
      this.addNewCategoryToServer(categoryName.trim());
    }
  }

  async addNewCategoryToServer(categoryName) {
    try {
      const formData = new FormData();
      formData.append("categoryName", categoryName);

      // Add CSRF token if available
      if (window.serverData.csrfToken) {
        formData.append(
          window.serverData.csrfHeader,
          window.serverData.csrfToken
        );
      }

      const response = await fetch(
        `${window.serverData.contextPath}/user/addCustomCategory`,
        {
          method: "POST",
          body: formData,
          headers: {
            "X-Requested-With": "XMLHttpRequest",
          },
        }
      );

      if (response.ok) {
        const result = await response.json();
        if (result.success) {
          this.addNewCategoryToUI(categoryName, result.categoryValue);
          this.showSuccess(`Category "${categoryName}" added successfully!`);
        } else {
          this.showError(result.message || "Failed to add category.");
        }
      } else {
        throw new Error("Failed to add category");
      }
    } catch (error) {
      console.error("Error adding category:", error);
      this.showError("Failed to add category. Please try again.");
    }
  }

  addNewCategoryToUI(categoryName, categoryValue) {
    // Create new category button
    const categoryButtons = document.querySelector(".category-buttons");
    const addBtn = document.getElementById("addCategoryBtn");

    const newCategoryBtn = document.createElement("button");
    newCategoryBtn.type = "button";
    newCategoryBtn.className = "category-btn";
    newCategoryBtn.dataset.category = categoryValue;
    newCategoryBtn.textContent = categoryName;

    // Add event listener
    newCategoryBtn.addEventListener("click", (e) => {
      this.selectCategory(e.target.dataset.category);
    });

    // Insert before add button
    categoryButtons.insertBefore(newCategoryBtn, addBtn);

    // Select the new category
    this.selectCategory(categoryValue);
  }

  autoResizeTextarea(textarea) {
    textarea.style.height = "auto";
    textarea.style.height = Math.max(150, textarea.scrollHeight) + "px";
  }

  handleFormSubmit(e) {
    e.preventDefault();

    const content =
      document.getElementById("journalContent")?.value.trim() || "";

    // Validate required fields
    if (!content) {
      this.showError("Please write something in your journal entry.");
      return;
    }

    // Update hidden fields before submission
    document.getElementById("selectedCategory").value = this.selectedCategory;
    document.getElementById("isHighlightValue").value = this.isHighlight;

    // Show loading state
    const saveBtn = document.querySelector(".btn-save");
    const originalText = saveBtn.textContent;
    saveBtn.disabled = true;
    saveBtn.textContent = "Saving...";

    // Submit form using AJAX to handle response
    this.submitFormAjax(e.target)
      .then((response) => {
        if (response.success) {
          this.showSuccess("Journal entry saved successfully!");
          setTimeout(() => {
            this.hidePopup();
          }, 1500);
        } else {
          this.showError(
            response.message || "Failed to save entry. Please try again."
          );
        }
      })
      .catch((error) => {
        console.error("Error saving entry:", error);
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
  new NewJournalEntry();
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
