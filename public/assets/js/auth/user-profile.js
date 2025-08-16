document.addEventListener("DOMContentLoaded", function () {
  const userMenuBtn = document.getElementById("userMenuBtn");
  const userMenuDropdown = document.getElementById("userMenuDropdown");
  const editProfileBtn = document.getElementById("editProfileBtn");
  const profileModal = document.getElementById("profileModalOverlay");
  const profileModalClose = document.getElementById("profileModalClose");
  const cancelProfileBtn = document.getElementById("cancelProfile");
  const profilePictureInput = document.getElementById("profilePicture");
  const currentProfilePic = document.getElementById("currentProfilePic");

  // Toggle user menu dropdown
  if (userMenuBtn && userMenuDropdown) {
    userMenuBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      userMenuDropdown.classList.toggle("show");
    });
  }

  // Close dropdown when clicking outside
  document.addEventListener("click", function (e) {
    if (
      userMenuDropdown &&
      !userMenuBtn?.contains(e.target) &&
      !userMenuDropdown.contains(e.target)
    ) {
      userMenuDropdown.classList.remove("show");
    }
  });

  // Open profile modal
  if (editProfileBtn) {
    editProfileBtn.addEventListener("click", function (e) {
      e.preventDefault();
      showProfileModal();
      if (userMenuDropdown) userMenuDropdown.classList.remove("show");
    });
  }

  // Close profile modal
  function closeProfileModal() {
    if (profileModal) {
      profileModal.classList.remove("show");
      setTimeout(() => {
        profileModal.style.display = "none";
      }, 300);
    }
  }

  if (profileModalClose) {
    profileModalClose.addEventListener("click", closeProfileModal);
  }

  if (cancelProfileBtn) {
    cancelProfileBtn.addEventListener("click", closeProfileModal);
  }

  // Close modal when clicking overlay
  if (profileModal) {
    profileModal.addEventListener("click", function (e) {
      if (e.target === profileModal) {
        closeProfileModal();
      }
    });
  }

  // ESC key to close modal
  document.addEventListener("keydown", function (e) {
    if (
      e.key === "Escape" &&
      profileModal &&
      profileModal.classList.contains("show")
    ) {
      closeProfileModal();
    }
  });

  // Profile picture preview
  if (profilePictureInput && currentProfilePic) {
    profilePictureInput.addEventListener("change", function (e) {
      const file = e.target.files[0];
      if (file && file.type.startsWith("image/")) {
        const reader = new FileReader();
        reader.onload = function (e) {
          currentProfilePic.src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    });
  }

  function showProfileModal() {
    if (profileModal) {
      profileModal.style.display = "flex";
      profileModal.offsetHeight; // Force reflow
      profileModal.classList.add("show");

      // Initialize Lucide icons
      if (typeof lucide !== "undefined") {
        lucide.createIcons();
      }

      // Focus on first input
      const firstInput = profileModal.querySelector('input[type="text"]');
      if (firstInput) {
        setTimeout(() => firstInput.focus(), 100);
      }
    }
  }
});
