function showSection(id) {
  document.querySelectorAll(".toggle-section").forEach(function (section) {
    section.classList.remove("active-section");
  });

  document.querySelectorAll(".toggle-button").forEach(function (button) {
    button.classList.remove("active-button");
  });

  const section = document.getElementById(id);
  if (section) {
    section.classList.add("active-section");
  }

  const buttonMap = {
    "tab-today": "btn-today",
    "tab-upcoming": "btn-upcoming",
    "tab-completed": "btn-completed",
    "tab-cancelled": "btn-cancelled",
  };

  const button = document.getElementById(buttonMap[id]);
  if (button) {
    button.classList.add("active-button");
  }
}
