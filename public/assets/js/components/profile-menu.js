document.addEventListener("DOMContentLoaded", function () {
  initUserProfileMenu();
  initCounselorProfileMenu();
  initAdminProfileMenu();
});

function initUserProfileMenu() {
  const menuButton = document.getElementById("userMenuBtn");
  const dropdown = document.getElementById("userMenuDropdown");
  const editButton = document.getElementById("editProfileBtn");
  const modal = document.getElementById("profileModalOverlay");
  const closeButton = document.getElementById("profileModalClose");
  const cancelButton = document.getElementById("cancelProfile");
  const fileInput = document.getElementById("profilePicture");
  const previewImage = document.getElementById("currentProfilePic");

  bindDropdownMenu(menuButton, dropdown);

  if (editButton) {
    editButton.addEventListener("click", function (event) {
      event.preventDefault();
      openProfileModal(modal);
      dropdown?.classList.remove("show");
    });
  }

  bindModalClose(modal, closeButton, cancelButton);
  bindImagePreview(fileInput, previewImage);
}

function initCounselorProfileMenu() {
  const menuButton = document.getElementById("counselorMenuBtn");
  const dropdown = document.getElementById("counselorMenuDropdown");
  const editButton = document.getElementById("editCounselorProfileBtn");
  const logoutButton = document.getElementById("counselorLogoutBtn");
  const logoutForm = document.getElementById("counselorLogoutForm");
  const modal = document.getElementById("counselorProfileModalOverlay");
  const closeButton = document.getElementById("counselorProfileModalClose");
  const cancelButton = document.getElementById("cancelCounselorProfile");
  const fileInput = document.getElementById("counselorProfilePicture");
  const previewImage = document.getElementById("counselorCurrentProfilePic");

  bindDropdownMenu(menuButton, dropdown);

  if (editButton) {
    editButton.addEventListener("click", function (event) {
      event.preventDefault();
      openProfileModal(modal);
     
      dropdown?.classList.remove("show");
    });
  }

  if (logoutButton && logoutForm) {
    logoutButton.addEventListener("click", function (event) {
      event.preventDefault();
      logoutForm.submit();
    });
  }

  bindModalClose(modal, closeButton, cancelButton);
  bindImagePreview(fileInput, previewImage);
}

function initAdminProfileMenu() {
  const menuButton = document.getElementById("adminMenuBtn");
  const dropdown = document.getElementById("adminMenuDropdown");
  bindDropdownMenu(menuButton, dropdown);
}

function bindDropdownMenu(button, dropdown) {
  if (!button || !dropdown) {
    return;
  }

  button.addEventListener("click", function (event) {
    event.stopPropagation();
    dropdown.classList.toggle("show");
  });

  document.addEventListener("click", function (event) {
    if (!button.contains(event.target) && !dropdown.contains(event.target)) {
      dropdown.classList.remove("show");
    }
  });
}

function bindModalClose(modal, closeButton, cancelButton) {
  if (!modal) {
    return;
  }

  const closeModal = function () {
    modal.classList.remove("show");
    setTimeout(function () {
      modal.style.display = "none";
    }, 300);
  };

  closeButton?.addEventListener("click", closeModal);
  cancelButton?.addEventListener("click", closeModal);

  modal.addEventListener("click", function (event) {
    if (event.target === modal) {
      closeModal();
    }
  });

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape" && modal.classList.contains("show")) {
      closeModal();
    }
  });
}

function bindImagePreview(fileInput, previewImage) {
  if (!fileInput || !previewImage) {
    return;
  }

  fileInput.addEventListener("change", function (event) {
    const file = event.target.files?.[0];
    if (!file || !file.type.startsWith("image/")) {
      return;
    }

    const reader = new FileReader();
    reader.onload = function (loadEvent) {
      previewImage.src = loadEvent.target?.result || previewImage.src;
    };
    reader.readAsDataURL(file);
  });
}

function openProfileModal(modal) {
  if (!modal) {
    return;
  }

  modal.style.display = "flex";
  modal.offsetHeight;
  modal.classList.add("show");

  if (typeof lucide !== "undefined") {
    lucide.createIcons();
  }

  const firstInput = modal.querySelector('input[type="text"]');
  if (firstInput) {
    setTimeout(function () {
      firstInput.focus();
    }, 100);
  }
}
